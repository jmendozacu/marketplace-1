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
class Netzarbeiter_GroupSwitcher_Block_Adminhtml_Groupswitcher_Rules_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('rules_grid');
		$this->setDefaultSort('priority');
		$this->setDefaultDir('desc');
		$this->setSaveParametersInSession(true);
		//$this->setUseAjax(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('GroupSwitcher/rule')->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		/*
		$this->addColumn('rule_id', array(
			'header' => Mage::helper('GroupSwitcher')->__('ID'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'id',
			'filter' => false,
		));
		 */
		$this->addColumn('priority', array(
			'header' => Mage::helper('GroupSwitcher')->__('Priority'),
			'align' => 'right',
			'width' => '50px',
			'index' => 'priority',
			'filter' => false,
		));
		$this->addColumn('name', array(
			'header' => Mage::helper('GroupSwitcher')->__('Rule Name'),
			'align' => 'left',
			'index' => 'name',
		));
		$this->addColumn('rule_type', array(
			'header' => Mage::helper('GroupSwitcher')->__('Rule Type'),
			'align' => 'left',
			'index' => 'rule_type',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_ruletype',
            'filter' => 'GroupSwitcher/adminhtml_widget_grid_column_filter_ruletype',
		));
		$this->addColumn('rule_value', array(
			'header' => Mage::helper('GroupSwitcher')->__('Rule Value'),
			'align' => 'left',
			'index' => 'rule_value',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_rulevalue',
		));
		$this->addColumn('group_before', array(
			'header' => Mage::helper('GroupSwitcher')->__('Apply if Customer is in Group'),
			'align' => 'left',
			'index' => 'group_id_before',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_customergroup',
            'filter' => 'GroupSwitcher/adminhtml_widget_grid_column_filter_customergroup',
		));
		$this->addColumn('group_after', array(
			'header' => Mage::helper('GroupSwitcher')->__('Switch to Group'),
			'align' => 'left',
			'index' => 'group_id_after',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_customergroup',
            'filter' => 'GroupSwitcher/adminhtml_widget_grid_column_filter_customergroup',
		));
		$this->addColumn('order_status', array(
			'header' => Mage::helper('GroupSwitcher')->__('Order Status must be'),
			'align' => 'left',
			'index' => 'order_status',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_orderstatus',
            'filter' => 'GroupSwitcher/adminhtml_widget_grid_column_filter_orderstatus',
		));
		$this->addColumn('is_active', array(
			'header' => Mage::helper('GroupSwitcher')->__('Is Active'),
			'align' => 'left',
			'index' => 'is_active',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_active',
            'filter' => 'GroupSwitcher/adminhtml_widget_grid_column_filter_active',
		));
		$this->addColumn('stop_processing', array(
			'header' => Mage::helper('GroupSwitcher')->__('Stop Processing'),
			'width' => '60px',
			'align' => 'left',
			'index' => 'stop_processing',
            'renderer' => 'GroupSwitcher/adminhtml_widget_grid_column_renderer_yesno',
            'filter' => false,
		));
		$this->addColumn('action', array(
			'header' => Mage::helper('adminhtml')->__('Action'),
			'width' => '100px',
			'type' => 'action',
			'getter' => 'getId',
			'actions' => array(
				array(
					'caption' => Mage::helper('adminhtml')->__('Edit'),
					'url' => array('base' => '*/*/edit'),
					'field' => 'id',
				),
			),
			'filter' => false,
			'sortable' => false,
			'index' => 'stores',
			'is_system' => true,
		));

		parent::_prepareColumns();
		return $this;
	}

	protected function getRowUrl($row)
	{
		$url = $this->getUrl('*/*/edit', array('id' => $row->getId()));
		return $url;
	}
}