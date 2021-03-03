<?php
class ModelExtensionModuleAltapay extends Model {

	public function addOrder($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "altapay_orders SET order_id='".$data['shop_orderid']."', created='".date('Y-m-d H:i:s', time())."', modified='".date('Y-m-d H:i:s', time())."', amount='".$data['amount']."', currency_code='".$data['currency']."', transaction_id='".$data['transaction_id']."', capture_status='0', void_status='0', refund_status='0'");
	}
}
