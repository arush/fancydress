<?php

$installer = $this;

$installer->startSetup();

$installer->run("    
    DELETE FROM     `{$this->getTable('core_config_data')}`  
    WHERE `path` = 'rewards/referral/subscription_email_template'  AND value LIKE 'rewards_referral_%'
      OR `path` = 'rewards/referral/confirmation_email_template' AND value LIKE 'rewards_referral_%'
    ;
");


$installer->endSetup();



