<?php

class Webkul_Mppartnergroup_Block_Adminhtml_Mppartnergroup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'mppartnergroup';
        $this->_controller = 'adminhtml_mppartnergroup';
        
        $this->_updateButton('save', 'label', Mage::helper('mppartnergroup')->__('Save Group'));
        $this->_updateButton('delete', 'label', Mage::helper('mppartnergroup')->__('Delete Group'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('mppartnergroup_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'mppartnergroup_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'mppartnergroup_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('mppartnergroup_data') && Mage::registry('mppartnergroup_data')->getId() ) {
            return Mage::helper('mppartnergroup')->__("Edit Group '%s'", $this->htmlEscape(Mage::registry('mppartnergroup_data')->getTitle()));
        } else {
            return Mage::helper('mppartnergroup')->__('Add Group');
        }
    }
}