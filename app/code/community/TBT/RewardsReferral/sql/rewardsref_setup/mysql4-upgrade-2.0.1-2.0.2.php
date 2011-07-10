<?php

$installer = $this;

$installer->startSetup();

$message = Mage::getModel('adminnotification/inbox');
$message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
$message->setDateAdded(date("c", time()));
$message->setTitle("Sweet Tooth Referral/Affiliate System 2.0.2 Was Installed Sucessfully");
$message->setDescription("The Sweet Tooth Referral/Affiliate System 2.0.2 Was Installed Successfully.");
$message->setUrl("http://www.getsweettooth.com/wiki/index.php/Referral_Affiliate");
$message->save();

$installer->endSetup();



