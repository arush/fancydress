<?php

class Ebizmarts_MailchimpPro_Model_Source_General extends Ebizmarts_MailchimpPro_Model_Source {

    public function toOptionArray($isMultiselect=false){

    	$options = '';
        if (!$this->_lists) {
			try {

				$lists = Mage::getModel('mailchimpPro/mailchimpPro')->getLists();

			    if($lists){
					$opts = array();
				    foreach ($lists as $list){
						$this->_lists[] = array('value'=>$list['id'],
												'label'=>$list['name']);
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