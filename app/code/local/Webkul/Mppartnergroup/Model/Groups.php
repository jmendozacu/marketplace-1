<?php

class Webkul_Mppartnergroup_Model_Groups extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('mppartnergroup')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('mppartnergroup')->__('Disabled')
        );
    }
}