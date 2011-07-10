<?php

class Ebizmarts_MailchimpPro_Adminhtml_SubscribersController extends Mage_Adminhtml_Controller_Action{

	protected function _initAction(){

    	$this->loadLayout()
             ->_setActiveMenu('mailchimpPro/subscribers')
             ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('mailchimpPro')->__('Subscribers List'));
		$this->getLayout()->getBlock('head')->setTitle($this->__('MailChimp Pro - Subscribers for all lists'));
		return $this;
    }

	public function indexAction() {

    	$this->_initAction()
        	 ->_addContent($this->getLayout()->createBlock('mailchimpPro/adminhtml_subscribers'))
          	 ->renderLayout();
		$this->getLayout()->getBlock('head')->setTitle($this->__('MailChimp Pro - WebHooks for Selected Lists'));
	}

	public function newAction(){


     	Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('adminhtml')->__('All subscripters has been synchronized.'));
        $this->_redirect('*/*/');
	}

}