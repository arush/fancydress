<?php

$installer = $this;

$installer->startSetup();


$installer->run("
ALTER TABLE `{$this->getTable('rewardsref_referral')}` DROP FOREIGN KEY rewardsref_referral_child_fk1;
");

$installer->run("
ALTER TABLE `{$this->getTable('rewardsref_referral')}` DROP FOREIGN KEY rewardsref_referral_parent_fk;
");

$installer->run("
ALTER TABLE `{$this->getTable('rewardsref_referral')}`
    ADD CONSTRAINT `rewardsref_referral_child_fk1` 
      FOREIGN KEY (`referral_child_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`)
      ON DELETE SET NULL ON UPDATE CASCADE
;
");


$installer->run("
ALTER TABLE `{$this->getTable('rewardsref_referral')}`
    ADD CONSTRAINT `rewardsref_referral_parent_fk` 
      FOREIGN KEY (`referral_parent_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`)
      ON DELETE CASCADE ON UPDATE CASCADE
;
");



$message = Mage::getModel('adminnotification/inbox');
$message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
$message->setDateAdded(date("c", time()));
$message->setTitle("Your Sweet Tooth Referral/Affiliate system was successfully updated to version 2.0.2.1");
$message->setDescription("Your Sweet Tooth Referral/Affiliate system was successfully updated to version 2.0.2.1");
$message->setUrl("http://www.getsweettooth.com/wiki/index.php/Referral_Affiliate");
$message->save();

$installer->endSetup();



