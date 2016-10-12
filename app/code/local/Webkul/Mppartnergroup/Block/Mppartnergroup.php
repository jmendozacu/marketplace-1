<?php
class Webkul_Mppartnergroup_Block_Mppartnergroup extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		$this->getLayout()->getBlock('head')->setTitle("Pay Group Fee");
		return parent::_prepareLayout();
    }
    
	public function getPermission(){
		$customerid=Mage::getSingleton('customer/session')->getCustomer()->getId();
		$partners=Mage::getModel('mppartnergroup/assinegroup')->getCollection()
					->addFieldtoFilter('partner_id',array('eq'=>$customerid));
		$checkType=Mage::getStoreConfig('marketplace/mppartnergroup/checktype');
		$expair=false;
		if(($checkType==0 ||$checkType==1)&&count($partners)!=0){
			$partners->addFieldToFilter('expiry_date',array('gt'=>date('Y-m-d h:i:s')));
			if(count($partners)==0){
				$expair=true;
			}
		}
		$type="";
		foreach($partners as $partner){		
			$type=$partner->getType();
			$allowqty=$partner->getNoOfProducts();
		}
		$groupsdetails=Mage::getModel('mppartnergroup/mppartnergroup')
								->getCollection()
								->addFieldToFilter('group_code',array('eq'=>$type));
		foreach($groupsdetails as $details){
			$type=$details->getGroupName();
		}
		if($type==""){
			$allowqty=Mage::getStoreConfig('marketplace/mppartnergroup/defaultproductallowed');
			$type="unassigned";
		}
		$products = Mage::getModel('marketplace/product')->getCollection()
							->addFieldToFilter('userid',array('eq'=>$customerid));
		if(($allowqty <= count($products) && $checkType!=1) ||$expair){
			return array('status'=>true,'type'=>$type,'qty'=>$allowqty,'expair'=>$expair);
		}
		return array('status'=>false);
	}
	
	public function getMpsellerfeepay(){ 
		$data=array();
		$detail=Mage::getStoreConfig('mpsellerfeepay/fee_options/detail');
		$partner_fee = Mage::getStoreConfig('mpsellerfeepay/fee_options/amount');
		$sand_box = Mage::getStoreConfig('marketplace/mppartnergroup/sandbox');
		$merchant_url = Mage::getStoreConfig('marketplace/mppartnergroup/paypalid');
		$currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
		$feefront=Mage::getStoreConfig('marketplace/mppartnergroup/feefront'); 
		if($feefront==0){ 
			$url=$this->getUrl('marketplace/marketplaceaccount/mydashboard/');
			Mage::app()->getFrontController()->getResponse()->setRedirect($url);
		}
		else{
			$customer=Mage::getSingleton('customer/session')->getCustomer();
			$customerid=$customer->getId(); 
			$email=$customer->getEmail();
			$fname=$customer->getFirstname();
			$lname=$customer->getLastname();        
			$data=array('partner_fee'=>$partner_fee , 'merchant'=>$merchant_url,'currency_code'=>$currency_code,'customer_id'=>$customerid,'email'=>$email,'firstname'=>$fname,'lastname'=>$lname,'sandbox'=>$sand_box,'detail'=>$detail);
					return $data;   
		}
	}
	
     public function getMppartnergroup(){ 
        if (!$this->hasData('mppartnergroup')) {
            $this->setData('mppartnergroup', Mage::registry('mppartnergroup'));
        }
        return $this->getData('mppartnergroup');
        
    }
}