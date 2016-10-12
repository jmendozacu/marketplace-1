<?php

class Webkul_Mppartnergroup_Adminhtml_MppartnergroupController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('marketplace/partnergroup')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Partner Group Manager'), Mage::helper('adminhtml')->__('Partner Group Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('mppartnergroup/mppartnergroup')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('mppartnergroup_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('mppartnergroup/partnergroup');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Partner Group Manager'), Mage::helper('adminhtml')->__('Partner Group Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Partner Group News'), Mage::helper('adminhtml')->__('Partner Group News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('mppartnergroup/adminhtml_mppartnergroup_edit'))
				->_addLeft($this->getLayout()->createBlock('mppartnergroup/adminhtml_mppartnergroup_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mppartnergroup')->__('Group does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('mppartnergroup/mppartnergroup');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mppartnergroup')->__('Group was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mppartnergroup')->__('Unable to find group to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('mppartnergroup/mppartnergroup');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Group was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $mppartnergroupIds = $this->getRequest()->getParam('mppartnergroup');
        if(!is_array($mppartnergroupIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select group(s)'));
        } else {
            try {
                foreach ($mppartnergroupIds as $mppartnergroupId) {
                    $mppartnergroup = Mage::getModel('mppartnergroup/mppartnergroup')->load($mppartnergroupId);
                    $mppartnergroup->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($mppartnergroupIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	public function massassinegroupAction(){
		//print_r($this->getRequest()->getParams());
		 $partners = $this->getRequest()->getParam('customer');
        if(!is_array($partners)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select group(s)'));
        } else {
            try {
				$groupcollection=Mage::getModel('mppartnergroup/mppartnergroup')->getCollection()
								->addFieldTOFilter('group_code',array('eq'=>$this->getRequest()->getParam('assinegroup')));
				$group="";
				foreach($groupcollection as $group){
					$group=$group;
				}
                foreach ($partners as $partner) {
					$customerscollection=Mage::getModel('mppartnergroup/assinegroup')
													->getCollection()
													->addFieldToFilter('partner_id',array('eq'=>$partner));
					
					if(count($customerscollection)){
						foreach($customerscollection as $customer){
							$customer->setType($this->getRequest()->getParam('assinegroup'));
							$customer->setTransactionDate(date('Y-m-d h:i:s'));
							$customer->setExpiryDate(date(strtotime('+'.$group->getTimePeriods().' months')));
							$customer->setNoOfProducts($group->getNoOfProducts());
							$customer->setTransactionId('Admin');
							$customer->setTransactionEmail('Admin');
							$customer->setIpnTransactionId('Admin');
							$customer->save();
						}
					}else{
						$data=Mage::getModel('mppartnergroup/assinegroup');
						$expiry_date=date(strtotime('+'.$group->getTimePeriods().' months'));
						$data->setData(array('partner_id'=>$partner,
											 'type'=>$this->getRequest()->getParam('assinegroup'),
											 'expiry_date'=>$expiry_date,
											 'no_of_products'=>$group->getNoOfProducts(),
											 'transaction_date'=>date('Y-m-d h:i:s'),
											 'transaction_id'=>'Admin',
											 'transaction_email'=>'Admin',
											 'ipn_transaction_id'=>'Admin'
											 )
									 );
						$data->save();
						
					}
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($partners))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('marketplace/adminhtml_partners/index');
	}
    public function massStatusAction()
    {
        $mppartnergroupIds = $this->getRequest()->getParam('mppartnergroup');
        if(!is_array($mppartnergroupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select group(s)'));
        } else {
            try {
                foreach ($mppartnergroupIds as $mppartnergroupId) {
                    $mppartnergroup = Mage::getSingleton('mppartnergroup/mppartnergroup')
                        ->load($mppartnergroupId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($mppartnergroupIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'mppartnergroup.csv';
        $content    = $this->getLayout()->createBlock('mppartnergroup/adminhtml_mppartnergroup_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'mppartnergroup.xml';
        $content    = $this->getLayout()->createBlock('mppartnergroup/adminhtml_mppartnergroup_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}