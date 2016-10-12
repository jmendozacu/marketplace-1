<?php
/**
 * Webkul Marketplace Order Invoice controller
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
require_once 'Mage/Customer/controllers/AccountController.php';
class Webkul_Marketplace_Order_InvoiceController extends Mage_Customer_AccountController{	

	/**
     * Initialize invoice model instance
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _initInvoice($update = false)
    {
        $this->_title(Mage::helper('marketplace')->__('Invoices'));

        $invoice = false;
        $itemsToInvoice = 0;
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');

    	$tracking=Mage::getModel('marketplace/order')->getOrderinfo($orderId);
    	if((count($tracking))&&Mage::getStoreConfig('marketplace/marketplace_options/ordermanage')){
	    	if ($tracking->getInvoiceId() == $invoiceId) {
		        if ($invoiceId) {
		            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
		            if (!$invoice->getId()) {
		                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('The invoice no longer exists.'));
		                return false;
		            }
		        } elseif ($orderId) {
		            $order = Mage::getModel('sales/order')->load($orderId);
		            /**
		             * Check order existing
		             */
		            if (!$order->getId()) {
		                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('The order no longer exists.'));
		                return false;
		            }
		        }
		    }else{
		    	Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('You are not authorize to view this invoice.'));
	            return false;
		    }
		}else{
			$this->_redirect('marketplace/order/history');
			return false;
		}

        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

	/**
     * View invoice detail
     */
	public function viewAction() {
		$invoiceId = $this->getRequest()->getParam('invoice_id');
		$order_id = $this->getRequest()->getParam('order_id');
		if ($invoice = $this->_initInvoice()) {
			try{
				$this->loadLayout();
		        $this->_initLayoutMessages('customer/session');
		        $this->_initLayoutMessages('catalog/session');
				$this->_title(Mage::helper('marketplace')->__("#%s Order Invoice", $invoice->getIncrementId()));
		    	$this->renderLayout();
		    }catch(Exception $e){
		    	$this->_redirect('marketplace/order/view', array('id'=>$order_id));
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
    	$invoiceId = $this->getRequest()->getParam('invoice_id');
		$order_id = $this->getRequest()->getParam('order_id');
        if ($invoice = $this->_initInvoice()) {
            try {        		
                $invoice->sendEmail();
                $historyItem = Mage::getResourceModel('sales/order_status_history_collection')
                    ->getUnnotifiedForInstance($invoice, Mage_Sales_Model_Order_Invoice::HISTORY_ENTITY_NAME);
                if ($historyItem) {
                    $historyItem->setIsCustomerNotified(1);
                    $historyItem->save();
                }
                Mage::getSingleton('core/session')->addSuccess(Mage::helper('marketplace')->__('The message has been sent.'));
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(Mage::helper('marketplace')->__('Failed to send the order email.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('marketplace/order_invoice/view', array('order_id'=>$order_id,'invoice_id'=>$invoiceId));
    }

    /**
     * Print invoice
     */
    public function printAction()
    {
    	$invoiceId = $this->getRequest()->getParam('invoice_id');
		$order_id = $this->getRequest()->getParam('order_id');
    	if ($invoice = $this->_initInvoice()) {
	    	try{
                if ($invoice = Mage::getModel('sales/order_invoice')->load($invoiceId)) {	            	
	                $pdf = Mage::getModel('marketplace/order_pdf_invoice')->getPdf(array($invoice));
	                $this->_prepareDownloadResponse('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').
	                    '.pdf', $pdf->render(), 'application/pdf');
	            }else {
		            $this->_forward('noRoute');
		        }

	        } catch (Exception $e) {
				$message = $e->getMessage();
				Mage::getSingleton('core/session')->addError($message);
				$this->_redirect('marketplace/order_invoice/view', array('order_id'=>$order_id,'invoice_id'=>$invoiceId));
			}
		}else{
			$this->_redirect('marketplace/order_invoice/view', array('order_id'=>$order_id,'invoice_id'=>$invoiceId));
		}		
    }
}