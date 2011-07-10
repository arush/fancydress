<?php

class Ebizmarts_MailchimpPro_Model_Subscriber extends Mage_Newsletter_Model_Subscriber {

	/*public function subscribeCustomer($customer){

		$params = Mage::app()->getRequest()->getParams();
    	if(Mage::helper('mailchimpPro')->checkSubscribe() || !isset($params['is_subscribed'])){
    		return parent::subscribeCustomer($customer);
    	}else{
    		return $this;
    	}
    }*/

 	public function subscribe($email,$fromWebHook = null){

 		$customerSession = Mage::getSingleton('customer/session');
		$ownerId = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($email)
            ->getId();
        if (($customerSession->isLoggedIn() && $ownerId == $customerSession->getId())
        	|| $fromWebHook
        	|| Mage::helper('mailchimpPro')->checkSubscribe()){
            return parent::subscribe($email);
        }
        else {
            return $this->register($email,$ownerId);
        }

    }

	public function register($email,$ownerId){

        $this->loadByEmail($email);
        $customerSession = Mage::getSingleton('customer/session');

        if(!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED || $this->getStatus() == self::STATUS_NOT_ACTIVE) {
            // if user subscribes own login email - confirmation is not needed
            if ($customerSession->isLoggedIn() && $ownerId == $customerSession->getId()){
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }
            else {
                $this->setStatus(self::STATUS_NOT_ACTIVE);
            }
            $this->setSubscriberEmail($email);
        }

        if ($customerSession->isLoggedIn()) {
            $this->setStoreId($customerSession->getCustomer()->getStoreId());
            $this->setCustomerId($customerSession->getCustomerId());
        } else {
            $this->setStoreId(Mage::app()->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setIsStatusChanged(true);

        try {
            $this->save();
            return $this->getStatus();
        }
        catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function sendConfirmationRequestEmail() {

    	if(Mage::helper('mailchimpPro')->checkSendSubscribe()){
    		return parent::sendConfirmationRequestEmail();
    	}else{
    		return $this;
    	}
    }

    public function sendConfirmationSuccessEmail() {

    	if(Mage::helper('mailchimpPro')->checkSendSubscribe()){
    		return parent::sendConfirmationSuccessEmail();
    	}else{
    		return $this;
    	}
    }

    public function sendUnsubscriptionEmail() {

    	if(Mage::helper('mailchimpPro')->checkSendUnsubscribe()){
    		return parent::sendUnsubscriptionEmail();
    	}else{
    		return $this;
    	}
    }

}
?>
