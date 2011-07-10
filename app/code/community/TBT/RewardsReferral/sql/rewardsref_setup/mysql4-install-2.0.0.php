<?php

$installer = $this;

$installer->startSetup();


$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('customer', 'rewardsref_notify_on_referral', array(
    'label' => Mage::helper('rewardsref')->__('Notify on Referral'),
    'type' => 'int',
    'input' => 'select',
    'visible' => true,
    'required' => false,
    'position' => 1,
    'default' => 1,
    'default_value' => 1,
    'source' => "rewardsref/attribute_notify",
));

$message = Mage::getModel('adminnotification/inbox');
$message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
$message->setDateAdded(date("c", time()));
$message->setTitle("Sweet Tooth Referral/Affiliate System 2.0 Was Installed Sucessfully");
$message->setDescription("Congrats! The Sweet Tooth Referral/Affiliate System 2.0 Was Installed Successfully.");
$message->setUrl("http://www.getsweettooth.com/wiki/index.php/Referral_Affiliate");
$message->save();

$installer->endSetup();



