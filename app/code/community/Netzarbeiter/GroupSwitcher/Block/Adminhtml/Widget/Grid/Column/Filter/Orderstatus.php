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
class Netzarbeiter_GroupSwitcher_Block_Adminhtml_Widget_Grid_Column_Filter_Orderstatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
	/**
	 * If I don't want order states to be in the select add them here
	 *
	 * @var array
	 */
	protected $_filterStates = array(
		//'pending_paypal', 'pending_amazon_asp'
	);

	protected function _getFilterStates()
	{
		return $this->_filterStates;
	}

    protected function _getOptions()
    {
		$options = array(array('value' => '', 'label' => Mage::helper('GroupSwitcher')->__('All Order States')));
		$states = Mage::getConfig()->getNode('global/sales/order/statuses')->asArray();
		foreach ($states as $value => $state)
		{
			if (in_array($value, $this->_getFilterStates())) continue;
			$options[] = array('value' => $value, 'label' => Mage::helper('GroupSwitcher')->__($state['label']));
		}
        return $options;
    }
}