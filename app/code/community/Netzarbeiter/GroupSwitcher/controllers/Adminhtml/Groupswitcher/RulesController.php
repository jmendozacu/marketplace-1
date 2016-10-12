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
class Netzarbeiter_GroupSwitcher_Adminhtml_Groupswitcher_RulesController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('customer/groups');
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Customer'), Mage::helper('adminhtml')->__('Customer'));
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Customer Groups'), Mage::helper('adminhtml')->__('Customer Groups'));
		$this->_addBreadcrumb(Mage::helper('GroupSwitcher')->__('Switch Group Rules'), Mage::helper('GroupSwitcher')->__('Switch Group Rules'));

		return $this;
	}

	public function indexAction()
	{
		$this->_initAction();
		$this->renderLayout();
	}

	public function newAction()
	{
		$this->_forward('edit');
	}

	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$model = Mage::getModel('GroupSwitcher/rule')->load((int) $id);

		if ($model->getId() || ! $id)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if ($data)
			{
				$model->setData($data)
					->setId($id);
			}

			Mage::register('groupswitcher_data', $model);

			$this->_initAction();

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('GroupSwitcher/adminhtml_groupswitcher_rules_edit'));
			$this->_addLeft($this->getLayout()->createBlock('GroupSwitcher/adminhtml_groupswitcher_rules_edit_tabs'));

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('GroupSwitcher')->__('Rule does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			$model = Mage::getModel('GroupSwitcher/rule');

			$id = $this->getRequest()->getParam('id');
			if ($id) $model->setId($id);
			
			Mage::getSingleton('adminhtml/session')->setFormData($data);
			
			try
			{
				/*
				 * Input validation
				 */
				if ($data)
				{
					$data['rule_value'] = isset($data['rule_value']) ? trim($data['rule_value']) : '';

					/*
					 * Check conditions
					 */
					switch ($data['rule_type'])
					{
						case 'buy_product':

							if (! Mage::getModel('catalog/product')->getIdBySku($data['rule_value']))
							{
								Mage::getSingleton('adminhtml/session')->addNotice(
									Mage::helper('GroupSwitcher')->__('I can\'t find a product with the SKU "%s", but I will save the rule anyway.', $data['rule_value'])
								);
							}
							break;

						case 'num_orders':

							if (! $data['rule_value'])
							{
								Mage::throwException(Mage::helper('GroupSwitcher')->__('Please enter a rule condition value larger then zero.'));
							}

							if (intval($data['rule_value']) != $data['rule_value'])
							{
								Mage::throwException(Mage::helper('GroupSwitcher')->__('Please enter a positive integer as the condition value.'));
							}
							break;
							
						case 'order_total':
						case 'total_orders':
							
							if (! $data['rule_value'])
							{
								Mage::throwException(Mage::helper('GroupSwitcher')->__('Please enter a condition value larger then zero.'));
							}

							$data['rule_value'] = str_replace(',', '.', $data['rule_value']);
							if (! preg_match('/^\+?[1-9][0-9]*(.[0-9]*)$/', $data['rule_value']))
							{
								Mage::throwException(Mage::helper('GroupSwitcher')->__('Please enter a positive number as the condition value.'));
							}
							break;
					}
				}

				$model->setData($data);
				if ($id) $model->setId($id);

				$model->save();

				if (! $model->getId())
				{
					Mage::throwException(Mage::helper('GroupSwitcher')->__('Error saving rule'));
				}

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('GroupSwitcher')->__('Rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
				}
				else
				{
					$this->_redirect('*/*/');
				}
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				if ($model && $model->getId())
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
				}
				else
				{
					$this->_redirect('*/*/');
				}
			}

			return;
		}

		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('GroupSwitcher')->__('No data found to save'));
		$this->_redirect('*/*/');
	}

	public function deleteAction()
	{
		if ($id = $this->getRequest()->getParam('id'))
		{
			try
			{
				Mage::getModel('GroupSwitcher/rule')->load($id)->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('GroupSwitcher')->__('Rule successfully deleted'));
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

}