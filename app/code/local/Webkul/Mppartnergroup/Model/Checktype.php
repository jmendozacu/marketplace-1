<?php

class Webkul_Mppartnergroup_Model_Checktype
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
			array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('Only Number Of Products')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Only Time')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Time and Number Of Products')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            0 => Mage::helper('adminhtml')->__('Time and Number Of Products'),
            1 => Mage::helper('adminhtml')->__('Only Time'),
			2 => Mage::helper('adminhtml')->__('Only Number Of Products'),
        );
    }

}
