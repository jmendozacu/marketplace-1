<?php
class Webkul_Marketplace_Block_Sellercategory extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
	
	public function getCategoryList(){
		$sellerid=$this->getProfileDetail()->getmageuserid();
		$products=Mage::getModel('marketplace/product')->getCollection()
								->addFieldToFilter('userid',array('eq'=>$sellerid))
								->addFieldToFilter('status', array('neq' => 2))
								->addFieldToSelect('mageproductid');
		$eavAttribute = new Mage_Eav_Model_Mysql4_Entity_Attribute();
        $pro_att_id = $eavAttribute->getIdByCode("catalog_category","name");

        $storeId = Mage::app()->getStore()->getStoreId();
        if(!isset($_GET["c"])){
			$_GET["c"] ='';
		}
       	if(!$_GET["c"]){
        	$parentid = Mage::app()->getStore($storeId)->getRootCategoryId();
        }else{
        	$parentid = $_GET["c"];
        }

		$prefix = Mage::getConfig()->getTablePrefix();
		$products->getSelect()
		->join(array("ces" => $prefix."cataloginventory_stock_item"),"ces.product_id = main_table.mageproductid",array("is_in_stock" => "is_in_stock"))->where("ces.is_in_stock = 1")
		->join(array("ces2" => $prefix."cataloginventory_stock_item"),"ces2.product_id = main_table.mageproductid",array("qty" => "qty"))->where("ces2.manage_stock = 0 OR ces2.use_config_manage_stock = 1 OR ces2.qty != 0.0000")
        ->join(array("ccp" => $prefix."catalog_category_product"),"ccp.product_id = main_table.mageproductid",array("category_id" => "category_id"))
        ->join(array("cce" => $prefix."catalog_category_entity"),"cce.entity_id = ccp.category_id",array("parent_id" => "parent_id"))->where("cce.parent_id = '".$parentid."'")
        ->columns('COUNT(*) AS countCategory')
        ->group('category_id')
        ->join(array("ce1" => $prefix."catalog_category_entity_varchar"),"ce1.entity_id = ccp.category_id",array("name" => "value"))->where("ce1.attribute_id = ".$pro_att_id)
        ->order('name');
        return $products;
	}
	
	public function getProfileDetail(){
		$profileurl = Mage::helper('marketplace')->getCollectionUrl();
		if($profileurl){
			$data=Mage::getModel('marketplace/userprofile')->getCollection()
						->addFieldToFilter('profileurl',array('eq'=>$profileurl));
			foreach($data as $seller){ return $seller;}
		}
	}    
}