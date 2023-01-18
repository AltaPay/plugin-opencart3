<?php

class ModelExtensionModuleAltapay extends Model {

	public function installDB() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "altapay_orders` (
			  `altapay_order_id` int(11) NOT NULL AUTO_INCREMENT,
			  `order_id` int(11) NOT NULL,
			  `created` DATETIME NOT NULL,
			  `modified` DATETIME NOT NULL,
			  `amount` DECIMAL( 10, 2 ) NOT NULL,
			  `currency_code` CHAR(3) NOT NULL,
			  `transaction_id` VARCHAR(24) NOT NULL,
			  `capture_status` INT(1) DEFAULT NULL,
			  `void_status` INT(1) DEFAULT NULL,
			  `refund_status` INT(1) DEFAULT NULL,
			  PRIMARY KEY (`altapay_order_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

        $this->db->query('CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'altapay_order_reconciliation` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `order_id` int(11) unsigned NOT NULL,
            `reconciliation_identifier` varchar(255) NOT NULL,
            `transaction_type` varchar(255) NOT NULL,            
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;');
	}
	
	public function uninstallDB() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "altapay_orders`;");

		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "altapay_order_reconciliation`;");

		// Remove all payment methods TODO
	}

	public function getOrder($order_id) {
		// Load order and transaction data
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "altapay_orders` WHERE `order_id` = '" . (int)$order_id . "' LIMIT 1");
		$queryItems = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . (int)$order_id . "'");
		$queryTotals = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE `order_id` = '" . (int)$order_id . "'");

		if ($query->num_rows) {
			$query->row["items"]=$queryItems->rows;
            $query->row["totals"]=$queryTotals->rows;
			return $query->row;
		} else {
			return false;
		}
	}

	public function updateOrderMeta($order_id, $capture = false, $refund = false, $void = false) {
        $status_query = "";
		if ($capture) {
            $status_query = "capture_status='1'";
		} elseif ($refund) {
            $status_query = "refund_status='1'";
		} elseif ($void) {
            $status_query = "void_status='1'";
		}
        if (!empty($status_query)){
            $this->db->query("UPDATE " . DB_PREFIX . "altapay_orders SET modified='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', $status_query WHERE order_id='".(int)$order_id."'");
        }
	}

    /**
     * @param int $order_id
     *
     * @return
     */
   public function getOrderReconciliationIdentifiers($order_id)
    {
        $query = $this->db->query('SELECT reconciliation_identifier, transaction_type FROM `' . DB_PREFIX . 'altapay_order_reconciliation` WHERE order_id =' . (int) $order_id);

        if ($query->num_rows) {
            return $query->row;
        } else {
            return [];
        }
    }

    /**
     * @param int $order_id
     * @param string $reconciliation_identifier
     * @param string $type
     *
     * @return void
     */
    public function saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier, $type = 'captured') {
        $query = $this->db->query(
            'SELECT id FROM `' . DB_PREFIX . 'altapay_order_reconciliation` WHERE order_id =' . (int) $order_id .
            " AND reconciliation_identifier ='" . $this->db->escape((string) $reconciliation_identifier) .
            "' AND transaction_type ='" . $this->db->escape((string) $type) . "'");

        if (!$query->num_rows) {
            $this->db->query(
                'INSERT INTO `' . DB_PREFIX . 'altapay_order_reconciliation` 
                (order_id, reconciliation_identifier, transaction_type) 
                VALUES ' . "('" . (int) $order_id . "', 
                '" . $this->db->escape((string) $reconciliation_identifier) . "',
                '" . $this->db->escape((string) $type) . "')");
        }
    }

}
