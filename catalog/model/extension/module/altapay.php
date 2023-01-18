<?php
class ModelExtensionModuleAltapay extends Model {

	public function addOrder($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "altapay_orders SET order_id='".(int)$data['shop_orderid']."', created='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', modified='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', amount='".(float)$data['amount']."', currency_code='".$this->db->escape((string)$data['currency'])."', transaction_id='".$this->db->escape((string)$data['transaction_id'])."', capture_status='0', void_status='0', refund_status='0'");
	}

    /**
     * @param int $order_id
     * @param string $reconciliation_identifier
     * @param string $type
     *
     * @return void
     */
    function saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier, $type = 'captured') {
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
