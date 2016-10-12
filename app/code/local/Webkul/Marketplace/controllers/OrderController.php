<?php
/**
 * Webkul Marketplace Orders controller
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
require_once 'Mage/Customer/controllers/AccountController.php';
class Webkul_Marketplace_OrderController extends Mage_Customer_AccountController{	

	/**
     * Orders grid
     */
    public function historyAction() {
		$this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('catalog/session');
		$this->getLayout()->getBlock('head')->setTitle( Mage::helper('marketplace')->__('My Orders'));
    	$this->renderLayout();
	}

	/**
     * Initialize order model instance
     *
     * @return Mage_Sales_Model_Order || false
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('id');
        $order = Mage::getModel('sales/order')->load($id);

    	$tracking=Mage::getModel('marketplace/order')->getOrderinfo($id);
    	if((count($tracking)) && Mage::getStoreConfig('marketplace/marketplace_options/ordermanage')){
	    	if ($tracking->getOrderId() == $id) {
		        if (!$order->getId()) {
		            Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('This order no longer exists.'));
		            $this->_redirect('*/*/');
		            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
		            return false;
		        }
		    }else{
		    	Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('You are not authorize to manage this order.'));
		    	$this->_redirect('marketplace/order/history');
	            return false;
		    }
		}else{
			$this->_redirect('marketplace/order/history');
			return false;
		}

        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

	/**
     * View order detail
     */
	public function viewAction() {
		if ($order = $this->_initOrder()) {
			try{
				$this->loadLayout();
		        $this->_initLayoutMessages('customer/session');
		        $this->_initLayoutMessages('catalog/session');
				$this->_title(Mage::helper('marketplace')->__("#%s Order", $order->getRealOrderId()));
		    	$this->renderLayout();
		    }catch(Exception $e){
		    	$this->_redirect('*/*/history');
		    }
		}else{
			$this->_redirect('marketplace/order/history');
		}
	}

	/**
     * Print Order
     */

	public function printAction()
	{
		if ($order = $this->_initOrder()) {
			try{
				$this->loadLayout();
		        $this->_initLayoutMessages('customer/session');
		        $this->_initLayoutMessages('catalog/session');
				$this->_title(Mage::helper('marketplace')->__("Print #%s Order", $order->getRealOrderId()));
		    	$this->renderLayout();
		    }catch(Exception $e){
		    	$this->_redirect('*/*/history');
		    }
	    }else{
			$this->_redirect('marketplace/order/history');
		}
	}

	/**
     * Notify user
     */
    public function emailAction()
    {
    	$id = $this->getRequest()->getParam('id');
        if ($order = $this->_initOrder()) {
            try {
                $order->sendNewOrderEmail();
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                    ->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('The order email has been sent.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__($e->getMessage()));
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Failed to send the order email.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('marketplace/order/view', array('id' => $id));
    }

    /**
     * Seller Additional information to display on order
     */
	public function selleraddressAction(){
		try{
			if($this->getRequest()->isPost()){
				if(!$this->_validateFormKey()){
					 $this->_redirect('*/*/');
				}
				$wholedata=$this->getRequest()->getParams();
				$customerid=Mage::getSingleton('customer/session')->getCustomerId();

				$collection = Mage::getModel('marketplace/userprofile')->getCollection();
				$collection->addFieldToFilter('mageuserid',array('eq'=>$customerid));
				foreach($collection as $row){
					$id=$row->getAutoid();
				}

				$collectionload = Mage::getModel('marketplace/userprofile')->load($id);
				$collectionload->setOthersInfo($wholedata['others_info']);
				$collectionload->save();
				Mage::getSingleton('core/session')->addSuccess( Mage::helper('marketplace')->__('Information has been saved'));				
			}
		} catch (Exception $e) {
			$message = $e->getMessage();
			Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__($message));
		}
		$this->_redirect('marketplace/pricerules');
	}

	/**
     * Cancel order
     */
	public function cancelAction(){
		$orderid = $this->getRequest()->getParam('id');
		if ($order = $this->_initOrder()) {
            try {
            	$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
                $flag = Mage::getModel('marketplace/order')->cancelorder($order,$partnerid);
               	if($flag){
               		$orderid = $this->getRequest()->getParam('id');					
					$collection = Mage::getModel('marketplace/saleslist')->getCollection()
									->addFieldToFilter('mageproownerid', array('eq' => $partnerid))
									->addFieldToFilter('mageorderid', array('eq' => $orderid));
					foreach($collection as $saleproduct){
						$saleproduct->setCpprostatus(2);
						$saleproduct->setPaidstatus(2);						
						$saleproduct->setCollectCodStatus(2);
						$saleproduct->setAdminPayStatus(2);						
						$saleproduct->save();
						
						$trackingcoll = Mage::getModel('marketplace/order')->getCollection()
									->addFieldToFilter('order_id',array('eq'=>$orderid))
									->addFieldToFilter('seller_id',array('eq'=>$partnerid));
						foreach($trackingcoll as $tracking){
							$tracking->setTrackingNumber('canceled');
							$tracking->setCarrierName('canceled');
							$tracking->setIsCanceled(1);
							$tracking->save();
						}

						$is_mpshippingmanager = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mpshippingmanager');
						if($is_mpshippingmanager){
					        if(Mage::getModel("mpshippingmanager/tracking")){
								$trackingcol1=Mage::getModel('mpshippingmanager/tracking')->getCollection()
											->addFieldtoFilter('order_id',array('eq'=>$orderid))
											->addFieldtoFilter('seller_id ',array('in'=>$partnerid));
								foreach($trackingcol1 as $tracking) {
									$tracking->setTrackingNumber('canceled');
									$tracking->setCarrierName('canceled');
									$tracking->save();
								}
							}
						}
					}
					Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('The order has been cancelled.'));
					$this->_redirect('marketplace/order/view/',array('id'=>$orderid));
				}else{
					Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('You are not permitted to cancel this order.'));
					$this->_redirect('marketplace/order/history');
				}
			}catch (Mage_Core_Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__($e->getMessage()));
            }
            catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('The order has not been cancelled.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('marketplace/order/view', array('id' => $orderid));
	}



	/**
     * Ship order
     */
	public function shipAction(){
		$orderid = $this->getRequest()->getParam('id');
		if ($order = $this->_initOrder()) {
			try{
				$customerid=Mage::getSingleton('customer/session')->getCustomerId();	
				$seller_orderslist=Mage::getModel('marketplace/saleslist')->getCollection()
								 ->addFieldToFilter('mageproownerid',array('eq'=>$customerid))
								 ->addFieldToFilter('mageorderid',array('eq'=>$orderid));
				if(count($seller_orderslist)){
					$trackingid=$this->getRequest()->getParam('tracking_id');
					$carrier=$this->getRequest()->getParam('carrier');
					$order=Mage::getModel('sales/order')->load($orderid);
					if($order->canUnhold()) { 
						Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__("Can not do shipment as order is in HOLD state"));
					} else {
						$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
						$items=array();
						$shippingAmount=0;
						$trackingsdata=Mage::getModel('marketplace/order')->getCollection()
										 ->addFieldToFilter('order_id',array('eq'=>$orderid))
										 ->addFieldToFilter('seller_id',array('eq'=>$partnerid));
						foreach($trackingsdata as $tracking){								
							$shippingAmount=$tracking->getShippingCharges();
							$items=explode(',',$tracking->getItemIds());
						}

							/**/
						$itemsarray = $this->_getItemQtys($order,$items);
						
						if(count($itemsarray)>0){
													
							$shipment = false;				
							$shipmentId = $this->getRequest()->getParam('shipment_id');			
							$orderId = $orderid;	
							if($shipmentId){
								$shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
							}elseif($orderId){
								$order  = Mage::getModel('sales/order')->load($orderId);
								if (!$order->getId()) {
									Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('The order no longer exists.'));
									return false;
								}
								if($order->getForcedDoShipmentWithInvoice()){
									Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Cannot do shipment for the order separately from invoice.'));
									return false;
								}
								if(!$order->canShip()){
									Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Cannot do shipment for the order.'));
									return false;
								}

								$savedQtys = $this->_getItemQtys($order,$items);
								$shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys['data']);
							}
							$shipment->register();
							$comment = '';
							$shipment->getOrder()->setCustomerNoteNotify(1);
							$responseAjax = new Varien_Object();
							$isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];
							if ($isNeedCreateLabel && true) {
								$responseAjax->setOk(true);
							}
							$shipment->getOrder()->setIsInProcess(true);
							$transactionSave = Mage::getModel('core/resource_transaction')
										->addObject($shipment)->addObject($shipment->getOrder())->save();

							$shipmentCreatedMessage = Mage::helper('marketplace')->__('The shipment has been created.');
							$labelCreatedMessage    = Mage::helper('marketplace')->__('The shipping label has been created.');
							Mage::getSingleton('core/session')->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
								: $shipmentCreatedMessage);	
							
							$trackingcol1=Mage::getModel('marketplace/order')->getCollection()
										->addFieldtoFilter('order_id',array('eq'=>$orderid))
										->addFieldtoFilter('seller_id ',array('eq'=>$partnerid));
							$courrier="custom";
							foreach($trackingcol1 as $row) {
								if($shipment->getId() != '') { 
									$row->setShipmentId($shipment->getId());
									$row->setTrackingNumber($trackingid);
									$row->setCarrierName($carrier);
									$row->save();
									$track = Mage::getModel('sales/order_shipment_track')
									 ->setShipment($shipment)
									 ->setData('title',  $carrier)
									 ->setData('number', $trackingid)
									 ->setData('carrier_code',  $courrier)
									 ->setData('order_id', $shipment->getData('order_id'))
									 ->save();
								}
							}

							$is_mpshippingmanager = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mpshippingmanager');
							if($is_mpshippingmanager){
						        if(Mage::getModel("mpshippingmanager/tracking")){
									$trackingcol1=Mage::getModel('mpshippingmanager/tracking')->getCollection()
													->addFieldtoFilter('order_id',array('eq'=>$orderid))
													->addFieldtoFilter('seller_id ',array('in'=>$partnerid));
									$courrier="custom";
									foreach($trackingcol1 as $row) {
										if($shipment->getId() != '') { 
											$row->setShipmentId($shipment->getId());
											$row->setTrackingNumber($trackingid);
											$row->setCarrierName($carrier);
											$row->save();
										}
									}
								}
							}

							$shipment->sendEmail(true, $comment)->setEmailSent(true)->save();

							Mage::getModel('marketplace/order')->getCommsionCalculation($order);
						}
					}
				}else{
					Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('You are not permitted to generate shipment for this order.'));
				}
			}catch(Exception $e){
				Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__($e->getMessage()));			
			}
		}
		$this->_redirect('marketplace/order/view/',array('id'=>$orderid));
	}

	/**
     * Invoice order
     */
	public function invoiceAction(){
		$orderid = $this->getRequest()->getParam('id');
		if ($order = $this->_initOrder()) {
			try{
				$invoice_id = 0;
				$partnerid=Mage::getSingleton('customer/session')->getCustomerId();
				$order=Mage::getModel('sales/order')->load($orderid);
				if($order->canUnhold()) { 
					Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__("Can not create invoice as order is in HOLD state"));
				} else {
					$marketplace_order=Mage::getModel('marketplace/order')->getOrderinfo($orderid);
					$invoiceId=$marketplace_order->getInvoiceId();
					if(!$invoiceId){
						$items=array();
						$itemsarray=array();
						$shippingAmount=0;
						$trackingsdata=Mage::getModel('marketplace/order')->getCollection()
									 ->addFieldToFilter('order_id',array('eq'=>$orderid))
									 ->addFieldToFilter('seller_id',array('eq'=>$partnerid));
						foreach($trackingsdata as $tracking){
							$shippingAmount=$tracking->getShippingCharges();
							$codcharges=$tracking->getCodCharges();
							$items=explode(',',$tracking->getItemIds());
						}

						$is_mpshippingmanager = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mpshippingmanager');
						if($is_mpshippingmanager){
					        if(Mage::getModel("mpshippingmanager/tracking")){
					        	$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
									 ->addFieldToFilter('order_id',array('eq'=>$orderid))
									 ->addFieldToFilter('seller_id',array('eq'=>$partnerid));
								foreach($trackingsdata as $tracking){
									$shippingAmount=$tracking->getShippingCharges();
								}
					        }
					    }

						$codCharges = 0;
						$tax = 0;
						$collection = Mage::getModel('marketplace/saleslist')->getCollection()
											->addFieldToFilter('mageproownerid',$partnerid)
											->addFieldToFilter('mageorderid',array('eq'=>$orderid));
						foreach($collection as $saleproduct){

							$codCharges = $codCharges+$saleproduct->getCodCharges();

			                $tax = $tax + $saleproduct->getTotaltax();
						}			        

						$itemsarray = $this->_getItemQtys($order,$items);

						if(count($itemsarray)>0){
							
							if($order->canInvoice()) { 
								$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($itemsarray['data']);
								$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
								$invoice->setShippingAmount($shippingAmount);
								$invoice->setSubtotal($itemsarray['subtotal']);
								$invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
								$invoice->setMpcashondelivery($codCharges);
								$invoice->setGrandTotal($itemsarray['subtotal']+$shippingAmount+$codcharges+$tax);
								$invoice->setBaseGrandTotal($itemsarray['subtotal']+$shippingAmount+$codcharges+$tax);
								$invoice->register();
							
								$transactionSave = Mage::getModel('core/resource_transaction')
											->addObject($invoice)
											->addObject($invoice->getOrder());
								$transactionSave->save();

								$invoice_id = $invoice->getId();

								$invoice->sendEmail();
				                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
				                    ->getUnnotifiedForInstance($invoice, Mage_Sales_Model_Order_Invoice::HISTORY_ENTITY_NAME);
				                if ($historyItem) {
				                    $historyItem->setIsCustomerNotified(1);
				                    $historyItem->save();
				                }

								Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('Invoice has been created for this order.'));
							}
						}

						/*update mpcod table records*/
						if($invoice_id != '') {
							$saleslist_coll = Mage::getModel('marketplace/saleslist')->getCollection()
											->addFieldToFilter('mageproownerid', array('eq' => $partnerid))
											->addFieldToFilter('mageorderid', array('eq' => $orderid));
							foreach($saleslist_coll as $saleslist){
								$saleslist->setCollectCodStatus(1);
								$saleslist->save();
							}

							$trackingcol1=Mage::getModel('marketplace/order')->getCollection()
											->addFieldtoFilter('order_id',array('eq'=>$orderid))
											->addFieldtoFilter('seller_id ',array('eq'=>$partnerid));
							foreach($trackingcol1 as $row) {							
								$row->setInvoiceId($invoice_id);
								$row->save();							
							}

							$is_mpshippingmanager = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mpshippingmanager');
							if($is_mpshippingmanager){
						        if(Mage::getModel("mpshippingmanager/tracking")){
									$trackingcol1=Mage::getModel('mpshippingmanager/tracking')->getCollection()
										->addFieldtoFilter('order_id',array('eq'=>$orderid))
										->addFieldtoFilter('seller_id ',array('in'=>$partnerid));
									foreach($trackingcol1 as $row) {
										$row->setInvoiceId($invoice_id);
										$row->save();
									}
								}
							}
						}
						$historyItem = Mage::getResourceModel('sales/order_status_history_collection')
							->getUnnotifiedForInstance($order, Mage_Sales_Model_Order::HISTORY_ENTITY_NAME);
						if ($historyItem) {
							$historyItem->setIsCustomerNotified(1);
							$historyItem->save();
						}
					}else{
						Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Cannot create Invoice for this order.'));
					}
				}
			}catch(Exception $e){
				Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__($e->getMessage()));			
			}
		}
		$this->_redirect('marketplace/order/view/',array('id'=>$orderid));
	}

	/**
     * Initialize requested invoice instance
     * @param unknown_type $order
     */
    protected function _initInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')
                ->load($invoiceId)
                ->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Initialize creditmemo model instance
     *
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    protected function _initCreditmemo($update = false)
    {
        $creditmemo = false;
        
        $customerid=Mage::getSingleton('customer/session')->getCustomerId();
		$orderId = $this->getRequest()->getParam('id');

		$refund_data=$this->getRequest()->getParams();
        
        $order  = Mage::getModel('sales/order')->load($orderId);
        $invoice = $this->_initInvoice($order);

        // if (!$order->_canCreditmemo($order)) {
        //     return false;
        // }

        $trackingsdata=Mage::getModel('marketplace/order')->getCollection()
						 ->addFieldToFilter('order_id',array('eq'=>$orderId))
						 ->addFieldToFilter('seller_id',array('eq'=>$customerid));
		foreach($trackingsdata as $tracking){
			$items=explode(',',$tracking->getItemIds());
			$cod_charges = $tracking->getCodCharges();
		}

        $savedData = $this->_getItemData($order,$items);
        
        $qtys = array();
        $data = array();
        foreach ($savedData as $orderItemId =>$itemData) {
            if (isset($itemData['qty'])) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
        }
        $shippingamount = 0;
        $is_mpshippingmanager = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mpshippingmanager');
		if($is_mpshippingmanager){
	        if(Mage::getModel("mpshippingmanager/tracking")){
	        	$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
					 ->addFieldToFilter('order_id',array('eq'=>$orderId))
					 ->addFieldToFilter('seller_id',array('eq'=>$customerid));
				foreach($trackingsdata as $tracking){
					$shippingamount=$tracking->getShippingCharges();
				}
	        }
	    }
	    if($refund_data['creditmemo']['shipping_amount']==''){
	    	$refund_data['creditmemo']['shipping_amount'] = 0;
	    }
	    if($refund_data['creditmemo']['adjustment_positive']==''){
	    	$refund_data['creditmemo']['adjustment_positive'] = 0;
	    }
	    if($refund_data['creditmemo']['adjustment_negative']==''){
	    	$refund_data['creditmemo']['adjustment_negative'] = 0;
	    }
        $data['shipping_amount'] = $refund_data['creditmemo']['shipping_amount'];
        $data['adjustment_positive'] = $refund_data['creditmemo']['adjustment_positive'];
        $data['adjustment_negative'] = $refund_data['creditmemo']['adjustment_negative'];
        $data['qtys'] = $qtys;
        $service = Mage::getModel('sales/service_order', $order);
        if ($invoice) {
            $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
        } else {
            $creditmemo = $service->prepareCreditmemo($data);
        }

        Mage::register('current_creditmemo', $creditmemo);

        return $creditmemo;
    }

    /**
     * Save creditmemo and related order, invoice in one transaction
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     */
    protected function _saveCreditmemo($creditmemo)
    {
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();

        return $this;
    }

	/**
     * Refund order
     */
	public function creditmemoAction(){
		$orderid = $this->getRequest()->getParam('id');
		if ($order = $this->_initOrder()) {
            try {
	            $creditmemo = $this->_initCreditmemo();
	            if ($creditmemo) {
	                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
	                    Mage::throwException(
	                        Mage::helper('marketplace')->__('Credit memo\'s total must be positive.')
	                    );
	                }

	                $comment = '';
	                if (!empty($data['comment_text'])) {
	                    $creditmemo->addComment(
	                        $data['comment_text'],
	                        isset($data['comment_customer_notify']),
	                        isset($data['is_visible_on_front'])
	                    );
	                    if (isset($data['comment_customer_notify'])) {
	                        $comment = $data['comment_text'];
	                    }
	                }

	                if (isset($data['do_refund'])) {
	                    $creditmemo->setRefundRequested(true);
	                }
	                if (isset($data['do_offline'])) {
	                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
	                }

	                $creditmemo->register();
	                if (!empty($data['send_email'])) {
	                    $creditmemo->setEmailSent(true);
	                }

	                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

	                $this->_saveCreditmemo($creditmemo);

	                $creditmemo->sendEmail(!empty($data['send_email']), $comment);

	                /*update mpcod table records*/

	                $orderid = $this->getRequest()->getParam('id');
					$partnerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
					$actparterprocost = 0;
					$totalamount = 0;
					$codCharges = 0;
					$shippingamount = 0;
					$tax = 0;
					$is_mpshippingmanager = Mage::helper('core/data')->isModuleOutputEnabled('Webkul_Mpshippingmanager');
					if($is_mpshippingmanager){
				        if(Mage::getModel("mpshippingmanager/tracking")){
				        	$trackingsdata=Mage::getModel('mpshippingmanager/tracking')->getCollection()
								 ->addFieldToFilter('order_id',array('eq'=>$orderid))
								 ->addFieldToFilter('seller_id',array('eq'=>$partnerid));
							foreach($trackingsdata as $tracking){
								$shippingamount=$tracking->getShippingCharges();
							}
				        }
				    }
			        $collection = Mage::getModel('marketplace/saleslist')->getCollection()
										->addFieldToFilter('mageproownerid',$partnerid)
										->addFieldToFilter('mageorderid',array('eq'=>$orderid));
					foreach($collection as $saleproduct){
						$saleproduct->setPaidstatus(3);
						$saleproduct->setCollectCodStatus(3);
						$saleproduct->save();			
					}

					$trackingcoll = Mage::getModel('marketplace/order')->getCollection()
										->addFieldToFilter('order_id',array('eq'=>$orderid))
										->addFieldToFilter('seller_id',array('eq'=>$partnerid));
					foreach($trackingcoll as $tracking){
						$tracking->setCreditmemoId($creditmemo->getId());
						$tracking->save();
					}

	                Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('The credit memo has been created.'));

	                Mage::getSingleton('core/session')->getCommentText(true);
	                $this->_redirect('marketplace/order/view', array('id' => $creditmemo->getOrderId()));
	                return;
	            } else {
	                $this->_forward('noRoute');
	                return;
	            }
	        } catch (Mage_Core_Exception $e) {
	            Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__($e->getMessage()));
	            Mage::getSingleton('core/session')->setFormData($data);
	        } catch (Exception $e) {
	            Mage::logException($e);
	            Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Cannot save the credit memo.'));
	        }
        }
        $this->_redirect('marketplace/order/view', array('id' => $orderid));
	}

	protected function _getItemQtys($order,$items){
		$data=array();
		$subtotal = 0;
		$baseSubtotal = 0;
		foreach($order->getAllItems() as $item){
			if(in_array($item->getProductId(),$items)){
				$data[$item->getItemId()]=intval($item->getQtyOrdered());
				$subtotal+=$item->getRowTotal();
				$baseSubtotal+=$item->getBaseRowTotal();
			}else{
				$data[$item->getItemId()]=0;
			}   
		}
		return array('data'=>$data,'subtotal'=>$subtotal,'baseSubtotal'=>$baseSubtotal);
	}

	/**
     * Get requested items qtys
     */
    protected function _getItemData($order,$items)
    {
    	$data['items'] = array();
        foreach($order->getAllItems() as $item){        	
			if(in_array($item->getProductId(),$items)){
				$data['items'][$item->getItemId()]['qty']=intval($item->getQtyOrdered());
			}else{
				$data['items'][$item->getItemId()]['qty']=0;
			}   
		}
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }
}