<?php

// Load altapay API
require_once(dirname(__file__,4) .'/altapay/altapay/altapay-php-sdk/lib/AltapayMerchantAPI.class.php');
require_once(dirname(__file__,4).'/traits/traits.php');

class ControllerExtensionPaymentAltapay{key} extends Controller {

	use traitTransactionInfo;

	private $terminal_key = '{name}';

	public function index() {
		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['text_loading'] = $this->language->get('text_loading');

		$data['continue'] = $this->url->link('checkout/success');

		return $this->load->view('extension/payment/Altapay_{key}', $data);
	}
	
	public function confirm() {			
		if ($this->session->data['payment_method']['code'] == 'Altapay_{key}') {
			// Load settings model
			$this->load->model('setting/setting');
			
			// Load order model
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $order_info["shipping_custom_field"] = null;
            $order_info["payment_custom_field"] = null;
//			print_r($order_info);
			// Decode the HTML entities from the address data
			$order_info = $this->decodeHtmlEntitiesArrayValues($order_info);
			// Connect to Altapay
			$api = $this->api_login();

			// Create data for the order
			$amount = (float) number_format($order_info['total'] * $order_info['currency_value'], 2, '.', '');
			$currency = $order_info['currency_code'];

			// Billing company overrides the billing last name. Idem for shipping last name:
			$billing_lastname = $order_info['payment_company'] ? $order_info['payment_company'] : $order_info['payment_lastname'];
			$shipping_lastname = $order_info['shipping_company'] ? $order_info['shipping_company'] : $order_info['shipping_lastname'];
			
			
			if(empty($order_info['shipping_iso_code_2']) || empty($order_info['shipping_zone']) || empty($order_info['shipping_city']))
			{
				$order_info['shipping_iso_code_2'] = $order_info['payment_iso_code_2'];
				$order_info['shipping_zone'] = $order_info['payment_zone'];
				$order_info['shipping_city'] = $order_info['payment_city'];
			}
			// Set customer data
			$customer_info = array(
				'billing_firstname' => $order_info['payment_firstname'],
				'billing_lastname' => $billing_lastname,
				'billing_address' => ($order_info['payment_address_2']) ? $order_info['payment_address_1']."\n".$order_info['payment_address_2'] : $order_info['payment_address_1'],
				'billing_postal' => $order_info['payment_postcode'],
				'billing_city' => $order_info['payment_city'],
				'billing_region' => $order_info['payment_zone'],
				'billing_country' => $order_info['payment_iso_code_2'],
				'email' => $order_info['email'],
				'shipping_firstname' => $order_info['shipping_firstname'],
				'shipping_lastname' => $shipping_lastname,
				'shipping_address' => ($order_info['shipping_address_2']) ? $order_info['shipping_address_1']."\n".$order_info['shipping_address_2'] : $order_info['shipping_address_1'],
				'shipping_postal' => $order_info['shipping_postcode'],
				'shipping_city' => $order_info['shipping_city'],
				'shipping_region' => $order_info['shipping_zone'],
				'shipping_country' => $order_info['shipping_iso_code_2'],
			);

			$cookie = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '';
			$language = 'en';

			/* $languages = array('da_DK' => 'da', 'sv_SE' => 'sv', 'nn_NO' => 'no', 'de_DE' => 'de');
			if ($languages[get_locale()]) {
				$language = $languages[get_locale()];
			} */

			// Get chosen page from altapay settings
			if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
				$base_path = $this->config->get('config_ssl');
			} else {
				$base_path = $this->config->get('config_url');
			}

			$config = array(
				'callback_form' => $base_path.'index.php?route=extension/payment/Altapay_{key}/paymentwindow',
				'callback_ok' => $base_path.'index.php?route=extension/payment/Altapay_{key}/accept',
				'callback_fail' => $base_path.'index.php?route=extension/payment/Altapay_{key}/fail',
				'callback_open' => $base_path.'index.php?route=extension/payment/Altapay_{key}/open',
				'callback_notification' => $base_path.'index.php?route=extension/payment/Altapay_{key}/callback',
			);

			// Make these as settings
			$payment_type = 'payment'; // TODO Get options from payment method
			if ($this->config->get('Altapay_{key}_payment_action') == 'capture') {
				$payment_type = 'paymentAndCapture';
			}

			// Add orderlines to the request
			$linedata = array();

			// Add shipping prices TODO
			$voucher = '';
			$shipping = '';
			$coupon = '';
			$tax_total = 0.00;
			$order_totals = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_info['order_id'] . "'");
			if ($order_totals->num_rows) {
				foreach ($order_totals->rows as $line) {
					if ($line['code'] === 'shipping') {
						$shipping = array(
							'description' => $order_info['shipping_method'],
							'itemId' => $order_info['shipping_code'],
							'quantity' => 1,
							'unitPrice' => $line['value'] * $order_info['currency_value'],
							'goodsType' => 'shipment'
						);

						$shipping_total = $line['value'] * $order_info['currency_value'];
					}

					if ($line['code'] === 'tax') {
						$tax_total = $line['value'] * $order_info['currency_value'];
					}

					if ($line['code'] == 'coupon') {
						$coupon = array(
							'description' => $line['title'],
							'itemId' => 'coupon',
							'quantity' => 1,
							'unitPrice' => $line['value'] * $order_info['currency_value'],
							'goodsType' => 'handling',
						);

						$coupon_total = $line['value'] * $order_info['currency_value'];
					}

					if ($line['code'] == 'sub_total') {
						$sub_total = $line['value'] * $order_info['currency_value'];
					}

					if ($line['code'] == 'total') {
						$total = $line['value'] * $order_info['currency_value'];
					}

					if ($line['code'] == 'voucher') {
						$voucher = array(
							'description' => $line['title'],
							'itemId' => 'voucher',
							'quantity' => 1,
							'unitPrice' => $line['value'] * $order_info['currency_value'],
						);
					}
				}
			}

			// Calculate tax percentage
			if ($tax_total > 0) {
				// Calculate tax total before discount
				$total_exvat_before_discount = $sub_total + $shipping_total;
				$total_exvat = $total - $tax_total;

				if (isset($voucher['unitPrice'])) {
					$total_exvat = $total_exvat + ($voucher['unitPrice'] * -1);
				}

				// Calculate the difference and find out of the tax total before discount
				$calc = $total_exvat_before_discount / $total_exvat;
				$tax_total = $tax_total * $calc;

				$calc_tax_total = $tax_total;
			}

			$orderlines = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'");
			if ($orderlines->num_rows) {
				foreach ($orderlines->rows as $line) {
					$tax_total = $tax_total - ($line['tax'] * $line['quantity']);

					// Set tax and price values
					if ($line['tax'] > 0) {
						$line['taxable'] = true;
						$line['price_inc_vat'] = ($line['price'] + $line['tax']) * $order_info['currency_value'];
						$line['price'] = $line['price'] * $order_info['currency_value'];
					} else {
						$line['taxable'] = false;
						$line['price_inc_vat'] = $line['price'] * $order_info['currency_value'];
						$line['price'] = $line['price'] * $order_info['currency_value'];
					}

					// Get orderline sku
					$product_info = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$line['product_id'] . "'");
					if ($product_info->num_rows) {
						$line['sku'] = $product_info->row['sku'];
					} else {
						$line['sku'] = '';
					}

					if (!$line['sku']) {
						$line['sku'] = $line['model'];
					}

					$line['tax'] = $line['tax'] * $line['quantity'] * $order_info['currency_value'];

					$linedata[] = array(
						'description' => $line['name'],
						'itemId' => ($line['sku']) ? $line['sku'] : $line['product_id'],
						'quantity' => $line['quantity'],
						'unitPrice' => (float) number_format($line['price'], 2, '.', ''),
						'taxAmount' => (float) number_format($line['tax'], 2, '.', ''),
						//'discount' => (float) ($line_discount_percent > 0) ? $line_discount_percent : '',
					);
				}
			}

			$transactionInfo = $this->transactionInfo();

			// Add shipping to orderlines
			if ($shipping) {
				// Check if shipping includes tax by checking total tax against tax from the orderlines etc. and add that tax amount to shipping cost.
				if ($tax_total > 0) {
					$shipping['taxAmount'] = $tax_total * $order_info['currency_value'];
				}
				$linedata[] = $shipping;
			}

			if ($coupon) {
				if ($tax_total > 0) {
					if ($coupon_total <> 0) {
						// Calculate discount inkl. tax. Add shipping if it includes tax
						$discount_percent = $coupon_total / ($sub_total + $shipping_total);

						$subtotal_inc_vat = $sub_total + $calc_tax_total + $shipping_total;

						$discount_inc_vat = $subtotal_inc_vat * $discount_percent;
						$coupon['unitPrice'] = $discount_inc_vat;
					}
				} else {
					if ($coupon_total <> 0) {
						// Calculate discount inkl. tax, Add shipping if it includes tax
						$discount_percent = $coupon_total / $sub_total;

						$subtotal_inc_vat = $sub_total + $calc_tax_total;

						$discount_inc_vat = $subtotal_inc_vat * $discount_percent;
						$coupon['unitPrice'] = $discount_inc_vat;
					}
				}

				$linedata[] = $coupon;
			}

			if ($voucher) {
				$linedata[] = $voucher;
			}

			$response = $api->createPaymentRequest($this->terminal_key, $order_info['order_id'], $amount, $currency, $payment_type, $customer_info, $cookie, $language, $config, $transactionInfo, $linedata);

			if( !$response->wasSuccessful() ) {
				echo json_encode( array('status' => 'error', 'message' => $response->getErrorMessage()) );
				exit;
			}
			$redirectURL = $response->getRedirectURL();

			echo json_encode( array('status' => 'ok', 'redirect' => $redirectURL) );
			exit;
		}
	}

	public function accept() {
		// Load settings model
		$this->load->model('setting/setting');

		// Load order model
		$this->load->model('checkout/order');

		// Load altapay model
		$this->load->model('extension/module/altapay');

		// Get postdata
		$postdata = $_POST;

		// Define postdata
		$order_id = $postdata['shop_orderid'];
		$currency = $postdata['currency'];
		$txnid = $postdata['transaction_id'];

		$error_message = '';
		if (isset($postdata['error_message'])) {
			$error_message = $postdata['error_message'];
		}

		$merchant_error_message = '';
		if (isset($postdata['merchant_error_message'])) {
			$merchant_error_message = $postdata['merchant_error_message'];
		}

		$status = $postdata['status'];
		$payment_status = $postdata['payment_status'];

		// Add meta data to the order
		if ($status === 'succeeded') {
			$comment = 'Payment authorized'; // TODO Make translation
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_Altapay_{key}_order_status_id'), $comment, true);

			// Add order to transaction table
			$this->model_extension_module_altapay->addOrder($postdata);

			// Redirect to order success
			$this->response->redirect($this->url->link('checkout/success', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	public function open() {
		// Load settings model
		$this->load->model('setting/setting');

		// Load order model
		$this->load->model('checkout/order');

		// Get postdata
		$postdata = $_POST;

		// Define postdata
		$order_id = $postdata['shop_orderid'];
		$currency = $postdata['currency'];
		$txnid = $postdata['transaction_id'];

		$error_message = '';
		if (isset($postdata['error_message'])) {
			$error_message = $postdata['error_message'];
		}

		$merchant_error_message = '';
		if (isset($postdata['merchant_error_message'])) {
			$merchant_error_message = $postdata['merchant_error_message'];
		}

		$status = $postdata['status'];
		$payment_status = $postdata['payment_status'];

		// Add meta data to the order
		if ($status === 'open') {
			$comment = 'Pending approval from acquirer'; // TODO Make translation
			$this->model_checkout_order->addOrderHistory($order_id, 1, $comment, true); // Get pending status

			// Redirect to order success
			$this->response->redirect($this->url->link('checkout/success', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	public function fail() {
		// Load settings model
		$this->load->model('setting/setting');

		// Load order model
		$this->load->model('checkout/order');

		// Get postdata
		$postdata = $_POST;

		// Define postdata
		$order_id = $postdata['shop_orderid'];
		$currency = $postdata['currency'];
		$txnid = $postdata['transaction_id'];
		$status = $postdata['status'];
		$payment_status = $postdata['payment_status'];

		$error_message = '';
		if (isset($postdata['error_message'])) {
			$error_message = $postdata['error_message'];
		}

		$merchant_error_message = '';
		if (isset($postdata['merchant_error_message'])) {
			$merchant_error_message = $postdata['merchant_error_message'];
		}

		// Either return to cart or set order as error.
		if ($error_message) {
			$this->session->data['error'] = 'Error: '.$error_message;
		}

		// Add merchant error message to order notes if exists // TODO Let shop owner select cancel and failed status
		if ($status == 'cancelled') {
			$this->model_checkout_order->addOrderHistory($order_id, 7, $merchant_error_message, false);
		} else {
			$this->model_checkout_order->addOrderHistory($order_id, 10, $merchant_error_message, false);
		}

		$this->response->redirect($this->url->link('checkout/cart', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function paymentwindow() {
		$this->load->language('extension/payment/Altapay_{key}');
		$this->document->setTitle($this->language->get('payment_window_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home'),
			'text' => $this->language->get('text_home')
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/payment/altapay_paymentwidow'),
			'text' => $this->language->get('heading_title')
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$data['postdata'] = $_POST;

        $data['products'] = array();
        $products = $this->cart->getProducts();
        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
            }

            // if ($product['image']) {
            //     $image = $this->model_tool_image->resize($product['image'], $this->config->get($this->config->get('config_theme') . '_image_cart_width'), $this->config->get($this->config->get('config_theme') . '_image_cart_height'));
            // } else {
            //     $image = '';
            // }

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                    if ($upload_info) {
                        $value = $upload_info['name'];
                    } else {
                        $value = '';
                    }
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                );
            }

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $price = false;
            }

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']);
            } else {
                $total = false;
            }

            $recurring = '';

            if ($product['recurring']) {
                $frequencies = array(
                    'day'        => $this->language->get('text_day'),
                    'week'       => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month'      => $this->language->get('text_month'),
                    'year'       => $this->language->get('text_year'),
                );

                if ($product['recurring']['trial']) {
                    $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                }

                if ($product['recurring']['duration']) {
                    $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                } else {
                    $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                }
            }

            $data['products'][] = array(
                'cart_id'   => $product['cart_id'],
              //  'thumb'     => $image,
                'name'      => $product['name'],
                'model'     => $product['model'],
                'option'    => $option_data,
                'recurring' => $recurring,
                'quantity'  => $product['quantity'],
                'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                'price'     => $price,
                'total'     => $total,
                'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
            );
        }


        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_'. $value['code'] . '_sort_order');
				
            }

            array_multisort($sort_order, SORT_ASC, $results);
	
            foreach ($results as $result) {
                if ($this->config->get('total_'.$result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
			}
			
			
            $sort_order = array();

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

			array_multisort($sort_order, SORT_ASC, $totals);

		}

        $data['totals'] = array();

        foreach ($totals as $total) {
            $data['totals'][]= array(
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
            );
		}

		return $this->response->setOutput($this->load->view('extension/payment/altapay_paymentwindow', $data));
	}

	public function callback() {
		// Load settings model
		$this->load->model('setting/setting');

		// Load order model
		$this->load->model('checkout/order');

		// Load altapay model
		$this->load->model('extension/module/altapay');

		// Get postdata
		$postdata = $_POST;

		// Define postdata
		$order_id = $postdata['shop_orderid'];
		$currency = $postdata['currency'];
		$txnid = $postdata['transaction_id'];

		$error_message = '';
		if (isset($postdata['error_message'])) {
			$error_message = $postdata['error_message'];
		}

		$merchant_error_message = '';
		if (isset($postdata['merchant_error_message'])) {
			$merchant_error_message = $postdata['merchant_error_message'];
		}

		$status = $postdata['status'];
		$payment_status = $postdata['payment_status'];

		// Add meta data to the order
		if ($status === 'succeeded') {
			$comment = 'Payment approved'; // TODO Make translation
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_Altapay_{key}_order_status_id'), $comment, true); // Get pending status

			// Add order to transaction table
			$this->model_extnesion_module_altapay->addOrder($postdata);

			// Redirect to order success
			$this->response->redirect($this->url->link('checkout/success', 'token=' . $this->session->data['token'], true));
		} else {
			$comment = $error_message;
			$this->model_checkout_order->addOrderHistory($order_id, 10, $comment, true); // Get pending status
		}
		echo 'OK';
		exit;
	}

	private function api_login() {
		// Load module settings
		$this->load->model('setting/setting');

		// Connect to Altapay
		$api = new AltapayMerchantAPI( $this->config->get('module_altapay_gateway_url'), $this->config->get('module_altapay_gateway_username'), $this->config->get('module_altapay_gateway_password') );
		$response = $api->login();

		if(!$response->wasSuccessful()) {
			return false;
		}
		return $api;
	}

	/**
	 * @param $arr
	 * @return array
	 */
	private function decodeHtmlEntitiesArrayValues($arr)
	{
		if (is_array($arr)) {
			foreach ($arr as $key=>$value) {
				if (is_string($value)) {
					$arr[$key] = html_entity_decode($value, ENT_NOQUOTES);
				}
			}
		}

		return $arr;
	}
}
