<?php

class Webkul_Mppartnergroup_Model_Mppartnergroup extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mppartnergroup/mppartnergroup');
    }
	
	public function saveTransactionrecord($wholedata){ 
		$transdata=array();
		$itemname=explode(' ',$wholedata['item_name1']);
		$invoice=explode('-',$wholedata['invoice']);
		$groupcollection=Mage::getModel('mppartnergroup/mppartnergroup')->getCollection()
								->addFieldTOFilter('group_code',array('eq'=>$itemname[2]));
		$no_of_products=0;
		$time_periods=0;
		foreach($groupcollection as $group){
			$no_of_products=$group->getNoOfProducts();
			$time_periods=$group->getTimePeriods();
		}
		$expiry_date=date(strtotime('+'.$time_periods.' months'));
		$transdata=array(
				'partner_id'=>$invoice[1] , 
				'transaction_id'=>$wholedata['txn_id'],
				'transaction_email'=>$wholedata['payer_email'],
				'ipn_transaction_id'=>$wholedata['ipn_track_id'],
				'transaction_date'=>$wholedata['payment_date'],
				'no_of_products'=>$no_of_products,
				'expiry_date'=>$expiry_date,
				'type'=>$itemname[2],
				'transaction_status'=>$wholedata['payer_status']
		);
		$collection=Mage::getSingleton('mppartnergroup/assinegroup')
								->getCollection()
								->addFieldToFilter('partner_id',array('eq'=>$invoice[1]));
	 
		if(count($collection)){
			$collection->addFieldToFilter('transaction_id',array('eq'=>$wholedata['txn_id']))
					   ->addFieldToFilter('expiry_date',array('gt'=>date('Y-m-d h:i:s')));
			if(count($collection)==0){
				$partnerData=Mage::getSingleton('mppartnergroup/assinegroup')
									->getCollection()
									->addFieldToFilter('partner_id',array('eq'=>$invoice[1]));
				foreach($partnerData as $data){
					$data->setTransactionId($wholedata['txn_id']);
					$data->setTransactionEmail($wholedata['payer_email']);
					$data->setIpnTransactionId($wholedata['ipn_track_id']);
					$data->setNoOfProducts($group->getNoOfProducts());
					$data->setTransactionDate($wholedata['payment_date']); 
					$data->setExpiryDate($expiry_date);
					$data->save();
				}
				if($wholedata['payer_status']=='verified'){
					$partnerdata=Mage::getModel('marketplace/userprofile')
							->getCollection()->addFieldToFilter('mageuserid',array('eq'=>$invoice[1]));
					if(count($partnerdata)){
						foreach($partnerdata as $data){
								$data->setWantpartner(1);
								$data->setPartnerstatus('Seller');
								$data->save();
						}
					}else{
						$customer=Mage::getModel('customer/customer')->load($invoice[1]);
						$data=array('wantpartner'=>1,'partnerstatus'=>'Seller','mageuserid'=>$customer->getId());
						$partner=Mage::getModel('marketplace/userprofile');
						$partner->setData($data)->save();
					}
				}
			}
		}else{
			if($wholedata['payer_status']=='verified'){
				$partnerdata=Mage::getModel('marketplace/userprofile')
						->getCollection()->addFieldToFilter('mageuserid',array('eq'=>$invoice[1]));
				if(count($partnerdata)){
					foreach($partnerdata as $data){
							$data->setWantpartner(1);
							$data->setPartnerstatus('Seller');
							$data->save();
					}
				}else{
					$customer=Mage::getModel('customer/customer')->load($invoice[1]);
					$data=array('wantpartner'=>1,'partnerstatus'=>'Seller','mageuserid'=>$customer->getId());
					$partner=Mage::getModel('marketplace/userprofile');
					$partner->setData($data)->save();
				}
			}
			$collection=Mage::getModel('mppartnergroup/assinegroup');
			$collection->setData($transdata);
			$collection->save();
		}
	}
}