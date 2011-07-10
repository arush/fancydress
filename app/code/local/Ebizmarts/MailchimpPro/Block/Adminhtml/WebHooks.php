<?php

class Ebizmarts_MailchimpPro_Block_Adminhtml_WebHooks extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct(){

    	$this->_controller = 'adminhtml_webHooks';
        $this->_blockGroup = 'mailchimpPro';
        $this->_headerText = Mage::helper('mailchimpPro')->__('MailChimp Pro - WebHooks for Selected Lists');
		parent::__construct();
    	$this->_addButton('add', array(
			            'label'     => 'WebHooks Syncronization',
			            'onclick'   => 'submitMyHooks(\'' . Mage::helper('adminhtml')->getUrl('mailchimpPro/adminhtml_webHooks/new') . '\')',
			            'class'     => 'add'
    					));
      }

      protected function _prepareLayout(){

      	parent::_prepareLayout();
      	if(!$this->getRequest()->isXmlHttpRequest()){
      		$this->getLayout()->getBlock('head')->addItem('skin_js', 'mailchimppro/MailChimpPro.js');
      	}
      }
  }