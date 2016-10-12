<?php
$installer = $this;

$installer->startSetup();

$this->addAttribute('customer', 'otherreson', array(
	'type' => 'varchar',
	'input' => 'text',
	'label' => 'Other text',
	'source' => "",
	'global' => 1,
	'visible' => 1,
	'required' => 1,
	'user_defined' => 1,
	'visible_on_front' => 1
));

Mage::getSingleton('eav/config')
	->getAttribute('customer', 'otherreson')
	->setData('used_in_forms', array('customer_register_address','customer_address_edit','adminhtml_customer'))
	->save();

$installer->endSetup();