<?php

class Ebizmarts_MailchimpPro_Model_Subscripter extends Mage_Core_Model_Abstract{

	public function _construct(){

		parent::_construct();
  		$this->_init('mailchimpPro/subscripter');
	}

	public function prepareCustomer($data){

		$email = (isset($data['data']['email']))? $data['data']['email'] : $data['data']['old_email'] ;
		$customerModel = Mage::getModel('customer/customer');
		$email = $data->getEmail();

		$customer = $customerModel->getCollection()
								  ->addFieldToFilter('email',$email)
								  ->getFirstItem();

		if(!$customer->getEntityId()){
			$customer = $customerModel;
			$customer->setEntityId('0')
					 ->setEmail($email)
					 ->setStoreId(Mage::app()->getDefaultStoreView()->getStoreId());
		}

		$memberId = (isset($data['data']['id']))? $data['data']['id'] : $data['data']['new_id'] ;

		$customer->setAction($data['type'])
				 ->setListId($data['data']['list_id'])
				 ->setMemberId($memberId);

		$subscripter = Mage::getModel('mailchimpPro/subscripter')->getCollection()
							->addFieldToFilter('list_id',$data['data']['list_id'])
							->addFieldToFilter('current_email',$email)
							->getFirstItem();

		$customer->setMailchimpproId($subscripter->getMailchimpproId());

		if(!$subscripter->getMailchimpproId() && ($data['type'] == 'upemail' || $data['type'] == 'profile')){
			$customer->setAction('subscribe');
		}
		if(isset($data['data']['new_email'])){
			$newEmail = $data['data']['new_email'];
		}elseif(isset($data['data']['merges']['EMAIL'])){
			$newEmail = $data['data']['merges']['EMAIL'];
		}else{
			$newEmail = $customer->getEmail();
		}

		$customer->setEmail($newEmail);
		return $customer;
	}

	public function registerInfo($customer){

		$subscripterModel = Mage::getModel('mailchimpPro/subscripter');

		if($customer->getMailchimpproId()){
			$subscripterModel->load($customer->getMailchimpproId());
			if($customer->getAction() == 'unsubscribe'){
				$subscripterModel->setIsSubscribed((bool)false);
			}else{
				$subscripterModel
					->setCustomerId($customer->getCustomerId())
					->setCurrentEmail($customer->getEmail())
					->setIsSubscribed((bool)true);
			}
		}else{
			$subscripterModel
		      ->setCustomerId($customer->getCustomerId())
		      ->setIsSubscribed((bool)true)
		      ->setCurrentEmail($customer->getEmail())
		      ->setListId($customer->getListId())
		      ->setStoreId($customer->getStoreId())
		      ->setCreatedTime(date("Y-m-d H:i:s",time()));
		}

		$subscripterModel
			  ->setMemberId($customer->getMemberId())
		      ->setUpdatedTime(date("Y-m-d H:i:s",time()))
		      ->save();
	}
}
