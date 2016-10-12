<?php
$installer = $this;

$installer->startSetup();

$this->addAttribute('customer', 'vendortype', array(
    'type' => 'int',
    'input' => 'select',
    'label' => 'Vendor type',
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
    ->getAttribute('customer', 'vendortype')
    ->setData('used_in_forms', array('adminhtml_customer','customer_account_create','customer_account_edit','adminhtml_checkout'))
    ->save();

$installer->endSetup();
