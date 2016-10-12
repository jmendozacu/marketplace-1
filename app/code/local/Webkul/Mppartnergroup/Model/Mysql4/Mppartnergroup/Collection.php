<?php

class Webkul_Mppartnergroup_Model_Mysql4_Mppartnergroup_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mppartnergroup/mppartnergroup');
    }
}