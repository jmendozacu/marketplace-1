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
class Netzarbeiter_GroupSwitcher_Block_Adminhtml_Groupswitcher_Rules_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
	protected $_groupsAssoc;

	protected $_defaultOrderStatus = 'complete';

	protected function _prepareForm()
	{
		if (Mage::getSingleton('adminhtml/session')->getGroupswitcherData())
		{
			$data = Mage::getSingleton('adminhtml/session')->getGroupswitcherData();
			Mage::getSingleton('adminhtml/session')->setGroupswitcherData(null);
		}
		elseif (Mage::registry('groupswitcher_data'))
		{
			$data = Mage::registry('groupswitcher_data')->getData();
		}
		else
		{
			$data = array();
		}

		/*
		 * Set some sane defults
		 */
		if (! isset($data['order_status']))
		{
			$data['order_status'] = $this->_defaultOrderStatus;
		}
		if (! isset($data['is_active']))
		{
			$data['is_active'] = 1;
		}
		if (! isset($data['stop_processing']))
		{
			$data['stop_processing'] = 1;
		}

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('rule_form', array(
			'legend' =>Mage::helper('GroupSwitcher')->__('Rule Information')
		));

		$fieldset->addField('name', 'text', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Rule Name'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'name',
				'note'		=> Mage::helper('GroupSwitcher')->__('For your internal use only'),
		));

		$fieldset->addField('group_id_after', 'select', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Switch customer to group'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'group_id_after',
				'options'	=> $this->_getGroupOptionsHash(false),
		));

		$fieldset->addField('is_active', 'select', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Is Active'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'is_active',
				'options'	=> $this->_getIsActiveOptionsHash(false),
		));

		$fieldset->addField('priority', 'text', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Priority'),
				'class'     => '',
				'required'  => false,
				'name'      => 'priority',
				'note'		=> Mage::helper('GroupSwitcher')->__('Matching rules with a higher number will be processed first'),
		));

		$fieldset->addField('stop_processing', 'select', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Stop Processing'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'stop_processing',
				'options'	=> $this->_getStopProcessingOptionsHash(false),
				'note'		=> Mage::helper('GroupSwitcher')->__('Don\'t process further matching rules if this one applies'),
		));

		$fieldset->addField('note', 'textarea', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Comments'),
				'class'     => '',
				'required'  => false,
				'name'      => 'note',
				'note'		=> Mage::helper('GroupSwitcher')->__('For your internal use only'),
		));
		

		$fieldset = $form->addFieldset('conditions', array(
			'legend' =>Mage::helper('GroupSwitcher')->__('Conditions')
		));

		$fieldset->addField('rule_type', 'select', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Rule Trigger'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'rule_type',
				'options'	=> $this->_getRuleTypeOptionsHash(),
		));

		$fieldset->addField('rule_value', 'text', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Rule Condition'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'rule_value',
				'note'		=> Mage::helper('GroupSwitcher')->__('For products enter the SKU, for turnover time enter N|amount, for all others enter the amount '),
		));

		$fieldset->addField('group_id_before', 'select', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Apply rule if customer is in group'),
				'class'     => '',
				'required'  => false,
				'name'      => 'group_id_before',
				'options'	=> $this->_getGroupOptionsHash(true),
		));

		$fieldset->addField('order_status', 'select', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Order Status must be'),
				'class'     => '',
				'required'  => false,
				'name'      => 'order_status',
				'options'	=> $this->_getOrderStatusOptionsHash(),
				'note'		=> Mage::helper('GroupSwitcher')->__('Only orders in the selected status are checked'),
		));

		$fieldset->addField('store_ids', 'multiselect', array(
				'label'     => Mage::helper('GroupSwitcher')->__('Stores'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'store_ids[]',
				'values'	=> $this->_getStoresOptionHash(),
				'note'		=> Mage::helper('GroupSwitcher')->__('Only orders associated with these stores are checked'),
		));

		$form->setValues($data);

		return parent::_prepareForm();
	}

	protected function _getGroupOptionsHash($withAny = false)
	{
		$options = array();
		$firstOption = $withAny ? 'Any' : '--- Please Choose ---';
		$options[''] = Mage::helper('GroupSwitcher')->__($firstOption);
		
		if (! isset($this->_groupsAssoc))
		{
			$this->_groupsAssoc = Mage::getModel('customer/group')->getCollection()
				->setRealGroupsFilter()
				->toOptionHash();
		}
		foreach ($this->_groupsAssoc as $key => $val)
		{
			$options[$key] = $val;
		}
		return $options;
	}

	protected function _getRuleTypeOptionsHash()
	{
		$options = array('' => Mage::helper('GroupSwitcher')->__('--- Please Choose ---'));
		$types = Mage::getConfig()->getNode('global/groupswitcher/rule/types')->asArray();
		foreach ($types as $value => $type)
		{
			$options[$value] = Mage::helper('GroupSwitcher')->__($type['label']);
		}
		return $options;
	}

	protected function _getOrderStatusOptionsHash()
	{
		$options = array();
		$firstOption = 'Anything';
		$options[''] = Mage::helper('GroupSwitcher')->__($firstOption);

		$states = Mage::getConfig()->getNode('global/sales/order/statuses')->asArray();
		foreach ($states as $value => $state)
		{
			$options[$value] = Mage::helper('GroupSwitcher')->__($state['label']);
		}
		return $options;
	}

	protected function _getIsActiveOptionsHash()
	{
		$options = array(
			0 => Mage::helper('GroupSwitcher')->__('Inactive'),
			1 => Mage::helper('GroupSwitcher')->__('Active'),
		);
		return $options;
	}

	protected function _getStopProcessingOptionsHash()
	{
		$options = array(
			0 => Mage::helper('GroupSwitcher')->__('No'),
			1 => Mage::helper('GroupSwitcher')->__('Yes'),
		);
		return $options;
	}

	protected function _getStoresOptionHash()
	{
		$options = array();
		foreach ($websites = Mage::app()->getWebsites() as $website)
		{
			foreach ($website->getGroups() as $group)
			{
				foreach ($group->getStores() as $store)
				{
					//$options[$store->getId()] = $store->getName();
					$wsName = $website->getName();
					$stName = $group->getName();
					$svName = $store->getName();
					if (strlen($wsName) > 10) $wsName = substr($wsName, 0, 8) . '...';
					if (strlen($stName) > 10) $stName = substr($stName, 0, 8) . '...';
					
					$options[] = array(
						'value' => $store->getId(),
						'label' => $wsName . ' / ' . $stName . ' / ' . $svName
					);
					
				}
			}
		}
		return $options;
	}
}