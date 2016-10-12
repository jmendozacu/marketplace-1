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
class Netzarbeiter_GroupSwitcher_Model_Rule_Type_TurnoverDaysLt
	extends Netzarbeiter_GroupSwitcher_Model_Rule_Type_TurnoverAbstract
{
	/**
	 * Check if the total turnover of the customers orders within the last N days is less then rule value.
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	public function _match(Mage_Customer_Model_Customer $customer, Mage_Sales_Model_Order $order)
	{
		@list($days, $amount) = explode('|', $this->getRule()->getRuleValue());
		
		/*
		 * Wrong rule value syntax?
		 * It must be N|Amount
		 * N = The number of days
		 * Amount = The Amount to reach
		 */
		if (! isset($amount)) return false;
		$turnover = $this->_getCustomerTurnover($customer, $order, $days);

		return $turnover < (double) $amount;
	}
}