<?php
$installer = $this;

$installer->startSetup();

$this->updateAttribute('customer','vendortype',array('source_model'=> 'exploration/entity_vendortype'));

$installer->endSetup();

