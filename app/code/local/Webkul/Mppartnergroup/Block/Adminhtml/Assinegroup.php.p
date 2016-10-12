<?php
class Webkul_Mppartnergroup_Block_Adminhtml_Assinegroup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_assinegroup';
    $this->_blockGroup = 'mppartnergroup';
    $this->_headerText = Mage::helper('mppartnergroup')->__('Assine Group Manager');
    $this->_addButtonLabel = Mage::helper('mppartnergroup')->__('Add Group To Customer');
    parent::__construct();
  }
}