<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE TABLE {$this->getTable('marketplace_partnergroup')} (
  `group_id` int(11) unsigned NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL default '',
  `group_code` varchar(255) NOT NULL default '',
  `no_of_products` int(6) NOT NULL,
  `time_periods` int(11) NOT NULL,
  `fee_amount` decimal(6,2) NOT NULL,
  `status` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('marketplace_partnertype')};
CREATE TABLE {$this->getTable('marketplace_partnertype')} (
  `index_id` int(11) unsigned NOT NULL auto_increment,
  `partner_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `no_of_products` int(11) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `transaction_email` varchar(255) NOT NULL,
  `ipn_transaction_id` varchar(255) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `expiry_date` datetime NOT NULL,
  `transaction_status` varchar(255) NOT NULL,
  PRIMARY KEY (`index_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 