<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Model_Review_Wrapper extends Varien_Object {
	
	/**
	 * @var Mage_Review_Model_Review
	 */
	protected $_review;
	
	/**
	 * @param Mage_Review_Model_Review $subscriber
	 */
	public function wrap(Mage_Review_Model_Review $review) {
		$this->_review = $review;
		return $this;
	}
	
	/**
	 * Return the wrapped review
	 * @return Mage_Review_Model_Review
	 */
	public function getReview() {
		return $this->_review;
	}
	
	/**
	 * Returns true if it's Pending!
	 * @return boolean
	 */
	public function isPending() {
		return $this->getReview ()->getStatusId () == Mage_Review_Model_Review::STATUS_PENDING;
	}
	
	/**
	 * Returns true if it's Approved!
	 * @return boolean
	 */
	public function isApproved() {
		return $this->getReview ()->getStatusId () == Mage_Review_Model_Review::STATUS_APPROVED;
	}
	
	/**
	 * Returns true if it's not Approved!
	 * @return boolean
	 */
	public function isNotApproved() {
		return $this->getReview ()->getStatusId () == Mage_Review_Model_Review::STATUS_NOT_APPROVED;
	}
	
	/**
	 * Approves all associated transfers with a pending status.
	 */
	public function approvePendingTransfers() {
		foreach ( $this->getAssociatedTransfers () as $transfer ) {
			if ($transfer->getStatus () == TBT_Rewards_Model_Transfer_Status::STATUS_PENDING) {
				//Move the transfer status from pending to approved, and save it!
				$transfer->setStatus ( TBT_Rewards_Model_Transfer_Status::STATUS_PENDING, TBT_Rewards_Model_Transfer_Status::STATUS_APPROVED );
				$transfer->save ();
			}
		}
	}
	
	/**
	 * Loops through each Special rule. If it applies, create a new pending transfer.
	 */
	public function ifNewReview() {
		$ruleCollection = Mage::getSingleton ( 'rewards/review_validator' )->getApplicableRulesOnReview ();
		foreach ( $ruleCollection as $rule ) {
			$is_transfer_successful = $this->createPendingTransfer ( $rule );
			
			if ($is_transfer_successful) {
				//Alert the customer on the distributed points  
				Mage::getSingleton ( 'core/session' )->addSuccess ( Mage::helper ( 'rewards' )->__ ( 'You will receive %s upon approval of this review', Mage::getModel ( 'rewards/points' )->set ( $rule ) ) );
			}
		}
	}
	
	/**
	 * Returns a collection of all transfers associated with this review
	 *
	 * @return array(TBT_Rewards_Model_Transfer) : A collection of all reviews associated with this review
	 */
	public function getAssociatedTransfers() {
		return Mage::getModel ( 'rewards/review_transfer' )->getTransfersAssociatedWithReview ( $this->getReview ()->getId () );
	}
	
	/**
	 * Creates a new transfer with a pending status using the rule information
	 *
	 * @param TBT_Rewards_Model_Special $rule
	 */
	public function createPendingTransfer($rule) {
		try {
			$is_transfer_successful = Mage::getModel ( 'rewards/review_transfer' )->transferReviewPoints ( $this->getReview (), $rule );
		} catch ( Exception $ex ) {
			die ( $ex->getMessage () );
			Mage::getSingleton ( 'core/session' )->addError ( $ex->getMessage () );
		}
		return $is_transfer_successful;
	}

}

?>