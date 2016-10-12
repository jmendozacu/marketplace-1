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
abstract class Netzarbeiter_GroupSwitcher_Model_Rule_Type_TurnoverAbstract
	extends Netzarbeiter_GroupSwitcher_Model_Rule_Type_Abstract
{
	protected $_customerId;
	protected $_turnover;
	protected $_orderStatusAttribute;

	/**
	 * Return the sum of all totals of all orders the customer has placed (within the specified number
	 * of days). If the rule applies only to a specific order status, only use the orders in that
	 * specific status. If a number of days > 0 are secified, only the turnover within the last
	 * N days is returned.
	 *
	 * Using raw SQL is ugly, but using a collection is too slow at this place (imo).
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Mage_Sales_Model_Order $order
	 * @param int $days
	 * @return double
	 */
	protected function _getCustomerTurnover(Mage_Customer_Model_Customer $customer, Mage_Sales_Model_Order $order, $days = 0)
	{
		if (isset($this->_customerId) && $this->_customerId == $customer->getId())
		{
			return $this->_turnover;
		}

		$this->_customerId = $customer->getId();
		$resource = $order->getResource();
		$adapter = $resource->getReadConnection();
		$bind = array('customer_id' => $customer->getId());
		$select = $adapter->select()
			->from(array('main_table' => $resource->getTable('sales/order')), 'SUM(grand_total) - SUM(shipping_amount)')
			->where('main_table.customer_id=:customer_id')
			;
		if ($days > 0)
		{
			$select->where(sprintf('DATE_SUB(CURDATE(), INTERVAL %d DAY) <= DATE(main_table.created_at)', $days));
		}
		if ($this->getRule()->getOrderStatus())
		{
			$select
				->joinInner( // eav sales order varchar table, current order status is saved there
					array(
						"_status_table" => "{$this->_getOrderStatusAttribute()->getBackend()->getTable()}",
					),
					"_status_table.entity_id = main_table.entity_id AND _status_table.attribute_id = {$this->_getOrderStatusAttribute()->getId()} AND _status_table.value=:order_status",
					""
				)
				;
			;
			$bind['order_status'] = $this->getRule()->getOrderStatus();
		}
		//Mage::log(array('select' => (string) $select, 'bind' => $bind));
		$this->_turnover = $adapter->fetchOne($select, $bind);

		return $this->_turnover;
	}

	protected function _getOrderStatusAttribute()
	{
		if (! isset($this->_orderStatusAttribute))
		{
			$this->_orderStatusAttribute = Mage::getModel('sales/order')->getResource()->getAttribute('status');;
		}
		return $this->_orderStatusAttribute;
	}
	
	/**
	 * We don't need valid orders for the turnover rules
	 * 
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	protected function _isValidOrder(Mage_Sales_Model_Order $order)
	{
		return true;
	}
	
	/**
	 * We don't need a specific order status for turnover rules
	 * 
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	protected function _checkOrderStatus(Mage_Sales_Model_Order $order)
	{
		return true;
	}
}