<?php

class Webkul_Mppartnergroup_Block_Adminhtml_Mppartnergroup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('mppartnergroup_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('mppartnergroup')->__('Group Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('mppartnergroup')->__('Group Information'),
          'title'     => Mage::helper('mppartnergroup')->__('Group Information'),
          'content'   => $this->getLayout()->createBlock('mppartnergroup/adminhtml_mppartnergroup_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}