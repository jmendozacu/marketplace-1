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
ALTER TABLE `{$this->getTable('groupswitcher_rule')}`
	ADD priority INT(11) NOT NULL DEFAULT '0',
	ADD is_active TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
	ADD stop_processing TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
CREATE INDEX `IDX_priority` ON `{$this->getTable('groupswitcher_rule')}` (`priority` DESC);
");

$this->addAttribute('groupswitch_rule', 'priority', array(
	'type' => 'static',
	'Label' => 'Priority',
	'required' => 1,
));

$this->addAttribute('groupswitch_rule', 'is_active', array(
	'type' => 'static',
	'Label' => 'Is Active',
	'required' => 1,
));

$this->addAttribute('groupswitch_rule', 'stop_processing', array(
	'type' => 'static',
	'Label' => 'Stop Processing after this Rule',
	'required' => 0,
));

$this->endSetup();

