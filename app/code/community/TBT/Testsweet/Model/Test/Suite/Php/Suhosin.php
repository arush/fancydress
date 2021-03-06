<?php

class TBT_Testsweet_Model_Test_Suite_Php_Suhosin extends TBT_Testsweet_Model_Test_Suite_Abstract {

    public function getRequireTestsweetVersion() {
        return '1.0.0.0';
    }

    public function getSubject() {
        return $this->__('Check PHP for Suhosin');
    }

    public function getDescription() {
        return $this->__('Check if PHP has Suhosin running. Suhosin can prevent some needed functions from running');
    }

    protected function generateSummary() {
      
        
$suhosin_help = <<<FEED
<pre>
http://www.magentocommerce.com/wiki/groups/227/magento-compatible_suhosin_configuration    

suggested in [php.ini]
    suhosin.mail.protect = 0
    suhosin.memory_limit = 256M
    suhosin.post.max_vars = 5000
    suhosin.post.max_value_length = 500000
    suhosin.request.max_vars = 5000
    suhosin.request.max_value_length = 500000
    suhosin.session.cryptua = Off

suggested in [.htaccess]
    php_value suhosin.mail.protect 0
    php_value suhosin.memory_limit 256M
    php_value suhosin.post.max_vars 5000
    php_value suhosin.post.max_value_length 500000
    php_value suhosin.request.max_vars 5000
    php_value suhosin.request.max_value_length 500000
<pre/>
FEED;

        // Check if Suhosin is compiled into php and not a extension
        // magento says to use these settings if using Suhosin...
        //http://www.magentocommerce.com/wiki/groups/227/magento-compatible_suhosin_configuration
        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();
        
        $has_suhosin = false;
        if (!extension_loaded('suhosin')) {
            $this->addPass($this->__("PHP does not have problematic extension Suhosin"));
        } else {
            $this->addWarning($this->__("PHP has problematic extension Suhosin"), $this->__("Magento suggested Suhosin configuration ontop of the default values: %s", $suhosin_help));
        }

        if (strpos($phpinfo, "Suhosin Patch") === false) {
            $this->addPass($this->__("Suhosin is not compiled directly into the PHP binarie"));
        } else {
            $this->addWarning($this->__("Suhosin is compiled directly into the PHP binarie"), $this->__("Magento suggested Suhosin configuration ontop of the default values: %s", $suhosin_help));
        }
      
    }

}
