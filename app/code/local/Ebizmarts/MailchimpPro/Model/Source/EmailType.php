<?php

class Ebizmarts_MailchimpPro_Model_Source_EmailType
{
    public function toOptionArray(){
        return array(
            array('value'=>'html', 'label'=>Mage::helper('mailchimpPro')->__('HTML')),
            array('value'=>'text', 'label'=>Mage::helper('mailchimpPro')->__('TEXT')),
            array('value'=>'mobile', 'label'=>Mage::helper('mailchimpPro')->__('MOBILE')),
        );
    }
}