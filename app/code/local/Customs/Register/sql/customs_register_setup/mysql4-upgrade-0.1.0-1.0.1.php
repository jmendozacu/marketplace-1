<?php
$installer = $this;

$installer->startSetup();

$this->updateAttribute('customer','reason',array('source_model'=> 'exploration/entity_reason'));

$installer->endSetup();

