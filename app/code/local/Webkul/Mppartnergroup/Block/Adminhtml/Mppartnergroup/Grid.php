<?php

class Webkul_Mppartnergroup_Block_Adminhtml_Mppartnergroup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('mppartnergroupGrid');
      $this->setDefaultSort('group_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('mppartnergroup/mppartnergroup')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('group_id', array(
          'header'    => Mage::helper('mppartnergroup')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'group_id',
      ));

      $this->addColumn('group_name', array(
          'header'    => Mage::helper('mppartnergroup')->__('Group Name'),
          'align'     =>'left',
          'index'     => 'group_name',
      ));
	  
	  $this->addColumn('group_code', array(
          'header'    => Mage::helper('mppartnergroup')->__('Group Code'),
          'align'     =>'left',
          'index'     => 'group_code',
      ));
	  $this->addColumn('no_of_products', array(
          'header'    => Mage::helper('mppartnergroup')->__('Number Of Products'),
          'align'     =>'left',
          'index'     => 'no_of_products',
      ));
	  $this->addColumn('time_periods', array(
          'header'    => Mage::helper('mppartnergroup')->__('Time In Months'),
          'align'     =>'left',
          'index'     => 'time_periods',
      ));
	  $currency_code=Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();
	   $this->addColumn('fee_amount', array(
          'header'    => Mage::helper('mppartnergroup')->__('Fee Amount in '.$currency_code),
          'align'     =>'left',
          'index'     => 'fee_amount',
      ));
      $this->addColumn('status', array(
          'header'    => Mage::helper('mppartnergroup')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('mppartnergroup')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('mppartnergroup')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('mppartnergroup')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('mppartnergroup')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('mppartnergroup_id');
        $this->getMassactionBlock()->setFormFieldName('mppartnergroup');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('mppartnergroup')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('mppartnergroup')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('mppartnergroup/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('mppartnergroup')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('mppartnergroup')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}