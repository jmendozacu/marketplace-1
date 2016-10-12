<?php

class Webkul_Mppartnergroup_Block_Adminhtml_Mppartnergroup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('mppartnergroup_form', array('legend'=>Mage::helper('mppartnergroup')->__('Group information')));
     
      $fieldset->addField('group_name', 'text', array(
          'label'     => Mage::helper('mppartnergroup')->__('Group Name'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'group_name',
      ));

      $fieldset->addField('group_code', 'text', array(
          'label'     => Mage::helper('mppartnergroup')->__('Group Code'),
          'required'  => true,
          'name'      => 'group_code',
		  'class'	=> 'validate-code',
	  ));
	  
	  $fieldset->addField('no_of_products', 'text', array(
          'label'     => Mage::helper('mppartnergroup')->__('Number Of Products Allowed'),
          'required'  => true,
          'name'      => 'no_of_products',
	  ));
	   $fieldset->addField('time_periods', 'text', array(
          'label'     => Mage::helper('mppartnergroup')->__('Time'),
          'required'  => true,
          'name'      => 'time_periods',
		  'class'	=>'validate-number',
		  'comment'=>'hello'
	  ));
	  $currency_code=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	  $fieldset->addField('fee_amount', 'text', array(
          'label'     => Mage::helper('mppartnergroup')->__('Fee Amount (in currency '.$currency_code.' )'),
          'required'  => true,
          'name'      => 'fee_amount',
		  'class'	  =>'validate-zero-or-greater',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('mppartnergroup')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('mppartnergroup')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('mppartnergroup')->__('Disabled'),
              ),
          ),
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getMppartnergroupData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getMppartnergroupData());
          Mage::getSingleton('adminhtml/session')->setMppartnergroupData(null);
      } elseif ( Mage::registry('mppartnergroup_data') ) {
          $form->setValues(Mage::registry('mppartnergroup_data')->getData());
      }
      return parent::_prepareForm();
  }
}