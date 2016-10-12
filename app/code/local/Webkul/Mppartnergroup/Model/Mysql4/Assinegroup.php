<?php

class Webkul_Mppartnergroup_Model_Mysql4_Assinegroup extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the mppartnergroup_id refers to the key field in your database table.
        $this->_init('mppartnergroup/assinegroup', 'index_id');
    }
}