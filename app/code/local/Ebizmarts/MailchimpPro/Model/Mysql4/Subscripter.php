<?php

  class Ebizmarts_MailchimpPro_Model_Mysql4_Subscripter extends Mage_Core_Model_Mysql4_Abstract
  {
      public function _construct()
      {
          $this->_init('mailchimpPro/subscripter', 'mailchimppro_id');
      }
  }