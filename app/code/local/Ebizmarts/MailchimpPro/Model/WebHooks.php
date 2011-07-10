<?php

class Ebizmarts_MailchimpPro_Model_WebHooks extends Mage_Core_Model_Abstract{

	protected $_webhookurl;
	protected $_actions = array();
	protected $_sources = array();

	private function setWebHookParams($list = ''){

		$this->_webhookurl = str_replace('index.php/','',Mage::getBaseUrl()).'WebHooksCapture.php';
		if(is_array($list)){
			$this->_actions['subscribe'] = (bool)$list['subscribe'];
			$this->_actions['unsubscribe'] = (bool)$list['unsubscribe'];
			$this->_actions['profile'] = (bool)$list['profile'];
			$this->_actions['cleaned'] = (bool)$list['cleaned'];
			$this->_actions['upemail'] = (bool)$list['upemail'];
			$this->_sources['user'] = (bool)$list['user'];
			$this->_sources['admin'] = (bool)$list['admin'];
			$this->_sources['api'] = (bool)$list['api'];
		}
		return $this;
	}

	private function getListWebHooks($client,$list,$apiKey){

		try {
			$response = $client->call('listWebhooks', array($apiKey,
														$list));
			return $response;
		}catch (Exception $e) {
			Mage::helper('mailchimpPro')->addException($e);
			return false;
        }
	}

	private function listWebhookAdd($client,$list,$apiKey){

		try {
			$response = $client->call('listWebhookAdd', array($apiKey,
															$list['list_id'],
															$this->_webhookurl,
															$this->_actions,
															$this->_sources));
			return $response;
		}catch (Exception $e) {
			Mage::helper('mailchimpPro')->addException($e);
			return false;
        }
	}

	private function listWebhookDel($client,$list,$apiKey){
		try {
			$response = $client->call('listWebhookDel', array($apiKey,
															$list['list_id'],
															$this->_webhookurl));
			return $response;
		}catch (Exception $e) {
			Mage::helper('mailchimpPro')->addException($e);
			return false;
        }
	}


	public function mainAction($listId){

		try {
			$mailchimpProModel = Mage::getModel('mailchimpPro/mailchimpPro');
			$mailchimpProModel->setInitParams(Mage::app()->getStore()->getStoreId());

            if(!$mailchimpProModel->_apiHost) return false;
			$client = new Zend_XmlRpc_Client($mailchimpProModel->_apiHost);

			if(is_array($listId)){
				$list = array();

				foreach($listId as $val){
					$list[substr($val,0,strpos($val,'='))] = substr($val,strpos($val,'=')+1,strlen($val));
				}

				$this->setWebHookParams($list);
				$response = $this->getListWebHooks($client,$list['list_id'],$mailchimpProModel->_apikey);
				foreach($response as $hook){
					if($hook['url'] == $this->_webhookurl){
						$response = $this->listWebhookDel($client,$list,$mailchimpProModel->_apikey);
					}
				}
				if($list['subscribe'] || $list['unsubscribe'] || $list['profile'] || $list['cleaned'] ||
				   $list['upemail'] || $list['user'] || $list['admin'] || $list['api']){
					$response = $this->listWebhookAdd($client,$list,$mailchimpProModel->_apikey);
				}
			}else{
				$this->setWebHookParams();
				$response = $this->getListWebHooks($client,$listId,$mailchimpProModel->_apikey);
			}

			if($response && is_array($response)){
				foreach($response as $hook){
					if($hook['url'] == $this->_webhookurl){
						return $hook;
					}
				}
				return false;
			}else{
				return false;
			}
		}catch (Exception $e) {
        	Mage::helper('mailchimpPro')->addException($e);
        }
	}

}
