<?php

class Ebizmarts_MailchimpPro_Block_Adminhtml_Subscribers extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct(){

    	$this->_controller = 'adminhtml_subscribers';
        $this->_blockGroup = 'mailchimpPro';
        $this->_headerText = Mage::helper('mailchimpPro')->__('MailChimp Pro - Subscribers for all lists');
		//$this->_addButtonLabel = Mage::helper('mailchimpPro')->__('Full Synchronization');
		parent::__construct();
		$this->_removeButton('add');

      }

  }