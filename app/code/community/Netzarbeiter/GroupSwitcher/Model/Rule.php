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
class Netzarbeiter_GroupSwitcher_Model_Rule extends Mage_Core_Model_Abstract
{
	protected $_eventPrefix = 'groupswitcher_rule';

	protected $_eventObject = 'rule';

	protected function _construct()
	{
		parent::_construct();
		$this->_init('GroupSwitcher/rule');
	}

	public function match(Mage_Customer_Model_Customer $customer, Mage_Sales_Model_Order $order)
	{
		return $this->getTypeModel()->match($customer, $order);
	}

	public function getTypeModel()
	{
		$modelClass = (string) Mage::getConfig()->getNode('global/groupswitcher/rule/types/' . $this->getRuleType() . '/model');

		if (! $modelClass)
		{
			Mage::throwException(Mage::helper('GroupSwitcher')->__('Unable to find model for rule type "%s"', $this->getRuleType()));
		}

		$typeModel = Mage::getSingleton($modelClass)
			->setRule($this);

		return $typeModel;
	}

	public function getStoreIds()
	{
		$data = $this->getData('store_ids');
		if (is_string($data))
		{
			$data = explode(',', $data);
			$this->setData($data);
		}
		return $data;
	}

	protected function _beforeSave()
	{
		/*
		 * Check values are positive or NULL for any
		 */
		
		$groupIdBefore = $this->getGroupIdBefore();
		if (isset($groupIdBefore) && ! $groupIdBefore)
		{
			$this->setGroupIdBefore(null);
		}

		$orderStatus = $this->getOrderStatus();
		if (isset($orderStatus) && ! $orderStatus)
		{
			$this->setOrderStatus(null);
		}

		$store_ids = $this->getStoreIds();
		if (is_array($store_ids))
		{
			$this->setStoreIds(implode(',', $store_ids));
		}
		
		return parent::_beforeSave();
	}
}