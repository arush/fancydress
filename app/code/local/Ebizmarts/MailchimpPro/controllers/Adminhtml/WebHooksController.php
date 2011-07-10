<?php

class Ebizmarts_MailchimpPro_Adminhtml_WebHooksController extends Mage_Adminhtml_Controller_Action{

	protected function _initAction(){

    	$this->loadLayout()
             ->_setActiveMenu('mailchimpPro/webHooks')
             ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('mailchimpPro')->__('WebHooks List'));
		$this->getLayout()->getBlock('head')->setTitle($this->__('MailChimp Pro - WebHooks for Selected Lists'));
		return $this;
    }

	public function indexAction() {

    	$this->_initAction()
        	 ->_addContent($this->getLayout()->createBlock('mailchimpPro/adminhtml_webHooks'))
          	 ->renderLayout();
	}

	public function newAction(){

		$mod = 0;

     	foreach($this->getRequest()->getPost() as $list){
     		$items = explode('&',$list);

     		if(is_array($items) && count($items) > 1){
     			Mage::getModel('mailchimpPro/webHooks')->mainAction($items);
     			$mod++;
     		}
    	}

     	if($mod > 0){
     		Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Total of %d list(s) were updated.', $mod));
     	}

	}

}