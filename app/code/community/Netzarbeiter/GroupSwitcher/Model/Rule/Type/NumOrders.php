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
class Netzarbeiter_GroupSwitcher_Model_Rule_Type_NumOrders
	extends Netzarbeiter_GroupSwitcher_Model_Rule_Type_Abstract
{
	protected $_customerId;
	protected $_orderCount;
	protected $_orderStatusAttribute;

	/**
	 * Check if the total number of orders is equal or higher the rule value
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Mage_Sales_Model_Order $order
	 * @return bool
	 */
	public function _match(Mage_Customer_Model_Customer $customer, Mage_Sales_Model_Order $order)
	{
		$orderCount = $this->_getCustomerOrderCount($customer, $order);
		
		return $orderCount >= intval($this->getRule()->getRuleValue());
	}

	/**
	 * Return the number of orders the customer has placed. If the rule applies only
	 * to a specific order status, only count the orders in that specific status.
	 *
	 * Using raw SQL is ugly, but using a collection is too slow at this place (imo).
	 *
	 * @param Mage_Customer_Model_Customer $customer
	 * @param Mage_Sales_Model_Order $order
	 * @return int
	 */
	protected function _getCustomerOrderCount(Mage_Customer_Model_Customer $customer, Mage_Sales_Model_Order $order)
	{
		if (isset($this->_customerId) && $this->_customerId == $customer->getId())
		{
			return $this->_orderCount;
		}

		$this->_customerId = $customer->getId();
		$resource = $order->getResource();
		$adapter = $resource->getReadConnection();
		$bind = array('customer_id' => $customer->getId());
		$select = $adapter->select()
			->from(array('main_table' => $resource->getTable('sales/order')), 'COUNT(*)')
			->where('main_table.customer_id=:customer_id')
			;
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
		$this->_orderCount = $adapter->fetchOne($select, $bind);
		
		return $this->_orderCount;
	}

	protected function _getOrderStatusAttribute()
	{
		if (! isset($this->_orderStatusAttribute))
		{
			$this->_orderStatusAttribute = Mage::getModel('sales/order')->getResource()->getAttribute('status');;
		}
		return $this->_orderStatusAttribute;
	}
}