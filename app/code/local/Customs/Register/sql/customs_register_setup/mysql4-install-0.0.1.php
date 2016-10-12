<?php
$installer = $this;

$installer->startSetup();

//$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

// $this-> // b? $setup ? trên và thêm <class>Mage_Eav_Model_Entity_Setup</class> vào confix
//$setup->addAttribute('customer', 'reason', array(

$this->addAttribute('customer', 'reason', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'Reason for approaching toanlm',
    'global' => 1,
    'visible' => 1,
    'required' => 1,
    'user_defined' => 1,
    'visible_on_front' => 1,
    'source'=>'eav/entity_attribute_source_table',
    'option'=> array(
    'values' => array(
        0 => 'Internet',
        1 => 'Tradeshow',
        2 => 'Other'
        ),
    ),
));


Mage::getSingleton('eav/config')
    ->getAttribute('customer', 'reason')
    ->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','adminhtml_checkout'))
    ->save();

$installer->endSetup();
