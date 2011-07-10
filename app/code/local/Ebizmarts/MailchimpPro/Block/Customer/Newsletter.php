<?php

class Ebizmarts_MailchimpPro_Block_Customer_Newsletter extends Mage_Customer_Block_Newsletter {

    protected $_lists;
    protected $_groups;
    protected $_additionalSubscribedlists;
    protected $_additionalLists;

	protected function _prepareLayout(){

  		parent::_prepareLayout();
	  	if(!$this->getRequest()->isXmlHttpRequest()){
	  		if(Mage::helper('mailchimpPro')->addAddons(Mage::app()->getStore()->getStoreId())) $this->getLayout()->getBlock('head')->addItem('skin_js', 'mailchimppro/js/MailChimpProAdds.js');
	  	}
	}

    public function isMailChimpProEnabled($store){

		$store = ($store)? $store : Mage::app()->getStore()->getStoreId() ;
		return Mage::helper('mailchimpPro')->mailChimpProEnabled($store);
    }

	public function canShowBigBox(){

		$store = Mage::app()->getStore()->getStoreId();

		if(Mage::helper('mailchimpPro')->mailChimpProEnabled($store)
			&& Mage::helper('mailchimpPro')->getGeneralConfig('listid',$store)){
				return true;
		}
        return false;
    }

    public function getLists(){

		$store = Mage::app()->getStore()->getStoreId();
        $this->_loadLists($store);
        return $this->_lists;
    }

	protected function _loadLists($store){

        if (empty($this->_lists) && Mage::helper('mailchimpPro')->mailChimpProEnabled($store)) {

            $this->_lists = array();

			$lists = Mage::helper('mailchimpPro')->getAvailablelists($store);

        } else {
            return;
        }

        if( isset($lists) && count($lists) == 0 ) return;

		foreach($lists as $k=>$v){
			if($v != null){
				$this->_lists[$k] = $v;
				unset($lists[$k]);
			}
		}

        ksort($this->_lists);
    }

    public function getSubscribedAddiotionalLists(){

		$this->getLists();
        $this->_loadAdditionalSubscribedLists();
        return $this->_additionalSubscribedlists;
    }

    public function _loadAdditionalSubscribedLists(){

        if (empty($this->_additionalSubscribedlists)) {

            $this->_additionalSubscribedlists = array();

			$lists = $this->_lists;

        } else {
            return;
        }

        if( isset($lists) && count($lists) == 0 ) return;


		$col = Mage::getModel('mailchimpPro/subscripter')->getCollection()
			->addFieldToFilter('store_id',Mage::app()->getStore()->getStoreId())
			->addFieldToFilter('customer_id',Mage::getSingleton('customer/session')->getCustomerId());

        foreach ($col as $item) {
        	if(array_key_exists($item->getListId(),$lists)){
        		$item->setName($lists[$item->getListId()]);
        		$this->_additionalSubscribedlists[$item->getListId()] = $item;
        		unset($lists[$item->getListId()]);
        	}
        }
        ksort($this->_additionalSubscribedlists);
    }

	public function getAdditionalLists($store){

		$store = ($store)? $store : Mage::app()->getStore()->getStoreId() ;
		if(Mage::helper('mailchimpPro')->getAdditionalEnabled($store)){
			$this->getLists();
	        $this->getSubscribedAddiotionalLists();
	        $this->_loadAdditionalLists();
		}
        return $this->_additionalLists;
    }

    public function _loadAdditionalLists(){

		if (empty($this->_additionalLists)) {
            $this->_additionalLists = array();

			$listsAll = $this->_lists;
			$listsSubscribed = $this->_additionalSubscribedlists;

        } else {
            return;
        }

        if( isset($listsAll) && count($listsAll) == 0 ) return;

        foreach ($listsSubscribed as $subscribed) {
        	if(array_key_exists($subscribed->getListId(),$listsAll)){
        		$this->_additionalLists[$subscribed->getListId()] = $subscribed;
        		unset($listsAll[$subscribed->getListId()]);
        	}
        }
        if(count($listsAll)){
        	foreach($listsAll as $k=>$v){
        		$item = new Varien_Object;
        		$item->setName($v)
        			 ->setListId($k)
    				 ->setIsSubscribed(false);
				$this->_additionalLists[$k] = $item;
        	}
        }

        ksort($this->_additionalLists);
    }

	public function getGroups($id){

		$store = Mage::app()->getStore()->getStoreId();
        $this->_loadGroups($store,$id);
        return $this->_groups;
	}

	public function getGroupInputs($listId = null, $store, $isAdditional){

		$listId = ($listId)? $listId : Mage::helper('mailchimpPro')->getGeneralConfig('general',$store);

		$input = '';
		$allValues = '';
		$jsId = ($isAdditional)? $listId : 'is_subscribed';

    	foreach($this->_groups[$listId] as $groupId=>$group){
   			$options = '';
   			$baseValue = '['.$group['name'].'|'.$group['id'].']';
   			$allValues .= $baseValue;

    		foreach($group['groups'] as $item){
    			$type = '';
				$value	= $baseValue.'['.$item['name'].']';
	    		$checked = (isset($item['checked']) && $item['checked'])? 'checked="checked" selected="selected"' : '';

	    		if($group['form_field'] == 'checkboxes'){
					$type = 'checkbox';
					$options .=  '<input type="'.$type.'" '.$checked.' value="' .$value.'" name="group['.$listId.']'.$value.'" title="'.$item['name'].'" class="'.$type.'" onclick="checkParent(\''.$jsId.'\')"/>
		        			<label for="subscription">'.$item['name'].'</label>';
				}elseif($group['form_field'] == 'dropdown'){
					$type = 'select';
					$options .=  '<option value="'.$value.'" '.$checked.'>'.$item['name'].'</option>';
		    	}elseif($group['form_field'] == 'radio'){
					$type = 'radio';
					$options .=  '<input type="'.$type.'" '.$checked.' value="' .$value.'" name="group['.$listId.']'.$baseValue.'[]" title="'.$item['name'].'" class="'.$type.'" onclick="checkParent(\''.$jsId.'\')"/>
	    				<label for="subscription">'.$item['name'].'</label>';
		    	}
	    	}

			if($type == 'select'){
				$opening = '<select class="'.$type.'" name="group['.$listId.']'.$baseValue.'[]" onchange="checkParent(\''.$jsId.'\')" >';
				$blank = '<option value="'.$baseValue.'[]">--Please Select--</option>';
				$options = $opening.$blank.$options.'</select>';
			}
			$header = ($type)? '<div class="title">'.$group['name'].'</div>': '';
			$input .= $header.$options;
    	}

		$allGrps = '<input type="hidden" name="allgroups['.$listId.']" id="allgroups['.$listId.']" value="'.$allValues.'" />';
    	return $input.$allGrps;
    }

	public function getNewsletterGroups($store){

		$store = ($store)? $store : Mage::app()->getStore()->getStoreId();
		$helper = Mage::helper('mailchimpPro');

		if((bool)$helper->getGeneralConfig('intgr',$store)){
			$id = $helper->getGeneralConfig('general',$store);
	        $this->_loadGroups($store,$id);
	        return $this->_groups;
		}
		return false;
	}

	public function isForcedToRegisterSubscribe(){

		$store = Mage::app()->getStore()->getStoreId();
		$helper = Mage::helper('mailchimpPro');

		if((bool)$helper->getSubscribeConfig('forece_register',$store)){
			return true;
		}
        return false;
    }

	protected function _loadGroups($store,$id){

        if (empty($this->_groups[$id]) && Mage::helper('mailchimpPro')->getGeneralConfig('intgr',$store)) {
            $this->_groups = array();
			$groups = Mage::getModel('mailchimpPro/mailchimpPro')->getGroupsByListId($store,$id,Mage::getSingleton('customer/session'));
        } else {
            return;
        }
        if( isset($groups) && !is_array($groups) /*&& count($groups) == 0*/ ) return;

        foreach ($groups as $item) {
    		$this->_groups[$id][$item['id']] = $item;
        }
        ksort($this->_groups[$id]);
    }

	public function getFormMultiActionUrl(){

        return $this->getUrl('mailchimpPro/manage/multiSave', array('_secure' => true));
    }

    public function getMainFormInput(){

		$store = Mage::app()->getStore()->getStoreId();
		$val = ($this->getIsSubscribed())? 1 : 0 ;
    	if((bool)Mage::helper('mailchimpPro')->getGeneralConfig('intgr',$store)){
    		$subA = '<input type="hidden" name="is_general" id="is_general" value="1" />';
			$id = Mage::helper('mailchimpPro')->getGeneralConfig('general',$store);
    		$a = $subA.'<input name="list['.$id.']" id="list['.$id.']" value="'.$val.'" ';
    	}else{
    		$a = '<input name="is_subscribed" id="subscription" value="1" ';
    	}

		if($val) $a .= ' checked="checked" ';
		$a .= 'class="checkbox" title="'.$this->__('General Subscription') .'" type="checkbox"/>';
		return $a;
    }

}
?>
