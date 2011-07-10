<?php
class Ebizmarts_MailchimpPro_ManageController extends Mage_Core_Controller_Front_Action {

	public function multiSaveAction(){

        if (!$this->_validateFormKey()) {
            return $this->_redirect('newsletter/manage/');
        }
        try {

			$params = (Mage::app()->getRequest()->getParams())? Mage::app()->getRequest()->getParams() : array();
			Mage::helper('mailchimpPro')->mailChimpProFilter(Mage::getSingleton('customer/session')->getCustomer(),$params);

            if (isset($params['list'])) {
            	if(count($params['list']) > 0){
            		Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription(s) has been updated.'));
            	}else{
            		Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been updated.'));
            	}
            } else {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription(s) has been removed.'));
            }
        }
        catch (Exception $e) {
        	Mage::helper('mailchimpPro')->addException($e);
        }
        $this->_redirect('newsletter/manage/');
    }

}
?>
