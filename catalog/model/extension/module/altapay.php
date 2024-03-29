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
    public function saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier, $type = 'captured')
    {

        $order_id = filter_var($order_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($order_id) {
            $query = $this->db->query(
                'SELECT id FROM `' . DB_PREFIX . 'altapay_order_reconciliation` WHERE order_id =' . $order_id .
                " AND reconciliation_identifier ='" . $this->db->escape((string)$reconciliation_identifier) .
                "' AND transaction_type ='" . $this->db->escape((string)$type) . "'");

            if (!$query->num_rows) {
                $this->db->query(
                    'INSERT INTO `' . DB_PREFIX . 'altapay_order_reconciliation` 
                (order_id, reconciliation_identifier, transaction_type) 
                VALUES ' . "('" . $order_id . "', 
                '" . $this->db->escape((string)$reconciliation_identifier) . "',
                '" . $this->db->escape((string)$type) . "')");
            }
        }
    }

    /**
     * @param int $order_id
     * @param bool $capture
     * @param bool $refund
     * @param bool $void
     *
     * @return void
     */
    public function updateOrderMeta($order_id, $capture = false, $refund = false, $void = false) {
        if (filter_var($order_id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false) {
            $status_query = "";
            if ($capture) {
                $status_query = "capture_status='1'";
            } elseif ($refund) {
                $status_query = "refund_status='1'";
            } elseif ($void) {
                $status_query = "void_status='1'";
            }
            if (!empty($status_query)) {
                $this->db->query("UPDATE " . DB_PREFIX . "altapay_orders SET modified='" . $this->db->escape((string)date('Y-m-d H:i:s', time())) . "', $status_query WHERE order_id='$order_id'");
            }
        }
    }

    /**
     * @param $order_id
     * @return array|false
     */
    public function getOrder($order_id)
    {
        $order_id = filter_var($order_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($order_id) {
            // Load order and transaction data
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "altapay_orders` WHERE `order_id` = '" . $order_id . "' LIMIT 1");
            $queryItems = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . $order_id . "'");
            $queryTotals = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE `order_id` = '" . $order_id . "'");

            if ($query->num_rows) {
                $query->row["items"] = $queryItems->rows;
                $query->row["totals"] = $queryTotals->rows;
                return $query->row;
            } else {
                return false;
            }
        }
        return false;
    }

}
