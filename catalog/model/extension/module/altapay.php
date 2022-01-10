<?php
class ModelExtensionModuleAltapay extends Model {

	public function addOrder($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "altapay_orders SET order_id='".(int)$data['shop_orderid']."', created='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', modified='".$this->db->escape((string)date('Y-m-d H:i:s', time()))."', amount='".(float)$data['amount']."', currency_code='".$this->db->escape((string)$data['currency'])."', transaction_id='".$this->db->escape((string)$data['transaction_id'])."', capture_status='0', void_status='0', refund_status='0'");
	}
}
