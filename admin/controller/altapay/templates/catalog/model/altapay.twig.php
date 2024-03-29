<?php
class ModelExtensionPaymentAltapay{key} extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/Altapay_{key}');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone_to_geo_zone` WHERE `geo_zone_id` = '" . (int)$this->config->get('altapay_geo_zone_id') . "' AND `country_id` = '" . (int)$address['country_id'] . "' AND (`zone_id` = '" . (int)$address['zone_id'] . "' OR `zone_id` = '0')");

		if ($this->config->get('payment_altapay_total') > 0 && $this->config->get('payment_altapay_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_altapay_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {

			$method_data = array(
				'code'       => 'Altapay_{key}',
				'title'      => $this->config->get('payment_Altapay_{key}_title'),
				'secret'     => $this->config->get('payment_Altapay_{key}_secret'),
				'terms'      => $this->config->get('payment_Altapay_{key}_custom_message'),
				'sort_order' => $this->config->get('payment_altapay_sort_order')
			);
		}

		return $method_data;
	}
}
