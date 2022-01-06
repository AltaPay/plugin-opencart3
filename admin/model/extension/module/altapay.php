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
	}
	
	public function uninstallDB() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "altapay_orders`;");
		
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
		if ($capture) {
			$this->db->query("UPDATE " . DB_PREFIX . "altapay_orders SET modified='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', capture_status='1' WHERE order_id='".(int)$order_id."'");
		} elseif ($refund) {
			$this->db->query("UPDATE " . DB_PREFIX . "altapay_orders SET modified='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', refund_status='1' WHERE order_id='".(int)$order_id."'");
		} elseif ($void) {
			$this->db->query("UPDATE " . DB_PREFIX . "altapay_orders SET modified='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', void_status='1' WHERE order_id='".(int)$order_id."'");
		}
	}
	
}
