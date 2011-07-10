<?php

class Ebizmarts_MailchimpPro_Block_Newsletter_Subscribe extends Mage_Newsletter_Block_Subscribe {

	protected function _prepareLayout(){

  		parent::_prepareLayout();
	  	if(!$this->getRequest()->isXmlHttpRequest()){
	  		if(Mage::helper('mailchimpPro')->addAddons(Mage::app()->getStore()->getStoreId())) $this->getLayout()->getBlock('head')->addItem('skin_js', 'mailchimppro/js/MailChimpProAdds.js');
	  	}
	}

	public function canShowSmallBox(){

		$store = Mage::app()->getStore()->getStoreId();
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$email = ($customer->getEmail())? $customer->getEmail() : '' ;
		$helper = Mage::helper('mailchimpPro');

		if($helper->mailChimpProEnabled($store) && !$helper->isSubscribed($email)){
				return true;
		}
        return false;
    }

	public function canShowMediumBox(){

    	$email = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress()->getEmail();
		if($this->canShowSmallBox() && !Mage::helper('mailchimpPro')->isSubscribed($email)){
				return true;
		}
        return false;
    }

	public function isForcedToSubscribeCheckout(){

		$store = Mage::app()->getStore()->getStoreId();
		$helper = Mage::helper('mailchimpPro');

		if((bool)$helper->getSubscribeConfig('forece_checkout',$store)){
			return true;
		}
        return false;
    }

	public function getFormActionUrl(){

    	if(!parent::getFormActionUrl()){
    		return $this->getUrl('newsletter/subscriber/new', array('_secure' => true));
    	}
		return parent::getFormActionUrl();
    }

}
?>
