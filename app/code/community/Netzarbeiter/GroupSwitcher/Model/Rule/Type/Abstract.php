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
abstract class Netzarbeiter_GroupSwitcher_Model_Rule_Type_Abstract
	extends Varien_Object
	implements Netzarbeiter_GroupSwitcher_Model_Rule_Type_Interface
{
	protected $_rule;

	/**
	 * Enforce type of rule object
	 *
	 * @param Netzarbeiter_GroupSwitcher_Model_Rule $rule
	 */
	public function setRule(Netzarbeiter_GroupSwitcher_Model_Rule $rule)
	{
		$this->_rule = $rule;
		return $this;
	}

	public function getRule()
	{
		return $this->_rule;
	}

	/**
	 * Make some basic validity checks before passing the flow of controll to the specific rule logic
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	final public function match(Mage_Customer_Model_Customer $customer, Mage_Sales_Model_Order $order)
	{
		if (! $this->_isValidCustomer($customer) || ! $this->_isValidOrder($order)) return false;

		if (! $this->_checkCustomerGroupBefore($customer)) return false;

		if (! $this->_checkCustomerGroupIsDifferentFromGroupIdAfter($customer)) return false;

		if (! $this->_checkOrderStatus($order)) return false;

		return $this->_match($customer, $order);
	}

	/**
	 * Check a customer entity is loaded
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return bool
	 */
	protected function _isValidCustomer(Mage_Customer_Model_Customer $customer)
	{
		return (bool) $customer->getId();
	}

	/**
	 * Check a order entity is loaded
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	protected function _isValidOrder(Mage_Sales_Model_Order $order)
	{
		return (bool) $order->getId();
	}

	/**
	 * Check the customers group matches the rules condition
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return bool
	 */
	protected function _checkCustomerGroupBefore(Mage_Customer_Model_Customer $customer)
	{
		if (! $this->getRule()->getGroupIdBefore()) return true;
		return $this->getRule()->getGroupIdBefore() == $customer->getGroupId();
	}

	/**
	 * Check the customers group is different from the rules target customer group
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @return bool
	 */
	protected function _checkCustomerGroupIsDifferentFromGroupIdAfter(Mage_Customer_Model_Customer $customer)
	{
		return $this->getRule()->getGroupIdAfter() != $customer->getGroupId();
	}

	/**
	 * Check the orders status matches the rules condition
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	protected function _checkOrderStatus(Mage_Sales_Model_Order $order)
	{
		if (! $this->getRule()->getOrderStatus()) return true;
		return $this->getRule()->getOrderStatus() == $order->getStatus();
	}
}