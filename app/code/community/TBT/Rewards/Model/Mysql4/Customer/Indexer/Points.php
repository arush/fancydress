<?php

class TBT_Rewards_Model_Mysql4_Customer_Indexer_Points extends Mage_Index_Model_Mysql4_Abstract {
	
	/**
	 * Class constructor
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 */
	public function _construct() {
		$this->_init ( 'rewards/customer_indexer_points', 'customer_id' );
        if( Mage::helper('rewards/version')->isBaseMageVersionAtLeast('1.4.1') ) {
		    $this->useIdxTable ( true );
        }
        
        return $this;
	}
	
	/**
	 * Reindex all data
	 * 
	 * @name reindexAll
	 * @see Mage_Index_Model_Mysql4_Abstract::reindexAll()
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	public function reindexAll() {
		$this->_createTable ();
        
        $customerTable = Mage::getSingleton('core/resource')->getTableName('customer_entity');
        
        // Set all balances to 0
        $sql = "INSERT INTO {$this->getIdxTable()} (customer_id, customer_points_usable)
                SELECT customer_table.entity_id, '0' FROM {$customerTable} as `customer_table`";
        $results = $this->_getWriteAdapter()->query($sql);
        
        // Update balances
        $sql = "SELECT `points_table`.* FROM (
            SELECT SUM(main_table.quantity) AS `customer_points_usable`, `main_table`.`customer_id` FROM `{$this->getTable ( 'rewards/transfer' )}` AS `main_table`
             LEFT JOIN `{$this->getTable ( 'rewards/transfer_reference' )}` AS `reference_table` ON main_table.rewards_transfer_id = reference_table.rewards_transfer_id
             LEFT JOIN `{$this->getTable ( 'rewards/currency' )}` AS `currency_table` ON currency_table.rewards_currency_id=main_table.currency_id WHERE (main_table.status IN (5)) GROUP BY `main_table`.`customer_id`,
                    `main_table`.`currency_id`
            UNION
            SELECT SUM(main_table.quantity) AS `customer_points_usable`, `main_table`.`customer_id` FROM `{$this->getTable ( 'rewards/transfer' )}` AS `main_table`
             LEFT JOIN `{$this->getTable ( 'rewards/transfer_reference' )}` AS `reference_table` ON main_table.rewards_transfer_id = reference_table.rewards_transfer_id
             LEFT JOIN `{$this->getTable ( 'rewards/currency' )}` AS `currency_table` ON currency_table.rewards_currency_id=main_table.currency_id WHERE (quantity < 0) AND (status=4) GROUP BY `main_table`.`customer_id`,
                    `main_table`.`currency_id`
            ) AS `points_table`
            GROUP BY `points_table`.`customer_id`
        ";
        $results = $this->_getWriteAdapter()->query($sql)->fetchAll();
        
		if (count ( $results )) {
			$this->_getWriteAdapter ()->insertOnDuplicate ( $this->getIdxTable (), $results );
		}
		return $this;
	}
	
	/**
	 * Reindex one customer balance
	 * 
	 * @name reindexUpdate
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	public function reindexUpdate($customerId) {
		$this->_getWriteAdapter ()->insertOnDuplicate ( $this->getIdxTable (), $this->_getCustomerBalance ( $customerId ) );
		return $this;
	}
	
	/**
	 * Reindex one customer balance
	 * 
	 * @name reindexUpdate
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	public function reindexDelete($customerId) {
		$adapter = $this->_getWriteAdapter ();
		$where = $adapter->quoteInto ( "{$this->getIdFieldName()} = ?", $customerId );
		$select = $adapter->delete ( $this->getIdxTable (), $where );
		return $this;
	}
	
	/**
	 * Returns the index table name
	 * 
	 * @see Mage_Index_Model_Mysql4_Abstract::getIdxTable()
	 * @param mixed $table
	 * @return mixed
	 */
	public function getIdxTable($table = null) {
		return $this->getTable ( 'rewards/customer_indexer_points' );
	}
	
	/**
	 * Returns one customer balance
	 * 
	 * @access protected
	 * @param int $customer_id if null, retrieve all the customer balances
	 * @return array
	 */
	protected function _getCustomerBalance($customer_id = null) {
		$customerModel = Mage::getModel ( 'rewards/customer' );
		
		$customers = $customerModel->getCollection ();
		
		// If a customer id was specified, filter out list by that customer id.
		if(!empty($customer_id) ) {
            $customers->addFieldToFilter('entity_id', $customer_id);
        }
		
		$customerIdxData = array ();
		foreach ( $customers as $customer ) {
			$customer = Mage::getModel ( 'rewards/customer' )->load ( $customer->getId () );
			
			// If the customer id is not there it means that the customer is not complete and should not have a points balance
			if(!$customer->getId()) {
			    continue;
			}
			
			$customerIdxData [] = array ('customer_id' => $customer->getId (), 
				'customer_points_usable' => array_sum ( $customer->getRealUsablePoints () ) 
			);
		}
		
		return $customerIdxData;
	}
	
	/**
	 * Creates the table for indexing
	 * 
	 * @access private
	 * @return TBT_Rewards_Model_Mysql4_Customer_Indexer_Points
	 */
	private function _createTable() {
		// Create the table if not exists
		$createTableSql = "CREATE TABLE IF NOT EXISTS `{$this->getIdxTable()}` (
			`customer_id` INT( 11 ) NOT NULL ,
			`customer_points_usable` INT( 11 ) NOT NULL ,
			PRIMARY KEY (  `customer_id` )
			) ENGINE=InnoDB DEFAULT CHARSET=utf8; ";
		$this->_getWriteAdapter ()->query ( $createTableSql );
		// Delete all records
		$this->_getWriteAdapter ()->delete ( $this->getIdxTable () );
		return $this;
	}

}