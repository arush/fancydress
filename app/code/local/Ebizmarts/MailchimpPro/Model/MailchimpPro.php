<?php

class Ebizmarts_MailchimpPro_Model_MailchimpPro extends Mage_Core_Model_Abstract{

	const ACTION_SUBSCRIBE     	= 'subscribe';
	const ACTION_UPDATE     	= 'update';
	const ACTION_UNSUBSCRIBE    = 'unsubscribe';

	public $_apikey;
	public $_apiHost;

	protected $_emailType;
	protected $_doubleOptin;
	protected $_updateExisting;
	protected $_replaceInterests;
	protected $_sendWelcome;

	protected $_deleteMember;
	protected $_sendGoodbye;
	protected $_sendNotify;

	public function setInitParams($store){

		$this->_apikey = Mage::helper('mailchimpPro')->getGeneralConfig('apikey',$store);
		$this->_apiHost = ($this->_apikey)? Mage::helper('mailchimpPro')->newMailChimpHost($this->_apikey,$store) : '';

		return $this;
	}

	private function setSubsParams($store){

		$this->_emailType = (string)Mage::helper('mailchimpPro')->getSubscribeConfig('email_type',$store);
		$this->_doubleOptin = (bool)Mage::helper('mailchimpPro')->getSubscribeConfig('double_optin',$store);
		$this->_updateExisting = (bool)Mage::helper('mailchimpPro')->getSubscribeConfig('update_existing',$store);
		$this->_replaceInterests = (bool)Mage::helper('mailchimpPro')->getSubscribeConfig('replace_interests',$store);
		$this->_sendWelcome = (bool)Mage::helper('mailchimpPro')->getSubscribeConfig('send_welcome',$store);

		return $this;
	}

	private function setUnSubsParams($store){

		$this->_deleteMember = (bool)Mage::helper('mailchimpPro')->getUnSubscribeConfig('delete_member',$store);
		$this->_sendGoodbye = (bool)Mage::helper('mailchimpPro')->getUnSubscribeConfig('send_goodbye',$store);
		$this->_sendNotify = (bool)Mage::helper('mailchimpPro')->getUnSubscribeConfig('send_notify',$store);

		return $this;
	}

	private function subscribeCustomer($customer,$client){

		$this->setSubsParams(Mage::app()->getStore()->getStoreId());
		// if user subscribes own login email - confirmation is not needed
		if($customer->getEntityId()){
			$this->_doubleOptin = (int)0;
		}
		$merge_vars = Mage::helper('mailchimpPro')->getMergeVars($customer,false);
		if(!is_array($merge_vars)) $merge_vars = array();
		$response = '';

		try {
			$response = $client->call('listSubscribe', array($this->_apikey,
															$customer->getListId(),
															$customer->getEmail(),
															$merge_vars,
															$this->_emailType,
															$this->_doubleOptin,
															$this->_updateExisting,
															$this->_replaceInterests,
															$this->_sendWelcome));
		}catch (Exception $e) {
			Mage::helper('mailchimpPro')->addException($e);
        }
		return $response;
	}

	private function unSubscribeCustomer($customer,$client,$subscripter){

		$this->setUnSubsParams(Mage::app()->getStore()->getStoreId());
		$id = ($subscripter->getMemberId())? $subscripter->getMemberId(): $customer->getEmail();
		$response = $client->call('listUnsubscribe', array($this->_apikey,
														$customer->getListId(),
														$id,
														$this->_deleteMember,
														$this->_sendGoodbye,
														$this->_sendNotify));
		$response = ($response)? $response : true ;
		return $response;
	}

	private function updateCustomer($customer, $client, $subscripter){

		$this->setSubsParams(Mage::app()->getStore()->getStoreId());
		$merge_vars = Mage::helper('mailchimpPro')->getMergeVars($customer,true);
		if(!is_array($merge_vars)) $merge_vars = array();

		$id = ($subscripter->getMemberId())? $subscripter->getMemberId(): $customer->getEmail();

		$response = $client->call('listUpdateMember', array($this->_apikey,
															$customer->getListId(),
															$id,
															$merge_vars,
															$this->_emailType,
															$this->_replaceInterests));

		return $response;
	}

	private function listMemberInfo($customer, $client){

		$response = '';
		try {
			$email = ($customer->getEmail())? $customer->getEmail() : $customer->getSubscriberEmail();
			$response = $client->call('listMemberInfo', array($this->_apikey,
															  $customer->getListId(),
															  $email));
		}catch (Exception $e) {
			/********the email not exists or the email is not subscribed************/
			if($e->getCode() != '232' && $e->getCode() != '215'){
				Mage::helper('mailchimpPro')->addException($e);
			}
        }
		return $response;
	}

	private function listInterestGroupings($id, $client){

		$response = '';
		try {
			$response = $client->call('listInterestGroupings', array($this->_apikey,
																 	$id));
		}catch (Exception $e) {
			/********the list don't have interest groups************/
			if($e->getCode() != '211') Mage::helper('mailchimpPro')->addException($e);
        }
		return $response;
	}

	public function getLists(){

		$this->setInitParams(Mage::app()->getStore()->getStoreId());
        if(!$this->_apiHost) return false;
		$client = new Zend_XmlRpc_Client($this->_apiHost);
	    $lists = $client->call('lists', $this->_apikey);

	    return $lists;
	}

	public function mainAction($customer,$registered = null){

		try {
			$this->setInitParams(Mage::app()->getStore()->getStoreId());
            if(!$this->_apiHost) return false;
			$client = new Zend_XmlRpc_Client($this->_apiHost);
			$response = false;

			if($customer->getAction() == self::ACTION_SUBSCRIBE){
				$response = $this->subscribeCustomer($customer,$client);
			}elseif($customer->getAction() == self::ACTION_UPDATE || $customer->getAction() == self::ACTION_UNSUBSCRIBE){
				$response = $this->updateCustomer($customer,$client,$registered);
				if($customer->getAction() == self::ACTION_UNSUBSCRIBE && $response){
					$this->registerSubscription($customer, $client,$registered);
					$response = $this->unSubscribeCustomer($customer,$client,$registered);
				}
			}

			if($response){
				if($this->_doubleOptin == (bool)0) $this->registerSubscription($customer, $client, $registered);
			}else{
				if (Mage::app()->getStore()->getStoreId() == 0) Mage::getSingleton('adminhtml/session')->addError('Mailchimp General Error');
			}

		}catch (Exception $e) {
        	Mage::helper('mailchimpPro')->addException($e);
        }
	}

	public function getGroupsByListId($currentStore,$id,$custom){

		try {
			$this->setInitParams($currentStore);

            if(!$this->_apiHost) return false;
			$client = new Zend_XmlRpc_Client($this->_apiHost);
			$listgroups = $this->listInterestGroupings($id,$client);

			if($listgroups){
				$registered = '';

				if($custom->getId()){
					$customer = Mage::getModel('customer/customer')->load($custom->getId());
					$customer->setCustomerId($customer->getEntityId())
							 ->setSubscriberEmail($customer->getEmail())
							 ->setListId($id);
					$registered = Mage::helper('mailchimpPro')->isMailchimpSubscribed($customer,false,$id)->getData();
				}


				if(!empty($registered)){
					$member = $group = array();
					$member = $this->listMemberInfo($customer,$client);

					if($member){
						foreach($member['merges']['GROUPINGS'] as $val){
							$group = array_merge($group, explode(', ',$val['groups']));
				        }
						if(count($group)){
							foreach($listgroups as $ky=>$item){
								foreach($item['groups'] as $k=>$val){
									if(in_array($val['name'],$group)){
										$listgroups[$ky]['groups'][$k]['checked'] = true;
									}
								}
							}
						}
					}
				}
				return $listgroups;
			}else{
				if ($currentStore == 0) Mage::getSingleton('adminhtml/session')->addError('Mailchimp General Error');
			}
		}catch (Exception $e) {
        	Mage::helper('mailchimpPro')->addException($e);
        }
        return '';
	}

	private function registerSubscription($customer, $client,$registered){

		$memberId = $this->listMemberInfo($customer,$client);
		$id = (is_array($memberId))? $memberId['id']: $customer->getEmail();
		$customer->setMemberId($id);
		if($registered) $customer->setMailchimpproId($registered->getMailchimpproId());
		Mage::getModel('mailchimpPro/subscripter')->registerInfo($customer);

		return true;
	}

	public function getStatus($params, $customer){

		try {

			$this->setInitParams(Mage::app()->getStore()->getStoreId());
			$client = new Zend_XmlRpc_Client($this->_apiHost);
			$status = $this->listMemberInfo($customer, $client);

			if(isset($params['is_subscribed']) && $params['is_subscribed']){
				if($status){
					$action = ($status['status'] == 'subscribed')? self::ACTION_UPDATE : self::ACTION_SUBSCRIBE;
				}else{
					$action = self::ACTION_SUBSCRIBE;
				}
			}else{
				if($status){
					$action = ($status['status'] == 'subscribed')? self::ACTION_UNSUBSCRIBE : 'nothing' ;
				}else{
					$action = 'nothing';
				}
			}
			return $action;
		}catch (Exception $e) {
        	Mage::helper('mailchimpPro')->addException($e);
        }
        return '';
	}
}
