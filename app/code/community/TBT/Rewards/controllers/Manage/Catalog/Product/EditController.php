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
 * Manage Currency Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Manage_Catalog_Product_EditController extends Mage_Adminhtml_Controller_Action {
	const EXPORT_FILE_NAME = 'product_applicable_rules';
	
	/**
	 * Export product grid to CSV format
	 */
	public function exportCsvAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H.i.s" ) . '.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_catalog_product_edit_tab_points' );
		$csv = $content->getCsv ();
		
		$this->_sendUploadResponse ( $fileName, $csv );
	}
	
	/**
	 * Export product grid to XML format
	 */
	public function exportXmlAction() {
		$fileName = self::EXPORT_FILE_NAME . '-' . date ( "m.d.y.H:i:s" ) . '.xml';
		$content = $this->getLayout ()->createBlock ( 'rewards/manage_catalog_product_edit_tab_points' );
		$xml = $content->getXml ();
		
		$this->_sendUploadResponse ( $fileName, $xml );
	}
	
	protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
		$response = $this->getResponse ();
		$response->setHeader ( 'HTTP/1.1 200 OK', '' );
		
		$response->setHeader ( 'Pragma', 'public', true );
		$response->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true );
		
		$response->setHeader ( 'Content-Disposition', 'attachment; filename=' . $fileName );
		$response->setHeader ( 'Last-Modified', date ( 'r' ) );
		$response->setHeader ( 'Accept-Ranges', 'bytes' );
		$response->setHeader ( 'Content-Length', strlen ( $content ) );
		$response->setHeader ( 'Content-type', $contentType );
		$response->setBody ( $content );
		$response->sendResponse ();
		die ();
	}
	
	public function dRulesGridAction() {
		$id = $this->getRequest ()->getParam ( 'product_id' );
		//die($id . "|");
		$model = Mage::getModel ( 'catalog/product' );
		if ($id) {
			$model->load ( $id );
		}
		Mage::register ( 'current_product', $model );
		Mage::register ( 'product', $model );
		
		//        $model = Mage::getModel('customer/customer');
		//        if ($id) {
		//            $model->load($id);
		//        }
		//        Mage::register('current_customer', $model);
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_catalog_product_edit_tab_distribution' )->toHtml () );
	}
	
	public function rRulesGridAction() {
		$id = $this->getRequest ()->getParam ( 'product_id' );
		//die($id . "|");
		$model = Mage::getModel ( 'catalog/product' );
		if ($id) {
			$model->load ( $id );
		}
		Mage::register ( 'current_product', $model );
		Mage::register ( 'product', $model );
		
		//        $model = Mage::getModel('customer/customer');
		//        if ($id) {
		//            $model->load($id);
		//        }
		//        Mage::register('current_customer', $model);
		$this->getResponse ()->setBody ( $this->getLayout ()->createBlock ( 'rewards/manage_catalog_product_edit_tab_redemption' )->toHtml () );
	}
	
	public function preDispatch() {
		if (! Mage::helper ( 'rewards/loyalty_checker' )->isValid ()) {
			Mage::throwException ( "Please check your Sweet Tooth registration code your Magento configuration settings, or contact WDCA through contact@wdca.ca for a description of this problem." );
		}
		parent::preDispatch ();
	}

}

?>