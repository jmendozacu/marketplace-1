<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupSwitcher
 * @copyright  Copyright (c) 2009 Vinai Kopp http://netzarbeiter.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS `{$this->getTable('groupswitcher_rule')}`;
CREATE TABLE `{$this->getTable('groupswitcher_rule')}` (
	`id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	`name` VARCHAR(255) NOT NULL DEFAULT '',
	`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	`group_id_before` SMALLINT(3) UNSIGNED NULL,
	`group_id_after` SMALLINT(3) UNSIGNED NOT NULL,
	`rule_type` ENUM('buy_product', 'num_orders', 'order_total', 'total_orders') NOT NULL,
	`rule_value` VARCHAR(255) NOT NULL DEFAULT '',
	`note` VARCHAR(500) NOT NULL DEFAULT '',
	CONSTRAINT `FK_group_id_before` FOREIGN KEY (`group_id_before`) REFERENCES {$this->getTable('customer_group')} (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `FK_group_id_after` FOREIGN KEY (`group_id_after`) REFERENCES {$this->getTable('customer_group')} (`customer_group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
	INDEX `IDX_rules` (`rule_type`),
	UNIQUE INDEX `UIDX_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$this->installEntities(array(
	'groupswitch_rule' => array(
		'entity_model' => 'GroupSwitcher/rule',
		'table' => 'GroupSwitcher/rule',
		'attributes' => array(
			'name'				=> array('type' => 'static', 'label' => 'Rule Name'),
			'group_id_before'	=> array('type' => 'static', 'label' => 'Switch if Group'),
			'group_id_after'	=> array('type' => 'static', 'label' => 'Switch to Group'),
			'created_at'		=> array('type' => 'static', 'label' => 'Created At'),
			'rule_type'			=> array('type' => 'static', 'label' => 'Rule Type'),
			'rule_value'		=> array('type' => 'static', 'label' => 'Rule Condition Value'),
			'note'				=> array('type' => 'static', 'label' => 'Comment'),
		),
	),
));

/*
 * This is a better approach - more flexible on the long run
 */
$this->removeAttribute('catalog_product', 'groupswitcher_group_id');


$this->endSetup();