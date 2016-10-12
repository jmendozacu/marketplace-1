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

/**
 * @category   Netzarbeiter
 * @package    Netzarbeiter_GroupSwitcher
 * @author     Vinai Kopp <vinai@netzarbeiter.com>
 */
class Netzarbeiter_GroupSwitcher_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('GroupSwitcher/rule');
	}

	public function addGroupIdFilter($groupId)
	{
		$this->getSelect()
			->where("group_id_after != ?", $groupId)
			->where("group_id_before = ? OR group_id_before IS NULL", $groupId)
			;
		return $this;
	}

	public function addOrderStatusFilter($orderStatus)
	{
		if ($orderStatus)
		{
			$this->getSelect()
				->where("order_status = ? OR order_status IS NULL", $orderStatus)
				;
		}
		return $this;
	}

	public function addActiveFilter()
	{
		$this->getSelect()
			->where("is_active = 1")
			;
		return $this;
	}

	public function addStoreIdFilter($storeId)
	{
		$this->getSelect()
			->where("FIND_IN_SET(?, `store_ids`)", intval($storeId))
			;
		return $this;
	}
	
	public function addEventFilter($events)
	{
		if (! is_array($events)) $events = array($events);
		
		foreach ($events as $event)
		{
			switch ($event)
			{
				case 'login':
					$this->getSelect()->where("rule_type IN ('turnover_days_ge', 'turnover_days_lt')");
					break;
			}
		}
		
		return $this;
	}
}