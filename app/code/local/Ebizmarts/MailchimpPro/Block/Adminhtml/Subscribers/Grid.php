  <?php

  class Ebizmarts_MailchimpPro_Block_Adminhtml_Subscribers_Grid extends Mage_Adminhtml_Block_Widget_Grid
  {
      public function __construct(){

          parent::__construct();
          $this->setId('subscribersGrid');
          $this->setDefaultSort('mailchimppro_id');
          $this->setDefaultDir('DESC');
          $this->setSaveParametersInSession(true);
      }

      protected function _prepareCollection(){

          $collection = Mage::getModel('mailchimpPro/subscripter')->getCollection();
          $this->setCollection($collection);
           return parent::_prepareCollection();
       }

       protected function _prepareColumns(){

           $this->addColumn('mailchimppro_id', array(
               'header'    => Mage::helper('mailchimpPro')->__('Id'),
               'align'     =>'left',
               'width'     => '10px',
               'index'     => 'mailchimppro_id'
           ));

           $this->addColumn('store_id', array(
               'header'    => Mage::helper('mailchimpPro')->__('Store Id'),
               'align'     =>'left',
               'width'     => '10px',
               'index'     => 'store_id'
           ));

           $this->addColumn('customer_id', array(
               'header'    => Mage::helper('mailchimpPro')->__('Customer Id'),
               'align'     => 'left',
               'width'     => '10px',
               'index'     => 'customer_id'
           ));

           $this->addColumn('current_email', array(
               'header'    => Mage::helper('mailchimpPro')->__('Email'),
               'align'     => 'left',
               'index'     => 'current_email'
           ));

          $this->addColumn('list_id', array(
              'header'    => Mage::helper('mailchimpPro')->__('list Id'),
              'width'     => '150px',
              'align'     => 'center',
              'type'      => 'options',
              'options'   => Mage::getSingleton('mailchimpPro/source_listIds')->toOptionArray(true,true),
              'index'     => 'list_id'
          ));

          $this->addColumn('updated_time', array(
              'header'    => Mage::helper('mailchimpPro')->__('Updated Time'),
              'align'     => 'left',
              'width'     => '180px',
              'type'      => 'datetime',
              'default'   => '--',
              'index'     => 'updated_time',
          ));

          $this->addColumn('is_subscribed', array(
              'header'    => Mage::helper('mailchimpPro')->__('Status'),
              'align'     => 'center',
              'width'     => '120px',
              'index'     => 'is_subscribed',
              'type'      => 'options',
              'options'   => array(
                  1 => 'Subscribed',
                  0 => 'Not Subscribed',
              ),
          ));
          return parent::_prepareColumns();
      }

      public function getRowUrl($row){

      	if($row->getData('customer_id') > 0){
      		return $this->getUrl('adminhtml/customer/edit/', array('id' =>$row->getCustomerId()));
      	}
      }
  }