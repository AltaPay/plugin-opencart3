<?php

require_once dirname(__file__, 3) . '/traits/traits.php';
require_once dirname(__file__, 4) . './../altapay-libs/autoload.php';

use Altapay\Api\Payments\CaptureReservation;
use Altapay\Api\Payments\RefundCapturedReservation;
use Altapay\Api\Payments\ReleaseReservation;
use Altapay\Exceptions\ResponseMessageException;
use Altapay\Exceptions\ResponseHeaderException;
use Altapay\Api\Others\Payments;
use Altapay\Request\OrderLine;

class ControllerExtensionPaymentAltapay{key} extends Controller
{

    use traitTransactionInfo;

    private $error = array();

    private $api_error = '';

    public function install()
    {
        // TODO call model for creating order transaction tabel
    }

    public function uninstall()
    {
        // TODO call model for deleting order transaction tabel
    }

    public function index()
    {
        $this->load->language('extension/payment/Altapay_{key}');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if ($this->request->post['payment_altapay_{key}_secret'] === '*****') {
                unset($this->request->post['payment_altapay_{key}_secret']);
            }
            $this->model_setting_setting->editSetting('payment_Altapay_{key}', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['heading_title']       = $this->language->get( 'heading_title' );
        $data['text_extensions']     = $this->language->get( 'Extensions' );
        $data['text_edit']           = $this->language->get( 'text_edit' );
        $data['text_enabled']        = $this->language->get( 'text_enabled' );
        $data['text_disabled']       = $this->language->get( 'text_disabled' );
        $data['text_all_zones']      = $this->language->get( 'text_all_zones' );
        $data['text_authorize']      = $this->language->get( 'text_authorize' );
        $data['text_capture']        = $this->language->get( 'text_capture' );
        $data['text_title']          = $this->language->get( 'text_title' );
        $data['text_secret']         = $this->language->get( 'text_secret' );
        $data['text_custom_message'] = $this->language->get( 'text_custom_message' );

        $data['entry_title']          = $this->language->get('entry_title');
        $data['entry_secret']         = $this->language->get('entry_secret');
        $data['entry_custom_message'] = $this->language->get('entry_custom_message');
        $data['entry_order_status']   = $this->language->get('entry_order_status');
        $data['entry_total']          = $this->language->get('entry_total');
        $data['entry_geo_zone']       = $this->language->get('entry_geo_zone');
        $data['entry_status']         = $this->language->get('entry_status');
        $data['entry_sort_order']     = $this->language->get('entry_sort_order');
        $data['entry_payment_action'] = $this->language->get('entry_payment_action');
        $data['entry_currency']       = $this->language->get('entry_currency');

        $data['help_total']        = $this->language->get('help_total');
        $data['help_order_status'] = $this->language->get('help_order_status');

        $data['button_save']   = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('Extensions'),
            'href' => $this->url->link('marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/Altapay_{key}',
                'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/Altapay_{key}',
            'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'],
            true);

        if (isset($this->request->post['payment_Altapay_{key}_title'])) {
            $data['payment_altapay_{key}_title'] = $this->request->post['payment_Altapay_{key}_title'];
        } else {
            $data['payment_altapay_{key}_title'] = $this->config->get('payment_Altapay_{key}_title');
        }

        if (isset($this->request->post['payment_Altapay_{key}_secret'])) {
            $data['payment_altapay_{key}_secret'] = $this->request->post['payment_Altapay_{key}_secret'];
        } else {
            $data['payment_altapay_{key}_secret'] = $this->config->get('payment_Altapay_{key}_secret');
        }

        if (isset($this->request->post['payment_Altapay_{key}_custom_message'])) {
            $data['payment_altapay_{key}_custom_message'] = $this->request->post['payment_Altapay_{key}_custom_message'];
        } else {
            $data['payment_altapay_{key}_custom_message'] = $this->config->get('payment_Altapay_{key}_custom_message');
        }

        if (isset($this->request->post['payment_Altapay_{key}_total'])) {
            $data['payment_altapay_{key}_total'] = $this->request->post['payment_Altapay_{key}_total'];
        } else {
            $data['payment_altapay_{key}_total'] = $this->config->get('payment_Altapay_{key}_total');
        }

        if (isset($this->request->post['payment_Altapay_{key}_order_status_id'])) {
            $data['payment_altapay_{key}_order_status_id']
                = $this->request->post['payment_Altapay_{key}_order_status_id'];
        } else {
            $data['payment_altapay_{key}_order_status_id']
                = $this->config->get('payment_Altapay_{key}_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['payment_Altapay_{key}_geo_zone_id'])) {
            $data['payment_altapay_{key}_geo_zone_id'] = $this->request->post['payment_Altapay_{key}_geo_zone_id'];
        } else {
            $data['payment_altapay_{key}_geo_zone_id'] = $this->config->get('payment_Altapay_{key}_geo_zone_id');
        }

        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->request->post['payment_Altapay_{key}_status'])) {
            $data['payment_altapay_{key}_status'] = $this->request->post['payment_Altapay_{key}_status'];
        } else {
            $data['payment_altapay_{key}_status'] = $this->config->get('payment_Altapay_{key}_status');
        }

        if (isset($this->request->post['payment_Altapay_{key}_sort_order'])) {
            $data['payment_altapay_{key}_sort_order'] = $this->request->post['payment_Altapay_{key}_sort_order'];
        } else {
            $data['payment_altapay_{key}_sort_order'] = $this->config->get('payment_Altapay_{key}_sort_order');
        }

        if (isset($this->request->post['payment_Altapay_{key}_payment_action'])) {
            $data['payment_altapay_{key}_payment_action']
                = $this->request->post['payment_Altapay_{key}_payment_action'];
        } else {
            $data['payment_altapay_{key}_payment_action'] = $this->config->get('payment_Altapay_{key}_payment_action');
        }

        $this->load->model('localisation/currency');
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();

        if (isset($this->request->post['payment_Altapay_{key}_currency_id'])) {
            $data['payment_altapay_{key}_currency_id'] = $this->request->post['payment_Altapay_{key}_currency_id'];
        } else {
            $data['payment_altapay_{key}_currency_id'] = $this->config->get('payment_Altapay_{key}_currency_id');
        }

        if (!empty($data['payment_altapay_{key}_secret'])) {
            $data['payment_altapay_{key}_secret'] = '*****';
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/Altapay_{key}', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/Altapay_{key}')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    // Legacy 2.0.0
    public function orderAction()
    {
        return $this->order();
    }

    // Legacy 2.0.3
    public function action()
    {
        return $this->order();
    }

    public function order()
    {
        $this->load->language('extension/payment/Altapay_{key}');

        $this->load->model('extension/module/altapay');
        $order = $this->model_extension_module_altapay->getOrder($this->request->get['order_id']);

        if ($order) {
            $data = array();

            foreach ($order['totals'] as $orderTotal) {
                if ($orderTotal['code'] == 'shipping') {
                    $order['shipping']['name']  = $orderTotal['title'];
                    $order['shipping']['price'] = $orderTotal['value'];
                }
            }
            $order['totals']    = "";
            $data['order_data'] = $order;

            $reserved = 0;
            $captured = 0;
            $refunded = 0;
            $charge   = 0;
            $reconciliation_identifiers = $this->model_extension_module_altapay->getOrderReconciliationIdentifiers($this->request->get['order_id']);

            $api = new Payments($this->getAuth());
            $api->setTransaction($order['transaction_id']);
            // Get payment data
            $payment = $api->call();

            if ($payment) {
                foreach ($payment as $pay) {
                    $reserved += (float)$pay->ReservedAmount;
                    $captured += (float)$pay->CapturedAmount;
                    $refunded += (float)$pay->RefundedAmount;
                    $charge   = $reserved - $captured - $refunded;
                    if ($charge <= 0) {
                        $charge = 0;
                    }
                }
            }

            $data['captured']   = number_format($captured, 2);
            $data['reserved']   = number_format($reserved, 2);
            $data['refunded']   = number_format($refunded, 2);
            $data['chargeable'] = number_format($charge, 2);

            $data['text_payment_info']      = $this->language->get('text_payment_info');
            $data['text_order_total']       = $this->language->get('text_order_total');
            $data['text_captured_amount']   = $this->language->get('text_captured_amount');
            $data['text_refund_amount']     = $this->language->get('text_refund_amount');
            $data['text_reserved_amount']   = $this->language->get('text_reserved_amount');
            $data['text_chargeable_amount'] = $this->language->get('text_chargeable_amount');
            $data['text_allow_amount']      = $this->language->get('text_allow_amount');
            $data['text_amount']            = $this->language->get('text_amount');
            $data['text_confirm_capture']   = $this->language->get('text_confirm_capture');
            $data['text_confirm_refund']    = $this->language->get('text_confirm_refund');
            $data['text_confirm_release']   = $this->language->get('text_confirm_release');
            $data['text_reconciliation_identifier'] = $this->language->get('text_reconciliation_identifier');
            $data['text_transaction_types'] = $this->language->get('text_transaction_types');

            $data['text_released'] = $this->language->get('text_released');
            $data['btn_release']   = $this->language->get('btn_release');
            $data['btn_refund']    = $this->language->get('btn_refund');
            $data['btn_capture']   = $this->language->get('btn_capture');
            $data['user_token']    = $this->request->get['user_token'];
            $data['order_id']      = $this->request->get['order_id'];
            $data['reconciliation_identifiers']      = $reconciliation_identifiers;

            return $this->load->view('extension/payment/Altapay_{key}_order', $data);
        }
    }

    public function capture()
    {
        $this->load->language('extension/payment/Altapay_{key}');
        $order_id   = $this->request->post['order_id'];
        $amount     = (double)$this->request->post['capture_amount'];
        $orderLines = array();
        if (array_key_exists('orderLines', $this->request->post)) {
            foreach ($this->request->post['orderLines'] as $item) {
                $orderLine            = new OrderLine(
                    $item['description'],
                    $item['itemId'],
                    $item['quantity'],
                    $item['unitPrice']
                );
                $orderLine->taxAmount = $item['taxAmount'];
                $orderLines[]         = $orderLine;
            }
        }

        if ($order_id && $amount > 0) {
            $this->load->model('extension/module/altapay');
            $order    = $this->model_extension_module_altapay->getOrder($order_id);
            $txnID    = $order['transaction_id'];
            $response = '';

            if ($txnID) {
                if ($this->altapayApiLogin()) {
                    try {
                        $reconciliation_identifier = sha1($order_id . time() . $txnID);
                        $api = new CaptureReservation($this->getAuth());
                        $api->setAmount(round($amount, 2));
                        $api->setOrderLines($orderLines);
                        $api->setTransaction($txnID);
                        $api->setReconciliationIdentifier($reconciliation_identifier);
                        $response = $api->call();
                    } catch (InvalidArgumentException $e) {
                        $this->api_error = $e->getMessage();
                    } catch (ResponseHeaderException $e) {
                        $this->api_error = $e->getMessage();
                    } catch (\Exception $e) {
                        $this->api_error = $e->getMessage();
                    }

                    if (!$response) {
                        // Log to order history TODO
                        $json = array(
                            'status'  => 'error',
                            'message' => $this->api_error
                        );
                    } else {
                        $api = new Payments($this->getAuth());
                        $api->setTransaction($txnID);
                        // Get payment data
                        $payment = $api->call();
                        if ($payment) {
                            $reserved = 0;
                            $captured = 0;
                            $refunded = 0;

                            foreach ($payment as $pay) {
                                $reserved += (float)$pay->ReservedAmount;
                                $captured += (float)$pay->CapturedAmount;
                                $refunded += (float)$pay->RefundedAmount;
                                $charge   = $reserved - $captured - $refunded;
                                if ($charge <= 0) {
                                    $charge = 0.00;
                                }
                            }

                            // Add to order history TODO
                            $json = array(
                                'status'     => 'ok',
                                'captured'   => number_format($captured, 2),
                                'reserved'   => number_format($reserved, 2),
                                'refunded'   => number_format($refunded, 2),
                                'chargeable' => number_format($charge, 2),
                                'message'    => 'Capture done',
                            );

                            // Update order meta
                            $this->model_extension_module_altapay->updateOrderMeta($order_id, 1);
                            // Add order reconciliation identifier
                            $this->model_extension_module_altapay->saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier);
                        }
                    }
                } else {
                    $json = array(
                        'status'  => 'error',
                        'message' => 'Could not connect to Altapay',
                    );
                }
            } else {
                $json = array(
                    'status'  => 'error',
                    'message' => 'Order got no transaction number',
                );
            }
        } else {
            $json = array(
                'status'  => 'error',
                'message' => 'Missing order id and amount',
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function refund()
    {
        $this->load->language('extension/payment/Altapay_{key}');
        $order_id   = $this->request->post['order_id'];
        $amount     = (double)$this->request->post['refund_amount'];
        $orderLines = array();
        if (array_key_exists('orderLines', $this->request->post)) {
            foreach ($this->request->post['orderLines'] as $item) {
                $orderLine            = new OrderLine(
                    $item['description'],
                    $item['itemId'],
                    $item['quantity'],
                    $item['unitPrice']
                );
                $orderLine->taxAmount = $item['taxAmount'];
                $orderLines[]         = $orderLine;
            }
        }

        if ($order_id && $amount > 0) {
            $this->load->model('extension/module/altapay');
            $order    = $this->model_extension_module_altapay->getOrder($order_id);
            $txnID    = $order['transaction_id'];
            $response = '';

            if ($txnID) {
                if ($this->altapayApiLogin()) {
                    try {
                        $reconciliation_identifier = sha1($order_id. time() . $txnID);
                        $api = new RefundCapturedReservation($this->getAuth());
                        $api->setAmount(round($amount, 2));
                        $api->setOrderLines($orderLines);
                        $api->setTransaction($txnID);
                        $api->setReconciliationIdentifier($reconciliation_identifier);
                        $response = $api->call();
                    } catch (ResponseHeaderException $e) {
                        $this->api_error = $e->getMessage();
                    } catch (\Exception $e) {
                        $this->api_error = $e->getMessage();
                    }

                    if ($response && in_array($response->Result,['Success', 'Open'], true)) {
                        $api = new Payments($this->getAuth());
                        $api->setTransaction($txnID);
                        // Get payment data
                        $payment = $api->call();
                        if ($payment) {
                            $reserved = 0;
                            $captured = 0;
                            $refunded = 0;

                            foreach ($payment as $pay) {
                                $reserved += (float)$pay->ReservedAmount;
                                $captured += (float)$pay->CapturedAmount;
                                $refunded += (float)$pay->RefundedAmount;
                                $charge   = $reserved - $captured - $refunded;
                                if ($charge <= 0) {
                                    $charge = 0.00;
                                }
                            }
                            $msg = 'Refund done';
                            if ($response->Result === 'Open'){
                                $msg = 'Refund is in progress.';
                            }
                            // Add to order history TODO
                            $json = array(
                                'status'     => 'ok',
                                'captured'   => number_format($captured, 2),
                                'reserved'   => number_format($reserved, 2),
                                'refunded'   => number_format($refunded, 2),
                                'chargeable' => number_format($charge, 2),
                                'message'    => $msg,
                            );
                            if($response->Result === 'Success') {
                                // Update order with status refunded
                                $this->model_extension_module_altapay->updateOrderMeta($order_id, false, true, false);
                                // Add order reconciliation identifier
                                $this->model_extension_module_altapay->saveOrderReconciliationIdentifier($order_id, $reconciliation_identifier, 'refunded');
                            }
                        }
                        // Set order to cancelled TODO
                    } else {
                        // Log to order history TODO
                        $json = array(
                            'status'  => 'error',
                            'message' => $this->api_error
                        );
                    }
                } else {
                    $json = array(
                        'status'  => 'error',
                        'message' => 'Could not connect to Altapay',
                    );
                }
            } else {
                $json = array(
                    'status'  => 'error',
                    'message' => 'Order got no transaction number',
                );
            }
        } else {
            $json = array(
                'status'  => 'error',
                'message' => 'Missing order id and amount',
            );
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function release()
    {
        $this->load->language('extension/payment/Altapay_{key}');
        $order_id = $this->request->post['order_id'];

        if ($order_id) {
            $this->load->model('extension/module/altapay');
            $order    = $this->model_extension_module_altapay->getOrder($order_id);
            $txnID    = $order['transaction_id'];
            $response = '';

            if ($txnID) {
                if ($this->altapayApiLogin()) {
                    // Release order
                    try {
                        $api = new ReleaseReservation($this->getAuth());
                        $api->setTransaction($txnID);
                        $response = $api->call();
                    } catch (ResponseHeaderException $e) {
                        $this->api_error = $e->getMessage();
                    } catch (ResponseMessageException $e) {
                        $this->api_error = $e->getMessage();
                    }

                    if ($response && $response->Result === 'Success') {
                        $api = new Payments($this->getAuth());
                        $api->setTransaction($txnID);
                        // Get payment data
                        $payment = $api->call();
                        if ($payment) {
                            $reserved = 0;
                            $captured = 0;
                            $refunded = 0;

                            foreach ($payment as $pay) {
                                $reserved += (float)$pay->ReservedAmount;
                                $captured += (float)$pay->CapturedAmount;
                                $refunded += (float)$pay->RefundedAmount;
                                $charge   = $reserved - $captured - $refunded;
                                if ($charge <= 0) {
                                    $charge = 0.00;
                                }
                            }
                            // Add to order history TODO
                            $json = array(
                                'status'     => 'ok',
                                'captured'   => number_format($captured, 2),
                                'reserved'   => number_format($reserved, 2),
                                'refunded'   => number_format($refunded, 2),
                                'chargeable' => number_format($charge, 2),
                                'message'    => 'Refund done',
                            );
                            // Update order with status captured
                            $this->model_extension_module_altapay->updateOrderMeta($order_id, false, false, 1);
                        }
                        // Set order to cancelled TODO
                    } else {
                        // Log to order history TODO
                        $json = array(
                            'status'  => 'error',
                            'message' => $this->api_error
                        );
                    }
                } else {
                    $json = array(
                        'status'  => 'error',
                        'message' => 'Could not connect to Altapay',
                    );
                }
            } else {
                $json = array(
                    'status'  => 'error',
                    'message' => 'Order got no transaction number',
                );
            }
        } else {
            $json = array(
                'status'  => 'error',
                'message' => 'Missing order id',
            );
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
