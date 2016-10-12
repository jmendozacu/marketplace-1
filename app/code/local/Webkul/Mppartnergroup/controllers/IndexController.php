<?php
class Webkul_Mppartnergroup_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$partnerId=Mage::getSingleton('customer/session')->getCustomerId();
		if($partnerId==''){
			$this->_redirect('customer/account/login/');
		}else{
			$this->loadLayout();     
			$this->renderLayout();
		}
    }
	public function getFeeAmountAction(){
		$type=$_POST['type'];
		$groups=Mage::getModel('mppartnergroup/mppartnergroup')
							->getCollection()
							->addFieldToFilter('group_code',array('eq'=>$type));
		foreach($groups as $data){
			echo json_encode(array('amount'=>$data->getFeeAmount(),'number_of_product'=>$data->getNoOfProducts()));
		}						
	}
	
	public function ipnNotifyAction(){
		$wholedata=$this->getRequest()->getParams();
        Mage::getModel('mppartnergroup/mppartnergroup')->saveTransactionrecord($wholedata);
	}
}