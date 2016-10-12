<?php
class Customs_Register_Model_Entity_Reason extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    
    public function getAllOptions()
    {
        /*if (!$this->_options) {
            $this->_options = array(
                array(
                'value' => "",
                'label' => "",
            ),
            array(
                'value' => 1,
                'label' => "Trade Show",
            ),
            array(
                'value' => 2,
                'label' => "Internet Search",
            ),
            array(
                'value' => 3,
                'label' => "Word of mouth",
            ),
            array(
                'value' => 4,
                'label' => "Other",
            )
            );
        }
        return $this->_options;*/
        
        // query get option from other text input
        //database read adapter 
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $result = $read->fetchAll("        
            SELECT  *, COUNT(*) AS count_row FROM
            customer_entity_varchar AS cev
            LEFT JOIN
            eav_attribute as ea
            ON cev.attribute_id = ea.attribute_id
            WHERE ea.attribute_code = 'otherreson' 
            GROUP BY cev.`value`
        ");
        
        $arr_add = array();
        foreach($result as $item) {
            if( $item["count_row"] >= 3) {
                array_push($arr_add, $item);
            }
        }        
        
        if (!$this->_options) {
        	$sourceString = Mage::getStoreConfig('selectoptions/general/textarea');
        	$sourceArray = array_filter(explode(',',trim(strip_tags($sourceString))));
        
        	$this->_options = array();
        	$this->_options[] = array(
        		'value' => " ",
        		'label' => 'Choose Option...'
        	);
        	foreach($sourceArray as $sourceIndex => $sourceItem){
        		$this->_options[] = array(
        			'value' => $sourceIndex + 1,
        			'label' => $sourceItem
        		);
        	}
        }
        
        // count and add list
        if (count($arr_add) > 0) {
            // value = replace space to _
            // label = Uppercase first charecter
            //$value = strtolower(str_replace(' ', '_', $arr_add[0]["value"]));
            $lable = ucfirst($arr_add[0]["value"]);
            //array_push($this->_options,array("value" => $value, "label" => $lable));
            
            // update confix data
            $sourceStringUpdate = $sourceString . ', ' . $lable;
            // update core config========================================
            
            if ( strpos($sourceString, $lable) === false ) {
                $updateCoreConfig = new Mage_Core_Model_Config();        
                $updateCoreConfig ->saveConfig('selectoptions/general/textarea', $sourceStringUpdate, 'default', 0);
            }

        }     
        
        return $this->_options;
    }
    
}