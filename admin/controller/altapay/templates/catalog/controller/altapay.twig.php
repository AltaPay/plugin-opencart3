<?php

require_once dirname(__file__, 4) . '/traits/traits.php';
require_once dirname(__file__, 4) . './../altapay-libs/autoload.php';

use Altapay\Api\Ecommerce\Callback;
use Altapay\Api\Ecommerce\PaymentRequest;
use Altapay\Api\Payments\CaptureReservation;
use Altapay\Api\Payments\RefundCapturedReservation;
use Altapay\Api\Payments\ReleaseReservation;
use Altapay\Exceptions\ClientException;
use Altapay\Exceptions\ResponseHeaderException;
use Altapay\Exceptions\ResponseMessageException;
use Altapay\Request\Address;
use Altapay\Request\Config;
use Altapay\Request\Customer;
use Altapay\Request\OrderLine;

class ControllerExtensionPaymentAltapay{key} extends Controller
{

    use traitTransactionInfo;

    private $terminal_key = '{name}';

    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading']   = $this->language->get('text_loading');
        $data['continue']       = $this->url->link('checkout/success');
        return $this->load->view('extension/payment/Altapay_{key}', $data);
    }

    public function confirm()
    {
        if ($this->session->data['payment_method']['code'] == 'Altapay_{key}') {
            // Load settings model
            $this->load->model('setting/setting');
            // Load order model
            $this->load->model('checkout/order');

            $order_info                          = $this->model_checkout_order->getOrder($this->session->data['order_id']);
            $order_info["shipping_custom_field"] = null;
            $order_info["payment_custom_field"]  = null;
            // Decode the HTML entities from the address data
            $order_info = $this->decodeHtmlEntitiesArrayValues($order_info);

            if(!$this->altapayApiLogin()){
                return false;
            }

            // Create data for the order
            $amount   = (float)number_format($order_info['total'] * $order_info['currency_value'], 2, '.', '');
            $currency = $order_info['currency_code'];

            if(empty($order_info['shipping_iso_code_2']) || empty($order_info['shipping_zone']) || empty($order_info['shipping_city']))
            {
                $order_info['shipping_iso_code_2'] = $order_info['payment_iso_code_2'];
                $order_info['shipping_zone']       = $order_info['payment_zone'];
                $order_info['shipping_city']       = $order_info['payment_city'];
            }


            $cookie   = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '';
            $language = 'en';

            // Get chosen page from AltaPay settings
            if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
                $base_path = $this->config->get('config_ssl');
            } else {
                $base_path = $this->config->get('config_url');
            }

            // Make these as settings
            $payment_type = 'payment'; // TODO Get options from payment method
            if ($this->config->get('payment_Altapay_{key}_payment_action') == 'capture') {
                $payment_type = 'paymentAndCapture';
            }

            // Add orderlines to the request
            $lineData = array();

            // Add shipping prices TODO
            $voucher           = '';
            $orderLineShipping = '';
            $shipping          = false;
            $coupon            = false;
            $tax_total         = 0;
            $calc_tax_total    = 0;
            $shipping_total    = 0;
            $order_totals      = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_info['order_id'] . "'");
            if ($order_totals->num_rows) {
                foreach ($order_totals->rows as $line) {
                    if ($line['code'] === 'shipping') {
                        $shipping_total    = $line['value'] * $order_info['currency_value'];
                        $orderLineShipping = new OrderLine(
                            $order_info['shipping_method'],
                            $order_info['shipping_code'],
                            1,
                            (float)number_format($line['value'] * $order_info['currency_value'], 2, '.', '')
                        );
                        $orderLineShipping->setGoodsType('shipment');
                        $shipping = true;
                    }

                    if ($line['code'] === 'tax') {
                        $tax_total = $line['value'] * $order_info['currency_value'];
                    }

                    if ($line['code'] == 'coupon') {
                        $coupon     = true;
                        $couponData = array(
                            'description' => $line['title'],
                            'itemId'      => 'coupon',
                            'unitPrice'   => $line['value'] * $order_info['currency_value']
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
                            'itemId'      => 'voucher',
                            'quantity'    => 1,
                            'unitPrice'   => $line['value'] * $order_info['currency_value'],
                        );
                    }
                }
            }

            // Calculate tax percentage
            if ($tax_total > 0) {
                // Calculate tax total before discount
                $total_exvat_before_discount = $sub_total + $shipping_total;
                $total_exvat                 = $total - $tax_total;

                if (isset($voucher['unitPrice'])) {
                    $total_exvat = $total_exvat + ($voucher['unitPrice'] * -1);
                }

                // Calculate the difference and find out of the tax total before discount
                $calc      = $total_exvat_before_discount / $total_exvat;
                $tax_total = $tax_total * $calc;

                $calc_tax_total = $tax_total;
            }

            $orderlines = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'");
            if ($orderlines->num_rows) {
                foreach ($orderlines->rows as $line) {
                    $tax_total = $tax_total - ($line['tax'] * $line['quantity']);
                    $unitCode  = 'unit';
                    if ($line['quantity'] > 1) {
                        $unitCode = 'units';
                    }

                    // Set tax and price values
                    if ($line['tax'] > 0) {
                        $line['taxable']       = true;
                        $line['price_inc_vat'] = ($line['price'] + $line['tax']) * $order_info['currency_value'];
                        $line['price']         = $line['price'] * $order_info['currency_value'];
                    } else {
                        $line['taxable']       = false;
                        $line['price_inc_vat'] = $line['price'] * $order_info['currency_value'];
                        $line['price']         = $line['price'] * $order_info['currency_value'];
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

                    $orderLine = new OrderLine(
                        $line['name'],
                        ($line['sku']) ? $line['sku'] : $line['product_id'],
                        $line['quantity'],
                        (float)number_format($line['price'], 2, '.', '')
                    );

                    $orderLine->taxAmount  = (float)number_format($line['tax'], 2, '.', '');
                    $orderLine->taxPercent = round(($line['tax'] / $line['price']) * 100, 2);
                    $orderLine->unitCode   = $unitCode;
                    $orderLine->setGoodsType('item');
                    $lineData[] = $orderLine;
                }
            }

            $transactionInfo = $this->transactionInfo();

            // Add shipping to orderlines
            if ($shipping) {
                // Check if shipping includes tax by checking total tax against tax from the orderlines etc. and add that tax amount to shipping cost.
                if ($tax_total > 0) {
                    $shipping['taxAmount'] = $tax_total * $order_info['currency_value'];
                }
                $lineData[] = $orderLineShipping;
            }

            if ($coupon) {
                if ($tax_total > 0) {
                    $discount_percent = $coupon_total / ($sub_total + $shipping_total);
                    $subtotal_inc_vat = $sub_total + $calc_tax_total + $shipping_total;
                } else {
                    $discount_percent = $coupon_total / $sub_total;
                    $subtotal_inc_vat = $sub_total + $calc_tax_total;
                }

                $discount_inc_vat = $subtotal_inc_vat * $discount_percent;
                $couponOrderLine  = new OrderLine(
                    $couponData['description'],
                    $couponData['itemId'],
                    1,
                    (float)number_format($discount_inc_vat, 2, '.', '')
                );
                $couponOrderLine->setGoodsType('handling');
                $lineData[] = $couponOrderLine;
            }

            if ($voucher) {
                $orderLine = new OrderLine(
                    $voucher['description'],
                    $voucher['itemId'],
                    1,
                    (float)number_format($voucher['unitPrice'], 2, '.', '')
                );

                $orderLine->setGoodsType('handling');
                $lineData[] = $orderLine;
            }

            //Add compensation
            $totalOrderAmount = round($amount, 2);
            $orderLinesTotal = 0;
            foreach ($lineData as $orderLine) {
                $orderLinePriceWithTax = ($orderLine->unitPrice * $orderLine->quantity) + $orderLine->taxAmount;
                $orderLinesTotal += $orderLinePriceWithTax - ($orderLinePriceWithTax * ($orderLine->discount / 100));
            }

            $totalCompensationAmount = round(($totalOrderAmount - $orderLinesTotal), 3);
            if (($totalCompensationAmount > 0 || $totalCompensationAmount < 0)) {
                $lineData[] = $this->compensationOrderline('total', $totalCompensationAmount);
            }

            $config = new Config();
            $config->setCallbackOk($base_path . 'index.php?route=extension/payment/Altapay_{key}/accept');
            $config->setCallbackFail($base_path . 'index.php?route=extension/payment/Altapay_{key}/fail');
            $config->setCallbackOpen($base_path . 'index.php?route=extension/payment/Altapay_{key}/open');
            $config->setCallbackNotification($base_path . 'index.php?route=extension/payment/Altapay_{key}/callback');
            $config->setCallbackForm($base_path . 'index.php?route=extension/payment/Altapay_{key}/paymentwindow');
            $config->setCallbackRedirect($base_path . 'index.php?route=extension/payment/Altapay_{key}/redirect');

            $customerInfo = $this->setCustomer($order_info);

            $request = new PaymentRequest($this->getAuth());
            $request->setTerminal($this->terminal_key)
                    ->setShopOrderId($order_info['order_id'])
                    ->setAmount($totalOrderAmount)
                    ->setCurrency($currency)
                    ->setTransactionInfo($transactionInfo)
                    ->setCookie($cookie)
                    ->setFraudService(null)
                    ->setLanguage($language)
                    ->setType($payment_type)
                    ->setConfig($config)
                    ->setCustomerInfo($customerInfo)
                    ->setOrderLines($lineData)
                    ->setSaleReconciliationIdentifier(sha1($order_info['order_id'] . time() . mt_rand()));

            if ($request) {
                try {
                    $response                 = $request->call();
                    $requestParams['result']  = 'success';
                    $requestParams['formurl'] = $response->Url;
                } catch (ClientException $e) {
                    $requestParams['result']  = 'error';
                    $requestParams['message'] = $e->getResponse()->getBody();
                } catch (ResponseHeaderException $e) {
                    $requestParams['result']  = 'error';
                    $requestParams['message'] = $e->getHeader()->ErrorMessage;
                } catch (ResponseMessageException $e) {
                    $requestParams['result']  = 'error';
                    $requestParams['message'] = $e->getMessage();
                } catch (\Exception $e) {
                    $requestParams['result']  = 'error';
                    $requestParams['message'] = $e->getMessage();
                }

                if (isset($requestParams['message']) && $requestParams['result'] === 'error') {
                    echo json_encode(array('status' => 'error', 'message' => $requestParams['message']));
                    exit;
                }

                $redirectURL = $response->Url;
                echo json_encode(array('status' => 'ok', 'redirect' => $redirectURL));
                exit;
            }
        }
    }

    /**
     * @param array $order_info
     *
     * @return Customer
     * @throws Exception
     */
    public function setCustomer($order_info)
    {
        $address        = new Address();
        $billing_address = ($order_info['payment_address_2']) ? $order_info['payment_address_1']."\n".$order_info['payment_address_2'] : $order_info['payment_address_1'];
        $shipping_address = ($order_info['shipping_address_2']) ? $order_info['shipping_address_1']."\n".$order_info['shipping_address_2'] : $order_info['shipping_address_1'];
        // Billing company overrides the billing last name. Idem for shipping last name:
        $billing_lastname = $order_info['payment_company'] ? $order_info['payment_company'] : $order_info['payment_lastname'];
        $shipping_lastname = $order_info['shipping_company'] ? $order_info['shipping_company'] : $order_info['shipping_lastname'];

        $billingInfo  = array(
            'firstname' => $order_info['payment_firstname'],
            'lastname'  => $billing_lastname,
            'address'   => $billing_address,
            'postcode'  => $order_info['payment_postcode'],
            'city'      => $order_info['payment_city'],
            'region'    => $order_info['payment_zone'],
            'country'   => $order_info['payment_iso_code_2'],
        );
        $shippingInfo = array(
            'firstname' => $order_info['shipping_firstname'],
            'lastname'  => $shipping_lastname,
            'address'   => $shipping_address,
            'postcode'  => $order_info['shipping_postcode'],
            'city'      => $order_info['shipping_city'],
            'region'    => $order_info['shipping_zone'],
            'country'   => $order_info['shipping_iso_code_2'],
        );
        if ($billing_address) {
            $this->populateAddressObject($billingInfo, $address);
        }
        $customer = new Customer($address);
        if ($shipping_address) {
            $shippingAddress = new Address();
            $this->populateAddressObject($shippingInfo, $shippingAddress);
            $customer->setShipping($shippingAddress);
        } else {
            $customer->setShipping($address);
        }
        $customer->setEmail($order_info['email']);
        $customer->setUsername($order_info['email']);
        $customer->setClientIP($_SERVER['REMOTE_ADDR']);
        $customer->setClientAcceptLanguage(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        $customer->setHttpUserAgent($_SERVER['HTTP_USER_AGENT']);
        $customer->setClientSessionID(crypt(session_id(), '$5$rounds=5000$customersessionid$'));

        return $customer;
    }


    /**
     * @param array   $addressInfo
     * @param Address $address
     *
     * @return void
     */
    private function populateAddressObject($addressInfo, $address)
    {
        $address->Firstname  = $addressInfo['firstname'];
        $address->Lastname   = $addressInfo['lastname'];
        $address->Address    = $addressInfo['address'];
        $address->City       = $addressInfo['city'];
        $address->PostalCode = $addressInfo['postcode'];
        $address->Region     = $addressInfo['region'] ?: '0';
        $address->Country    = $addressInfo['country'];
    }

    public function accept()
    {
        // Load settings model
        $this->load->model('setting/setting');

        // Load order model
        $this->load->model('checkout/order');

        // Load AltaPay model
        $this->load->model('extension/module/altapay');

        // Get post data
        $postdata = $_POST;

        // Define post data
        $order_id = $postdata['shop_orderid'];
        $currency = $postdata['currency'];
        $txnid    = $postdata['transaction_id'];

        $secret = $this->config->get('payment_Altapay_{key}_secret');
        $checksum = isset($postdata['checksum']) ? trim($postdata['checksum']) : '';
        if (!empty($checksum) and !empty($secret) and $this->calculateChecksum($postdata, $secret) !== $checksum) {
            exit;
        }

        $error_message = '';
        if (isset($postdata['error_message'])) {
            $error_message = $postdata['error_message'];
        }

        $merchant_error_message = '';
        if (isset($postdata['merchant_error_message'])) {
            $merchant_error_message = $postdata['merchant_error_message'];
        }

        $status               = $postdata['status'];
        $payment_status       = $postdata['payment_status'];
        $fraud_recommendation = !empty( $postdata['fraud_recommendation'] ) ? trim( $postdata['fraud_recommendation'] ) : '';

        // Add metadata to the order
        if ($status === 'succeeded') {

            $this->handleDuplicatePayment($postdata);

            if($this->detectFraud($order_id, $txnid, $postdata, $fraud_recommendation)){
                $this->session->data['error'] = 'Error: Payment Declined';
                $this->model_checkout_order->addOrderHistory($order_id, 1, "Fraud detected: {$postdata['fraud_explanation']}.", false);

                $this->response->redirect($this->url->link('checkout/cart', 'user_token=' . $this->session->data['user_token'], true));
            }

            // Add order to transaction table
            $this->model_extension_module_altapay->addOrder($postdata);

            // Save order reconciliation identifier
            $this->saveReconciliationIdentifier($order_id, $postdata);

            $comment = 'Payment authorized'; // TODO Make translation

            if ($postdata['type'] === 'paymentAndCapture' and $postdata['require_capture'] === 'true') {
                $reconciliation_identifier = sha1($order_id . time() . $txnid);
                try {
                    $api = new CaptureReservation($this->getAuth());
                    $api->setAmount(round($postdata['amount'], 2));
                    $api->setTransaction($postdata['transaction_id']);
                    $api->setReconciliationIdentifier($reconciliation_identifier);
                    $capture_response = $api->call();
                    if ($capture_response) {
                        $comment = 'Payment captured.';
                        $this->model_extension_module_altapay->updateOrderMeta($order_id, true);
                        $this->model_extension_module_altapay->saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier);
                    }
                } catch (InvalidArgumentException $e) {
                    $comment .= " Could not automatically capture payment.";
                } catch (ResponseHeaderException $e) {
                    $comment .= " Could not automatically capture payment.";
                } catch (\Exception $e) {
                    $comment .= " Could not automatically capture payment.";
                }
            }

            if($postdata['type'] === 'paymentAndCapture' and in_array($postdata['payment_status'], ['bank_payment_finalized', 'captured'], true)) {
                $comment = 'Payment captured.';
                $this->model_extension_module_altapay->updateOrderMeta($order_id, true);
            }

            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_Altapay_{key}_order_status_id'), $comment, true);

            // Redirect to order success
            $this->response->redirect($this->url->link('checkout/success', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    public function open()
    {
        // Load settings model
        $this->load->model('setting/setting');

        // Load order model
        $this->load->model('checkout/order');

        // Get post data
        $postdata = $_POST;

        // Define post data
        $order_id = $postdata['shop_orderid'];
        $currency = $postdata['currency'];
        $txnid    = $postdata['transaction_id'];

        $secret = $this->config->get('payment_Altapay_{key}_secret');
        $checksum = isset($postdata['checksum']) ? trim($postdata['checksum']) : '';
        if (!empty($checksum) and !empty($secret) and $this->calculateChecksum($postdata, $secret) !== $checksum) {
            exit;
        }

        $error_message = '';
        if (isset($postdata['error_message'])) {
            $error_message = $postdata['error_message'];
        }

        $merchant_error_message = '';
        if (isset($postdata['merchant_error_message'])) {
            $merchant_error_message = $postdata['merchant_error_message'];
        }

        $status         = $postdata['status'];
        $payment_status = $postdata['payment_status'];

        // Save order reconciliation identifier
        $this->saveReconciliationIdentifier($order_id, $postdata);

        // Add meta data to the order
        if ($status === 'open') {
            $comment = 'Pending approval from acquirer'; // TODO Make translation
            $this->model_checkout_order->addOrderHistory($order_id, 1, $comment, true); // Get pending status

            // Redirect to order success
            $this->response->redirect($this->url->link('checkout/success', 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    public function fail()
    {
        // Load settings model
        $this->load->model('setting/setting');

        // Load order model
        $this->load->model('checkout/order');

        // Load AltaPay model
        $this->load->model('extension/module/altapay');

        // Get post data
        $postdata = $_POST;

        // Define post data
        $order_id       = $postdata['shop_orderid'];
        $currency       = $postdata['currency'];
        $txnid          = $postdata['transaction_id'];
        $status         = $postdata['status'];
        $payment_status = $postdata['payment_status'];

        $secret = $this->config->get('payment_Altapay_{key}_secret');
        $checksum = isset($postdata['checksum']) ? trim($postdata['checksum']) : '';
        if (!empty($checksum) and !empty($secret) and $this->calculateChecksum($postdata, $secret) !== $checksum) {
            exit;
        }

        $max_date = '';
        $latest_trans_key = 0;
        $callback = new Callback($postdata);
        $response = $callback->call();
        foreach ($response->Transactions as $key => $value) {
            if ($value->CreatedDate > $max_date) {
                $max_date = $value->CreatedDate;
                $latest_trans_key = $key;
            }
        }

        if (isset($response->Transactions[$latest_trans_key])) {
            $transaction = $response->Transactions[$latest_trans_key];
        }

        // Add metadata to the order
        if (!empty($transaction) and $transaction->ReservedAmount > 0) {

            $this->handleDuplicatePayment($postdata);

            $fraud_recommendation = !empty( $postdata['fraud_recommendation'] ) ? trim( $postdata['fraud_recommendation'] ) : '';

            if($this->detectFraud($order_id, $txnid, $postdata, $fraud_recommendation)){
                $this->session->data['error'] = 'Error: Payment Declined';
                $this->model_checkout_order->addOrderHistory($order_id, 1, "Fraud detected: {$postdata['fraud_explanation']}.", false);

                $this->response->redirect($this->url->link('checkout/cart', 'user_token=' . $this->session->data['user_token'], true));
            }

            // Add order to transaction table
            $this->model_extension_module_altapay->addOrder($postdata);

            // Save order reconciliation identifier
            $this->saveReconciliationIdentifier($order_id, $postdata);

            $comment = 'Payment authorized'; // TODO Make translation

            if ($postdata['type'] === 'paymentAndCapture' and $postdata['require_capture'] === 'true') {
                $reconciliation_identifier = sha1($order_id . time() . $txnid);
                try {
                    $api = new CaptureReservation($this->getAuth());
                    $api->setAmount(round($postdata['amount'], 2));
                    $api->setTransaction($postdata['transaction_id']);
                    $api->setReconciliationIdentifier($reconciliation_identifier);
                    $capture_response = $api->call();
                    if ($capture_response) {
                        $comment = 'Payment captured.';
                        $this->model_extension_module_altapay->updateOrderMeta($order_id, true);
                        $this->model_extension_module_altapay->saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier);
                    }
                } catch (InvalidArgumentException $e) {
                    $comment .= " Could not automatically capture payment.";
                } catch (ResponseHeaderException $e) {
                    $comment .= " Could not automatically capture payment.";
                } catch (\Exception $e) {
                    $comment .= " Could not automatically capture payment.";
                }
            }

            if($postdata['type'] === 'paymentAndCapture' and in_array($postdata['payment_status'], ['bank_payment_finalized', 'captured'], true)) {
                $comment = 'Payment captured.';
                $this->model_extension_module_altapay->updateOrderMeta($order_id, true);
            }

            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_Altapay_{key}_order_status_id'), $comment, true);

            // Redirect to order success
            $this->response->redirect($this->url->link('checkout/success', 'user_token=' . $this->session->data['user_token'], true));
        }

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
            $this->session->data['error'] = 'Error: ' . $error_message;
        }

        // Add merchant error message to order notes if exists // TODO Let shop owner select cancel and failed status
        if ($status == 'cancelled') {
            $this->model_checkout_order->addOrderHistory($order_id, 7, $merchant_error_message, false);
        } else {
            $this->model_checkout_order->addOrderHistory($order_id, 10, $merchant_error_message, false);
        }

        $this->response->redirect($this->url->link('checkout/cart', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function paymentwindow()
    {
        // Get post data
        $postdata = $_POST;
        $secret   = $this->config->get('payment_Altapay_{key}_secret');
        $checksum = isset($postdata['checksum']) ? trim($postdata['checksum']) : '';
        if (!empty($checksum) and !empty($secret) and $this->calculateChecksum($postdata, $secret) !== $checksum) {
            exit;
        }

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

        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');
        $data['postdata']       = $_POST;

        $data['products'] = array();
        $products         = $this->cart->getProducts();
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
        $taxes  = $this->cart->getTaxes();
        $total  = 0;

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
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');

            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
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
            $data['totals'][] = array(
                'title' => $total['title'],
                'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
            );
        }

        return $this->response->setOutput($this->load->view('extension/payment/altapay_paymentwindow', $data));
    }

    public function redirect()
    {
        $this->load->language('extension/payment/Altapay_{key}');
        $this->document->setTitle($this->language->get('payment_window_title'));

        return $this->response->setOutput($this->load->view('extension/payment/altapay_redirect'));
    }

    public function callback()
    {
        // Load settings model
        $this->load->model('setting/setting');

        // Load order model
        $this->load->model('checkout/order');

        // Load AltaPay model
        $this->load->model('extension/module/altapay');

        // Get post data
        $postdata = $_POST;

        // Define postdata
        $order_id = $postdata['shop_orderid'];
        $currency = $postdata['currency'];
        $txnid    = $postdata['transaction_id'];

        $secret = $this->config->get('payment_Altapay_{key}_secret');
        $checksum = isset($postdata['checksum']) ? trim($postdata['checksum']) : '';
        if (!empty($checksum) and !empty($secret) and $this->calculateChecksum($postdata, $secret) !== $checksum) {
            exit;
        }

        $error_message = '';
        if (isset($postdata['error_message'])) {
            $error_message = $postdata['error_message'];
        }

        $merchant_error_message = '';
        if (isset($postdata['merchant_error_message'])) {
            $merchant_error_message = $postdata['merchant_error_message'];
        }

        $status               = $postdata['status'];
        $payment_status       = $postdata['payment_status'];
        $fraud_recommendation = !empty( $postdata['fraud_recommendation'] ) ? trim( $postdata['fraud_recommendation'] ) : '';

        // Add meta data to the order
        if ($status === 'succeeded') {

            $this->handleDuplicatePayment($postdata);

            if($this->detectFraud($order_id, $txnid, $postdata, $fraud_recommendation)){
                $this->session->data['error'] = 'Error: Payment Declined';
                $this->model_checkout_order->addOrderHistory($order_id, 1, "Fraud detected: {$postdata['fraud_explanation']}.", false);

                $this->response->redirect($this->url->link('checkout/cart', 'user_token=' . $this->session->data['user_token'], true));
            }

            // Save order reconciliation identifier
            $this->saveReconciliationIdentifier($order_id, $postdata);

            if($payment_status === 'bank_payment_refunded') {
                $row = $this->db->query("SELECT transaction_id FROM `" . DB_PREFIX . "altapay_orders` WHERE `capture_status` = '1' AND `order_id` = '" . (int)$order_id . "' LIMIT 1")->row;
                if ($row and $row['transaction_id'] === $txnid) {
                    $comment = 'Payment refunded.'; // TODO Make translation
                    $this->model_checkout_order->addOrderHistory($order_id, 11, $comment, true);
                    // Update order with status refunded
                    $this->model_extension_module_altapay->updateOrderMeta($order_id, false, true, false);
                }
                exit('Order refund status updated.');
            }
            // Add order to transaction table
            $this->model_extension_module_altapay->addOrder($postdata);

            $comment = 'Payment approved'; // TODO Make translation
            $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('payment_Altapay_{key}_order_status_id'), $comment, true); // Get pending status

            // Redirect to order success
            $this->response->redirect($this->url->link('checkout/success', 'token=' . $this->session->data['token'], true));
        } else {
            $comment = $error_message;
            $this->model_checkout_order->addOrderHistory($order_id, 10, $comment, true); // Get pending status
        }
        echo 'OK';
        exit;
    }

    /**
     * @param $arr
     *
     * @return array
     */
    private function decodeHtmlEntitiesArrayValues($arr)
    {
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                if (is_string($value)) {
                    $arr[$key] = html_entity_decode($value, ENT_NOQUOTES);
                }
            }
        }

        return $arr;
    }

    /**
     * @param $order_id
     * @param $post_data
     *
     * @return void
     */
    private function saveReconciliationIdentifier($order_id, $post_data)
    {
        $callback = new Callback($post_data);
        $response = $callback->call();
        if ($response && is_array($response->Transactions) && !empty($response->Transactions[0]->ReconciliationIdentifiers)) {
            foreach ($response->Transactions[0]->ReconciliationIdentifiers as $reconciliationIdentifier) {
                $reconciliation_identifier = $reconciliationIdentifier->Id;
                $reconciliation_type = $reconciliationIdentifier->Type;

                $this->model_extension_module_altapay->saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier, $reconciliation_type);
            }
        }
    }

    /**
     * @param $post_data
     * @return void
     */
    function handleDuplicatePayment($post_data)
    {
        $order_id = $post_data['shop_orderid'];
        $txn_id = $post_data['transaction_id'];
        $order = $this->model_extension_module_altapay->getOrder($order_id);
        $transaction_id = ($order) ? $order['transaction_id'] : '';

        $max_date = '';
        $latest_trans_key = 0;
        $callback = new Callback($post_data);
        $response = $callback->call();
        foreach ($response->Transactions as $key => $value) {
            if ($value->CreatedDate > $max_date) {
                $max_date = $value->CreatedDate;
                $latest_trans_key = $key;
            }
        }
        //Exit if payment already completed against the same order and the new transaction ID is different
        if (!empty($transaction_id) and $transaction_id != $txn_id) {
            // Release duplicate transaction from the gateway side
            $auth = $this->getAuth();

            if (isset($response->Transactions[$latest_trans_key])) {
                $transaction = $response->Transactions[$latest_trans_key];
                if (in_array($transaction->TransactionStatus, ['captured', 'bank_payment_finalized'], true)) {
                    if (in_array($transaction['TransactionStatus'], ['captured', 'bank_payment_finalized'], true)) {
                        $api = new RefundCapturedReservation($auth);
                    } else {
                        $api = new ReleaseReservation($auth);
                    }
                    $api->setTransaction($txn_id);
                    $api->call();
                }
                exit;
            }
        }
    }

    private function compensationOrderline($itemID, $compensationAmount)
    {
        $orderLine = new OrderLine(
            'compensation',
            'comp-' . $itemID,
            1,
            $compensationAmount
        );

        $orderLine->taxAmount = 0;
        $orderLine->discount = 0;
        $orderLine->unitCode = 'unit';
        $orderLine->setGoodsType('handling');

        return $orderLine;
    }

}
