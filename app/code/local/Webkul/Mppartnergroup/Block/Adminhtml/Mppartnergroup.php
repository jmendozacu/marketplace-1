<?php
class Webkul_Mppartnergroup_Block_Adminhtml_Mppartnergroup extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_mppartnergroup';
    $this->_blockGroup = 'mppartnergroup';
    $this->_headerText = Mage::helper('mppartnergroup')->__('Partner Group Manager');
    $this->_addButtonLabel = Mage::helper('mppartnergroup')->__('Add Group');
    parent::__construct();
  }
}