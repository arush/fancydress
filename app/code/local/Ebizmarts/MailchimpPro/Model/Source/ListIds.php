<?php

class Ebizmarts_MailchimpPro_Model_Source_ListIds extends Ebizmarts_MailchimpPro_Model_Source {

    public function toOptionArray($isMultiselect=false,$isGrid=false){

    	$options = '';
        if (!$this->_lists) {
			try {

			    $lists = Mage::getModel('mailchimpPro/mailchimpPro')->getLists();

			    if($lists){
					$opts = array();
				    foreach ($lists as $list){
				    	if($isGrid){
		    				$opts[$list['id']] = $list['name'];
				    	}else{
							$this->_lists[] = array('value'=>$list['id']."[".$list['name']."]",
											'label'=>$list['name']);
				    	}
					}
					if($isGrid){
						return $opts;
					}
				    $options = $this->_lists;
			        if(!$isMultiselect){
			            array_unshift($options, array('value'=>'',
													  'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
			        }
			    }
			}catch (Exception $e) {
	         	Mage::helper('mailchimpPro')->addException($e);
	        }
        }
		return $options;
    }
}