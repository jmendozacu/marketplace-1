<?php
Class Webkul_Mppartnergroup_Model_Observer
{
	public function productSave($observer){
		$product=$observer->getProduct();
		$customerid=$product->getUserid();
		$allowqty=0;
		$flag=0;
		$checkType=Mage::getStoreConfig('marketplace/mppartnergroup/checktype');
		$customer=Mage::getModel('customer/customer')->load($customerid);
		$partners=Mage::getModel('mppartnergroup/assinegroup')->getCollection()
					->addFieldtoFilter('partner_id',array('eq'=>$customerid));
		if($checkType==0 ||$checkType==1){	
			$partners->addFieldToFilter('expiry_date',array('gt'=>date('Y-m-d h:i:s')));			
		}			
		foreach($partners as $partner){	
			$allowqty=$partner->getNoOfProducts();
			$expirydate=$partner->getExpiryDate();
		}
		if($allowqty==0){
			$allowqty=Mage::getStoreConfig('marketplace/mppartnergroup/defaultproductallowed');
			$flag=1;
		}
		$products = Mage::getModel('marketplace/product')->getCollection()
							->addFieldToFilter('userid',array('eq'=>$customerid));
		$totalproduct=count($products);
		$productleft=($allowqty-$totalproduct)-1; //die();
		$date =strtotime($expirydate);
		$today =  new DateTime();
		$today->format('Y-m-d h:m:s');
		$current = $today->getTimestamp();
		$difference= ($date-$current)/86400;
		$email = Mage::getModel('admin/user')->load(1)->getEmail();
		$headers = 'From:Administrator' . "\r\n" .
				   'Reply-To: ' .$email. "\r\n" .
				   'X-Mailer: PHP/' . phpversion();
		if($flag==1){ //check when user didn't subscribe for group
			if($productleft<=5)
			$content = 'You have total '.$productleft.' product left to Add. Subscribe to add more product.';
			mail($customer->getEmail(),'Left Product',$content,$headers);
		}
		else if($flag==0){ //check when user subscribe for group
			if($checkType==2){ //check for only product
				if($productleft<=5)
					$content = 'You have total '.$productleft.' product left to Add.';
			}
			if($checkType==1){ //check for only time
				if($difference<=5)
					$content = 'Your group subscription expire on '.$expirydate;
			}
			if($checkType==0){ //check for product and time
				if($difference<=5 || $productleft<=5 )
					$content = 'Your group subscription expire on '.$expirydate.' and You have total '.$productleft.' product left to Add';
			}				
			mail($customer->getEmail(),'Left Product',$content,$headers);
		}
	}
}