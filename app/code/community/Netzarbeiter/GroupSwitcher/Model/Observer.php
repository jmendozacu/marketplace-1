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
class Netzarbeiter_GroupSwitcher_Model_Observer
{
	public function orderStatusHistorySaveAfter($observer)
	{
		$orderStatus = $observer->getEvent()->getStatus();
		$order = $orderStatus->getOrder();

		if (! $order->getCustomerId())
		{
			/*
			 * Guest order
			 */
			return;
		}

		$storeId = $order->getStoreId();

		if (! Mage::helper('GroupSwitcher')->getConfig('enable_ext', $storeId))
		{
			/*
			 * Module deactivated for the order store config scope
			 */
			return;
		}

		$this->_applyRules($order);
	}
	
	public function customerLogin($observer)
	{
		$customer = $observer->getEvent()->getCustomer();

		if (! $customer->getId())
		{
			/*
			 * o_O
			 * Guest? Can't see how that should be possible, but why not be a little paranoid
			 */
			return;
		}
		$storeId = Mage::app()->getStore()->getId();
		
		/*
		 * Dummy order model instance :)
		 */
		$order = Mage::getModel('sales/order')->setStoreId($storeId);
		
		$this->_applyRules($order, $customer, 'login');
	}

	protected function _applyRules(Mage_Sales_Model_Order $order, Mage_Customer_Model_Customer $customer = null, $ruleEventFilter = false)
	{
		if (! isset($customer))
		{
			$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
		}
		
		$ruleCollection = Mage::getResourceModel('GroupSwitcher/rule_collection')
			->addGroupIdFilter($customer->getGroupId())
			->addOrderStatusFilter($order->getStatus())
			->addActiveFilter()
			->addStoreIdFilter($order->getStoreId())
			->addOrder('priority');
			;
		
		if ($ruleEventFilter)
		{
			$ruleCollection->addEventFilter($ruleEventFilter);
		}
		
		foreach ($ruleCollection as $rule)
		{
			if ($rule->match($customer, $order))
			{
				$customer->setGroupId($rule->getGroupIdAfter())
					->save();
				
				if ($rule->getStopProcessing())
				{
					break;
				}
			}
		}
	}
}