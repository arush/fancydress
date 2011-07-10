<?php
class Ebizmarts_MailchimpPro_Model_Observer extends Mage_Core_Model_Abstract {

	protected $_current;

	private function getSubscriber($email){

		$subscriber = Mage::getSingleton('newsletter/subscriber')->loadByEmail($email);
		if(!$subscriber->getData()){
			$subscriber = Mage::getSingleton('customer/session')->getCustomer();

			if(!$subscriber->getEmail()){
				$subscriber->setStoreId(Mage::app()->getStore()->getStoreId())
						   ->setCustomerId(0)
						   ->setEmail($email);
			}
		}
		return $subscriber;
	}

    public function subscribeObserver($observer){

		$params = (Mage::app()->getRequest()->getParams())? Mage::app()->getRequest()->getParams() : array();

		if(isset($params['email'])){
			$email = $params['email'];
			$subscriber = $this->getSubscriber($email);
			Mage::helper('mailchimpPro')->mailChimpProFilter($subscriber,$params);

			/***********this lines send the customer to home page instead of the dashboard on register
	 		$cart = Mage::getSingleton('checkout/session');
			if($cart->getCartWasUpdated()) $cart->addSuccess(Mage::helper('mailchimpPro')->__('The subscription has been updated.'));
			***/
		}

        return $this;
    }

    public function customerObserver($observer){

		$customer = Mage::getSingleton('customer/session')->getCustomer();
		$email = $customer->getEmail();
		if(isset($email)){
			$params = (Mage::app()->getRequest()->getParams())? Mage::app()->getRequest()->getParams() : array();

			$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
			if(!$subscriber->getData()) $subscriber = $customer;

			Mage::helper('mailchimpPro')->mailChimpProFilter($subscriber,$params);
		}

        return $this;
    }

	public function registerObserver($observer){

		$params = (Mage::app()->getRequest()->getParams())? Mage::app()->getRequest()->getParams() : array();

		if(isset($params['is_subscribed']) || isset($params['subscribe_newsletter']) ||
			(bool)Mage::helper('mailchimpPro')->getSubscribeConfig('forece_checkout',Mage::app()->getStore()->getStoreId())){

			$email = (isset($params['billing']['email']))? $params['billing']['email'] : Mage::getSingleton('customer/session')->getCustomer()->getEmail();

			if($email){
				$newsModel = Mage::getModel('newsletter/subscriber');
				if(!$newsModel->loadByEmail($email)) $newsModel->subscribe($email);
				$subscriber = $this->getSubscriber($email);
				Mage::helper('mailchimpPro')->mailChimpProFilter($subscriber,$params);
			}
		}

        return $this;
    }

    public function updateNewObserver($observer){

		if(!$this->_current){
			$this->_current = true;

			$params = (Mage::app()->getRequest()->getParams())? Mage::app()->getRequest()->getParams() : array();

			if(isset($params['email'])/* && Mage::helper('mailchimpPro')->isSubscribed($params['email'])*/){

				$customer = $observer->getCustomer();
				$customer->setAction('update');

				$subscriptions = Mage::getModel('mailchimpPro/subscripter')->getCollection()
					->addFieldToFilter('store_id',Mage::app()->getStore()->getStoreId())
					->addFieldToFilter('customer_id',$customer->getEntityId());

				if(is_array($subscriptions) && count($subscriptions)> 0){
					foreach($subscriptions as $subscription){
						Mage::getModel('mailchimpPro/mailchimpPro')->mainAction($customer,$subscription);
					}
				}else{
					$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($params['email']);
					if(!$subscriber->getData()) $subscriber = $customer;
					Mage::helper('mailchimpPro')->mailChimpProFilter($subscriber,$params);
				}

			}
		}
        return $this;
    }

    public function updateNewAdminObserver($observer){

    	$params = (Mage::app()->getRequest()->getParams())? Mage::app()->getRequest()->getParams() : array();
    	if(isset($params['account']['email']) && isset($params['customer_id'])){
			$subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($params['account']['email']);
			if(!$subscriber->getData()){
	    		return $this;
			}elseif($subscriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED){
				Mage::log($subscriber->getData());
			}
			$params['is_general'] = true;
			$params['is_subscribed'] = (array_key_exists('subscription',$params))? true: false;

			//Mage::helper('mailchimpPro')->mailChimpProFilter($subscriber,$params);
    	}
		return $this;
    }

}
?>
