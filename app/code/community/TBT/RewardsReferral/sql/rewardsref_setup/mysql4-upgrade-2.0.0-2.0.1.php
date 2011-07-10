<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('rewardsref_referral')}` (
  `rewardsref_referral_id` int(11) unsigned NOT NULL auto_increment,
  `referral_parent_id` int(11) unsigned NOT NULL,
  `referral_child_id` int(11) unsigned default NULL,
  `referral_email` varchar(255) NOT NULL default '',
  `referral_name` varchar(255) default NULL,
  `referral_status` tinyint(1) default '0',
  `created_ts` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`rewardsref_referral_id`),
  UNIQUE KEY `email` (`referral_email`),
  UNIQUE KEY `son_id` (`referral_child_id`),
  KEY `FK_customer_entity` (`referral_parent_id`),
  CONSTRAINT `rewardsref_referral_child_fk1` 
    FOREIGN KEY (`referral_child_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`),
  CONSTRAINT `rewardsref_referral_parent_fk` 
    FOREIGN KEY (`referral_parent_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$message = Mage::getModel('adminnotification/inbox');
$message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
$message->setDateAdded(date("c", time()));
$message->setTitle("Sweet Tooth Referral/Affiliate was updated to 2.0.1");
$message->setDescription("Sweet Tooth Referral/Affiliate was updated to 2.0.1.  No extra action is necessary from yourself.");
$message->setUrl("http://www.getsweettooth.com/wiki/index.php/Referral_Affiliate");
$message->save();

$installer->endSetup();


