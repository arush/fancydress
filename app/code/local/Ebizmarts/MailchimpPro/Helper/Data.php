<?php
class Ebizmarts_MailchimpPro_Helper_Data extends Mage_Core_Helper_Abstract{

	public function getGeneralConfig($field,$store=null){

		return Mage::getStoreConfig('mailchimpPro/general/'.$field,$store);
	}

	public function getSubscribeConfig($field,$store=null){

		return Mage::getStoreConfig('mailchimpPro/subscribe/'.$field,$store);
	}

	public function getUnSubscribeConfig($field,$store=null){

		return Mage::getStoreConfig('mailchimpPro/unsubscribe/'.$field,$store);
	}

	private function F91B2E37D34E5DC4FFC59C324BDC1157C($store){

		$R8409EAA6EC0CE2EA307354B2E150F8C2 = str_replace('www.', '', $_SERVER['HTTP_HOST']); $REBBCEB7D5CE9F8309DCC3226F5DAC53B = $this->getGeneralConfig('sid',$store); $R1A634B62E7FB6CBC3AD8309D17FDC73C = substr(strrev($R8409EAA6EC0CE2EA307354B2E150F8C2), 0, strlen($R8409EAA6EC0CE2EA307354B2E150F8C2)); $R7BCAA4FB61D5AD641E1B67637D894EC1 = crypt($R8409EAA6EC0CE2EA307354B2E150F8C2 . 'Ebizmarts_MailChimp', $R1A634B62E7FB6CBC3AD8309D17FDC73C); $R835CC35CB400C713B188267E7C10C798 = ($R7BCAA4FB61D5AD641E1B67637D894EC1 === $REBBCEB7D5CE9F8309DCC3226F5DAC53B); return $R835CC35CB400C713B188267E7C10C798;
	}

	public function addAddons($store){

		if($this->mailChimpProEnabled($store) && !(bool)$this->F91B2E37D34E5DC4FFC59C324BDC1157C($store)){
			return true;
		}
		return false;
	}

	public function newMailChimpHost($apikey,$store){

		if(substr($apikey, -4) != '-us1' && substr($apikey, -4) != '-us2'){
        	Mage::getSingleton('adminhtml/session')->addError('The API key is not well formed');
			return false;
        }else{
			list($key, $dc) = explode('-',$apikey,2);
			if (!$dc) $dc = 'us1';
			list($aux, $host) = explode('http://',$this->getGeneralConfig('url',$store));
			$api_host = 'http://'.$dc.'.'.$host;
			return $api_host;
        }
	}

	public function mailChimpProEnabled($store){

		if((bool)$this->getGeneralConfig('active',$store) == true && $this->getGeneralConfig('apikey',$store)
			&& $this->getGeneralConfig('general',$store) && $this->getGeneralConfig('url',$store)){
			return true;
		}
		return false;
	}

	public function getAdditionalEnabled($store){

		return (bool)$this->getGeneralConfig('intgr',$store);
	}

	public function checkSubscribe(){

		$store = Mage::app()->getStore()->getStoreId();

		if($this->mailChimpProEnabled($store)){
			$return = ((bool)$this->getSubscribeConfig('double_optin',$store))? false : true;
		}else{
			$return = true;
		}

		return $return;
	}

	public function checkSendSubscribe(){

		$store = Mage::app()->getStore()->getStoreId();

		if($this->mailChimpProEnabled($store)){
			$return = ((bool)$this->getSubscribeConfig('double_optin',$store) || (bool)$this->getSubscribeConfig('send_welcome',$store))? false : true;
		}else{
			$return = true;
		}

		return $return;
	}

	public function checkSendUnsubscribe(){

		$store = Mage::app()->getStore()->getStoreId();

		if($this->mailChimpProEnabled($store)){
			$return = ((bool)$this->getUnSubscribeConfig('send_notify',$store) || (bool)$this->getUnSubscribeConfig('send_goodbye',$store))? false : true;
		}else{
			$return = true;
		}

		return $return;
	}

	public function isSubscribed($email){

		return Mage::getModel('newsletter/subscriber')
					->loadByEmail($email)
					->isSubscribed();
	}

	public function isMailchimpSubscribed($subscriber,$guest = false,$list){

		$col = Mage::getModel('mailchimpPro/subscripter')->getCollection()
				->addFieldToFilter('store_id',Mage::app()->getStore()->getStoreId())
				->addFieldToFilter('customer_id',$subscriber->getCustomerId())
				->addFieldToFilter('list_id',$list);
		if($guest){
			$col->addFieldToFilter('current_email',$subscriber->getSubscriberEmail());
		}else{
			if(!count($col)){
				$subscripter = Mage::getModel('mailchimpPro/subscripter')->getCollection()
						->addFieldToFilter('store_id',Mage::app()->getStore()->getStoreId())
						->addFieldToFilter('current_email',$subscriber->getSubscriberEmail())
						->addFieldToFilter('list_id',$list)
						->getFirstItem();

				return $subscripter;
			}
		}
		return $col->getFirstItem();
	}

	public function getAvailableLists($currentStore){

		if($this->mailChimpProEnabled($currentStore)){
			$avlists = explode(',',$this->getGeneralConfig('listid',$currentStore));
			$lists = array();
			foreach($avlists as $list){
				$id = substr($list,0,strpos($list,'['));
				if($id != $this->getGeneralConfig('general',$currentStore)){
					$lists[$id] = substr($list,strpos($list,'[')+1,-1);
				}

			}
			return $lists;
		}
		return false;
	}

	public function getMergeVars($customer,$flag){

		$merge_vars = array();
		$maps = explode('<',$this->getSubscribeConfig('mapping_fields',Mage::app()->getStore()->getStoreId()));
		foreach($maps as $map){
			if($map){
				$aux = substr(strstr($map,"customer='"),10);
				$customAtt = (string)substr($aux,0,strpos($aux,"'"));
				$aux = substr(strstr($map,"mailchimp='"),11);
				$chimpTag = (string)substr($aux,0,strpos($aux,"'"));
				if($chimpTag && $customAtt){
					if($customAtt == 'address'){
						$address = $customer->getAddress();
						$merge_vars[strtoupper($chimpTag)] = array('addr1'=>$address['street'],
																   'addr2'=>'',
																   'city'=>$address['city'],
																   'state'=>$address['region'],
																   'zip'=>$address['postcode'],
																   'country'=>$address['country_id']);
					}else{
						if($value = (string)$customer->getData(strtolower($customAtt))) $merge_vars[strtoupper($chimpTag)] = $value;
					}
				}
			}
		}

		if($flag) $merge_vars['EMAIL'] = $customer->getEmail();
		$groups = $customer->getListGroups();
		$interests = $grpToSend = array();
		if(is_array($groups) && count($groups)){
			$groupings = array();
			$grpName = '';
			$count = 0;
			foreach($groups as $option){
				$parts = explode(']',$option);
				if(substr($parts[0],1) == $customer->getListId() && count($parts) == 5){
					if($count == 0 || $grpName != substr($parts[1],1)){
						$grpName = substr($parts[1],1);
						$currentInterests = $currentInterest = array();
						if($count) $grpToSend[] = end($groupings);
					}
					$count++;
					if(substr($parts[3],1) != '') $interests[] = substr($parts[3],1);
					$currentInterest[] = substr($parts[3],1);
					$currentInterests = implode(", ", $currentInterest);
					$groupings[] = array('id'=>substr($parts[2],1),
									   'name'=>$grpName,
									   'groups'=>$currentInterests);
				    if($count == count($groups)) $grpToSend[] = end($groupings);
				}
			}
		}
		$merge_vars['GROUPINGS'] = $grpToSend;
		$merge_vars['INTERESTS'] = ($interests)? implode(", ", $interests) : '';
		return $merge_vars;
	}

	private function getAllListGroups($params,$currentStore){

		$allGrps = array();

		if($this->getGeneralConfig('intgr',$currentStore)){
			$params = (is_array($params))? $params : $allGrps;

			if(isset($params['group']) || isset($params['allgroups'])){
				$flag = false;
				if(isset($params['group'])){
					foreach($params['group'] as $list=>$group){
						if($group){
							$flag = true;
							$arrayCheck = array();
						}
					}
				}
				$groups = ($flag)? $params['group'] : $params['allgroups'];

				foreach($groups as $list=>$group){
					if(is_array($group)){
						foreach($group as $checkbox){
							if(is_array($checkbox)){
								foreach($checkbox as $item){
									$allGrps[] = '['.$list.']'.str_replace('|','][',$item);
									$arrayCheck[] = $item;
								}
							}else{
								if($checkbox){
									if(strstr($checkbox,']')){
										$allGrps[] = '['.$list.']'.$checkbox;
									}else{
										$allGrps[] = '['.$list.']'.str_replace('|','][',$checkbox).'][]';
									}
								}
							}
						}
					}else{
						if($group && strstr($group,'][')){
							$allGrps[] = '['.$list.']'.str_replace('|','][',$group);
						}else{
							$allGrps[] = '['.$list.']'.str_replace('|','][',$params['allgroups'][$list]).'[]';
						}
					}
				}
				if($flag && isset($params['allgroups'])){
					$missingGrps = explode("]",str_replace("[","",$params['allgroups'][$list]));
					foreach($missingGrps as $fixing){
						if($fixing && !in_array('['.$fixing.'][]',$arrayCheck)){
							$allGrps[] = '['.$list.']['.str_replace('|','][',$fixing).'][]';
						}
					}
				}
			}
		}
		return $allGrps;
	}

	public function mailChimpProFilter($subscriber,$params){

		$store = Mage::app()->getStore()->getStoreId();

		if($this->mailChimpProEnabled($store)){

			if($store == '0') $subscriber->setStoreId(Mage::app()->getDefaultStoreView()->getStoreId());
			$allGrps = $this->getAllListGroups($params,$store);
			$subscriber->setListGroups($allGrps);
			$cusId = ($subscriber->getCustomerId())? $subscriber->getCustomerId() : $subscriber->getEntityId();

			if($cusId){
				$customer = Mage::getSingleton('customer/customer')->load($cusId)->setSoreId($store);

				foreach($customer->getData() as $k=>$v) $subscriber->setData($k,$v);

				if($address = $customer->getDefaultBillingAddress()){
					$addressArray = array();
					foreach($address->getData() as $k=>$v){
						$addressArray[$k] = $v;
					}
					$subscriber->setAddress($addressArray);
				}

				if(!isset($params['is_general'])) $params['is_subscribed'] = true;
			}else{
				$billing = Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();

				if((string)$billing->getEmail() == (string)$subscriber->getEmail()){
					$address = array();
					foreach($billing->getData() as $k=>$v){
						$subscriber->setData($k,$v);
						$address[$k] = $v;
					}
					$subscriber->setAddress($address);
				}else{
					$subscriber->setFirstname('GUEST')
							   ->setLastname('GUEST');
				}
			    $params['is_subscribed'] = true;
			}

			$list = (isset($params['list']))? $params['list'] : array();

			if(!count($list)) $list[$this->getGeneralConfig('general',$store)] = 1;

			foreach($list as $k=>$v) $this->doTheAction($subscriber,$params,$k);

			if(!isset($params['is_general'])){
				$allLists = $this->getAvailableLists($store);
				foreach($allLists as $k=>$v){
					if($k && !array_key_exists($k,$list)){
						$params['is_subscribed'] = false;
						$this->doTheAction($subscriber,$params,$k);
					}
				}
			}
		}
		return true;
	}

	private function doTheAction($customer,$params,$list){

		if($customer->getCustomerId() || $customer->getEntityId()){
			$subscription = $this->isMailchimpSubscribed($customer,false,$list);
		}else{
			$subscription = $this->isMailchimpSubscribed($customer,true,$list);
		}
		$customer->setListId($list);

		$mainModel = Mage::getModel('mailchimpPro/mailchimpPro');
		$action = $mainModel->getStatus($params, $customer);
		$customer->setAction($action);
		$mainModel->mainAction($customer,$subscription);

		return true;
	}

	public function webHookFilter($data){

		$currentStore = Mage::app()->getDefaultStoreView()->getStoreId();
		if($this->mailChimpProEnabled($currentStore)){
			$lists = $this->getAvailablelists($currentStore);
			$lists = (is_array($lists))? $lists : array();
			$generalList = $this->getGeneralConfig('general',$currentStore);
			$lists[$generalList] = $generalList;

			if(array_key_exists($data['data']['list_id'],$lists)){

				$customer = Mage::getModel('mailchimpPro/subscripter')->prepareCustomer($data);

				if($customer->getMailchimpproId() || $customer->getAction() == 'subscribe'){
					Mage::getModel('mailchimpPro/subscripter')->registerInfo($customer);
				}

				$email = (isset($data['data']['email']))? $data['data']['email'] : $data['data']['old_email'] ;

				if($customer->getAction() == 'subscribe' && !$this->isSubscribed($email) && $data['data']['list_id'] == $generalList){
					Mage::getModel('newsletter/subscriber')->subscribe($email,true);
				}elseif(($customer->getAction() == 'unsubscribe' || $customer->getAction() == 'cleaned' ) && $data['data']['list_id'] == $generalList && $this->isSubscribed($email)){
					Mage::getModel('newsletter/subscriber')->loadByEmail($email)->unsubscribe();
				}
			}
		}
	}

 	public function addException(Exception $e){

		$currentStore = Mage::app()->getStore()->getStoreId();

		foreach(explode("\n", $e->getMessage()) as $message) {
    		if ($currentStore == 0){
            	Mage::getSingleton('adminhtml/session')->addError($this->__('Mailchimp General Error: ').$message);
    		}else{
    			Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
    		}
    		$this->logInfo($e, $this->__('Mailchimp General Error: ').$message);
        }
	}

	private function logInfo(Exception $exception, $alternativeText){

        $message = sprintf('Exception code: '.$exception->getCode().' \n Exception message: '.$exception->getMessage().' \n Trace: '.$exception->getTraceAsString());
        Mage::log($message, Zend_Log::DEBUG, 'mailChimpPro_Exceptions.log');

        return $this;
    }

}
?>
