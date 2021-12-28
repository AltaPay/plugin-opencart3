<?php

require_once(dirname(__file__, 4) . './../altapay-libs/autoload.php');
require_once dirname(__file__, 3) . '/traits/traits.php';

use Altapay\Api\Others\Terminals;
use GuzzleHttp\Exception\ClientException;

class ControllerExtensionModuleAltapay extends Controller
{

    use traitTransactionInfo;

    private $error = array();
    private $forbiddenChars = array("'", '"', '-');

    public function index()
    {

        $this->load->language('extension/module/altapay');
        $this->load->model('setting/setting');
        $this->load->model('design/layout');
        $this->load->model('extension/module/altapay');
        $this->load->model('user/user_group');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['save_success'] = '';
        $data['refresh_terminals_success'] = '';
        $data['refresh_terminals_error'] = '';
        $data['sync_terminals_success'] = '';
        $data['sync_terminals_error'] = '';

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() && isset($_POST['terminalsync'])) {

            $query0 = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` LIKE 'altapay_terminals_refreshed'");

            if ($query0->num_rows and $query0->row['value']) {

                $query = $this->db->query("SELECT iso_code_2 FROM " . DB_PREFIX . "country WHERE `country_id` = " . $this->config->get('config_country_id'));

                if ($query->num_rows) {
                    $query1 = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` LIKE 'payment_Altapay_%'");

                    if ($query1->num_rows) {
                        $data['sync_terminals_error'] = $this->language->get('text_sync_already_configured_terminals_error');
                    } else {
                        try {
                            $api = new Terminals($this->getAuth());
                            $response = $api->call();
                            if (!$response) {
                                $data['sync_terminals_error'] = $this->language->get('text_AltaPay_login_error');
                            } else {
                                $i = 1;
                                foreach ($response->Terminals as $terminal) {
                                    if ($terminal->Country == $query->row['iso_code_2']) {
                                        $forbiddenChars = array("'", '"', '-');

                                        // Remove single and double quotes - avoid the placeholders replacement issue from templates
                                        // where single quotes are used for strings
                                        $termName = str_replace($forbiddenChars, "", $terminal->Title);
                                        preg_match_all("#([A-Z]+)#", str_replace(' ', '', ucwords($termName)), $output);
                                        $termKey = implode('', $output[1]);

                                        // Add settings for each terminal
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_title', $termName);
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_currency_id', $this->config->get('config_currency'));
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_order_status_id', '15');
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_geo_zone_id', '0');
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_payment_action', 'authorize');
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_status', '1');
                                        $this->addSettingField('payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_sort_order', $i);
                                        $this->addExtensionField('payment', 'Altapay_' . $termKey);

                                        // Add Access/Modify permissions to the admin user group
                                        $this->model_user_user_group->addPermission(1, 'access', 'extension/payment/Altapay_' . $termKey);
                                        $this->model_user_user_group->addPermission(1, 'modify', 'extension/payment/Altapay_' . $termKey);

                                        $i++;
                                    }
                                }
                                if ($i > 1) {
                                    $data['sync_terminals_success'] = $this->language->get('text_sync_success');
                                }else{
                                    $data['sync_terminals_error'] = $this->language->get('text_sync_no_matching_terminals_error');
                                }
                            }
                        } catch (ClientException $e) {
                            $data['sync_terminals_error'] = $this->language->get('text_AltaPay_login_error') . ' ' . $e->getMessage();
                        } catch (Exception $e) {
                            $data['sync_terminals_error'] = $this->language->get('text_AltaPay_login_error') . ' ' . $e->getMessage();
                        }
                    }

                } else {
                    $data['sync_terminals_error'] = $this->language->get('text_sync_error');
                }

            } else {
                $data['sync_terminals_error'] = $this->language->get('text_sync_error_refresh_required');
            }

        } else if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() && isset($_POST['terminalrefresh'])) {
            if (!$this->config->get('module_altapay_gateway_url') || !$this->config->get('module_altapay_gateway_username') || !$this->config->get('module_altapay_gateway_password')) {
                $data['refresh_terminals_error'] = $this->language->get('text_refresh_error');
            } else {
                try {
                    $api = new Terminals($this->getAuth());
                    $response = $api->call();
                    if (!$response) {
                        $data['refresh_terminals_error'] = $this->language->get('text_AltaPay_login_error');
                    }else{
                        foreach ($response->Terminals as $term) {
                            // Remove single and double quotes - avoid the placeholders replacement issue from templates
                            // where single quotes are used for strings
                            $term_name = str_replace($this->forbiddenChars, "", $term->Title);
                            preg_match_all("#([A-Z]+)#", str_replace(' ', '', ucwords($term_name)), $output);
                            $term_key = implode('', $output[1]);
                            $term_key_ = str_replace(' ', '_', $term_name);

                            $_terminals[] = array(
                                'key' => $term_key,
                                'name' => $term_name,
                            );

                            // Check if file exists
                            $dir = dirname(__DIR__, 4);
                            // ADMIN templates
                            $path = $dir . '/admin/controller/extension/payment/Altapay_' . $term_key . '.php';
                            if (file_exists($path)) {
                                unlink($path);
                            }

                            $language_path = $dir . '/admin/language/en-gb/extension/payment/Altapay_' . $term_key . '.php';
                            if (file_exists($language_path)) {
                                unlink($language_path);
                            }

                            $view_template_path = $dir . '/admin/view/template/extension/payment/Altapay_' . $term_key . '.twig';
                            if (file_exists($view_template_path)) {
                                unlink($view_template_path);
                            }

                            $order_view_template_path = $dir . '/admin/view/template/extension/payment/Altapay_' . $term_key . '_order.twig';
                            if (file_exists($order_view_template_path)) {
                                unlink($order_view_template_path);
                            }

                            $payment_template = file_get_contents($dir . '/admin/controller/altapay/templates/admin/controller/altapay.twig.php');
                            $payment_template_language = file_get_contents($dir . '/admin/controller/altapay/templates/admin/language/altapay.twig.php');
                            $payment_template_view_template = file_get_contents($dir . '/admin/controller/altapay/templates/admin/view/altapay.twig');
                            $payment_template_order_view_template = file_get_contents($dir . '/admin/controller/altapay/templates/admin/view/altapay_order.twig');

                            // FRONTEND / catalog templates

                            $frontend_model_path = $dir . '/catalog/model/extension/payment/Altapay_' . $term_key . '.php';
                            if (file_exists($frontend_model_path)) {
                                unlink($frontend_model_path);
                            }

                            $frontend_language_path = $dir . '/catalog/language/en-gb/extension/payment/Altapay_' . $term_key . '.php';
                            if (file_exists($frontend_language_path)) {
                                unlink($frontend_language_path);
                            }

                            $frontend_controller_path = $dir . '/catalog/controller/extension/payment/Altapay_' . $term_key . '.php';
                            if (file_exists($frontend_controller_path)) {
                                unlink($frontend_controller_path);
                            }

                            $frontend_view_path = $dir . '/catalog/view/theme/default/template/extension/payment/Altapay_' . $term_key . '.twig';
                            if (file_exists($frontend_view_path)) {
                                unlink($frontend_view_path);
                            }

                            $frontend_payment_model = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/model/altapay.twig.php');
                            $frontend_template_language = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/language/altapay.twig.php');
                            $frontend_template_controller = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/controller/altapay.twig.php');
                            $frontend_template_view = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/view/altapay_button.twig');

                            // Replace patterns
                            $content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $payment_template);
                            $payment_dir = $dir . '/admin/controller/extension/payment';

                            // Check if terminals folder is writable
                            if ($payment_dir) {
                                file_put_contents($path, $content);
                                chmod($path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            $language_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $payment_template_language);
                            $language_dir = $dir . '/admin/language/en-gb/extension/payment';

                            // Check if language folder is writable
                            if ($language_dir) {
                                file_put_contents($language_path, $language_content);
                                chmod($language_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            $view_template_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $payment_template_view_template);
                            $view_template_dir = $dir . '/admin/view/template/extension/payment';

                            // Check if language folder is writable
                            if (is_writable($view_template_dir)) {
                                file_put_contents($view_template_path, $view_template_content);
                                chmod($view_template_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            $order_view_template_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $payment_template_order_view_template);
                            $order_view_template_dir = $dir . '/admin/view/template/extension/payment';

                            // Check if language folder is writable
                            if (is_writable($order_view_template_dir)) {
                                file_put_contents($order_view_template_path, $order_view_template_content);
                                chmod($order_view_template_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            // FRONTEND Templates
                            $frontend_model_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $frontend_payment_model);
                            $frontend_model_dir = $dir . '/catalog/model/extension/payment';
                            // Check if language folder is writable
                            if (is_writable($frontend_model_dir)) {
                                file_put_contents($frontend_model_path, $frontend_model_content);
                                chmod($frontend_model_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            $frontend_language_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $frontend_template_language);
                            $frontend_language_dir = $dir . '/catalog/language/en-gb/extension/payment';
                            // Check if language folder is writable
                            if (is_writable($frontend_language_dir)) {
                                file_put_contents($frontend_language_path, $frontend_language_content);
                                chmod($frontend_language_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            $frontend_controller_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $frontend_template_controller);
                            $frontend_controller_dir = $dir . '/catalog/controller/extension/payment';
                            // Check if language folder is writable
                            if (is_writable($frontend_controller_dir)) {
                                file_put_contents($frontend_controller_path, $frontend_controller_content);
                                chmod($frontend_controller_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }

                            $frontend_view_content = str_replace(array('{key}', '{_key_}', '{name}'), array($term_key, $term_key_, $term_name), $frontend_template_view);
                            $frontend_view_dir = $dir . '/catalog/view/theme/default/template/extension/payment';
                            // Check if language folder is writable
                            if (is_writable($frontend_view_dir)) {
                                file_put_contents($frontend_view_path, $frontend_view_content);
                                chmod($frontend_view_path, 0664);
                            } else {
                                $data['refresh_terminals_error'] = $this->language->get('text_altapay_terminals_error');
                            }
                        }
                        $this->addSettingField('altapay_terminals_refreshed', 'altapay_terminals_refreshed', 1);
                        $data['refresh_terminals_success'] = $this->language->get('text_refresh_success');
                    }
                } catch (ClientException $e) {
                    $data['refresh_terminals_error'] = $this->language->get('text_AltaPay_login_error') . ' ' . $e->getMessage();
                } catch (Exception $e) {
                    $data['refresh_terminals_error'] = $this->language->get('text_AltaPay_login_error') . ' ' . $e->getMessage();
                }
            }
        } elseif (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate() && isset($_POST['savestay'])) {
            $this->model_setting_setting->editSetting('module_altapay', $this->request->post);
            $data['save_success'] = $this->language->get('text_success');
        } elseif (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_altapay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_content_top'] = $this->language->get('text_content_top');
        $data['text_content_bottom'] = $this->language->get('text_content_bottom');
        $data['text_column_left'] = $this->language->get('text_column_left');
        $data['text_column_right'] = $this->language->get('text_column_right');
        $data['text_pwa_button'] = $this->language->get('text_pwa_button');
        $data['text_pay_button'] = $this->language->get('text_pay_button');
        $data['text_a_button'] = $this->language->get('text_a_button');
        $data['text_gold_button'] = $this->language->get('text_gold_button');
        $data['text_darkgray_button'] = $this->language->get('text_darkgray_button');
        $data['text_lightgray_button'] = $this->language->get('text_lightgray_button');
        $data['text_small_button'] = $this->language->get('text_small_button');
        $data['text_medium_button'] = $this->language->get('text_medium_button');
        $data['text_large_button'] = $this->language->get('text_large_button');
        $data['text_x_large_button'] = $this->language->get('text_x_large_button');

        $data['entry_gateway_url'] = $this->language->get('entry_gateway_url');
        $data['entry_gateway_username'] = $this->language->get('entry_gateway_username');
        $data['entry_gateway_password'] = $this->language->get('entry_gateway_password');
        $data['entry_terminals'] = $this->language->get('entry_terminals');
        $data['entry_layout'] = $this->language->get('entry_layout');
        $data['entry_position'] = $this->language->get('entry_position');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_saveclose'] = $this->language->get('button_save_close');
        $data['button_savestay'] = $this->language->get('button_save_stay');
        $data['button_refresh_terminals'] = $this->language->get('button_refresh_terminals');
        $data['button_sync_terminals'] = $this->language->get('button_sync_terminals');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_module_add'] = $this->language->get('button_module_add');
        $data['button_remove'] = $this->language->get('button_remove');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/altapay',
                'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link('extension/module/altapay', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['module_altapay_gateway_url'])) {
            $data['module_altapay_gateway_url'] = $this->request->post['module_altapay_gateway_url'];
        } elseif ($this->config->get('module_altapay_gateway_url')) {
            $data['module_altapay_gateway_url'] = $this->config->get('module_altapay_gateway_url');
        } else {
            $data['module_altapay_gateway_url'] = '';
        }

        if (isset($this->request->post['module_altapay_gateway_username'])) {
            $data['module_altapay_gateway_username'] = $this->request->post['module_altapay_gateway_username'];
        } elseif ($this->config->get('module_altapay_gateway_username')) {
            $data['module_altapay_gateway_username'] = $this->config->get('module_altapay_gateway_username');
        } else {
            $data['module_altapay_gateway_username'] = '';
        }

        if (isset($this->request->post['module_altapay_gateway_password'])) {
            $data['module_altapay_gateway_password'] = $this->request->post['module_altapay_gateway_password'];
        } elseif ($this->config->get('module_altapay_gateway_password')) {
            $data['module_altapay_gateway_password'] = $this->config->get('module_altapay_gateway_password');
        } else {
            $data['module_altapay_gateway_password'] = '';
        }

        if (isset($this->request->post['module_altapay_status'])) {
            $data['module_altapay_status'] = $this->request->post['module_altapay_status'];
        } else {
            $data['module_altapay_status'] = $this->config->get('module_altapay_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['altapay_terminals'] = $this->getTerminals($data);

        $this->response->setOutput($this->load->view('extension/module/altapay', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/altapay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    private function getTerminals($data)
    {
        if (!$data['module_altapay_gateway_url'] || !$data['module_altapay_gateway_username'] || !$data['module_altapay_gateway_password']) {
            return false;
        }

        return true;
    }

    public function install()
    {
        // Add db for orders and transactions
        $this->load->model('extension/module/altapay');
        $this->model_extension_module_altapay->installDB();
    }

    public function uninstall()
    {
        // Remove db for orders and transactions
        $this->load->model('extension/module/altapay');
        $this->model_extension_module_altapay->uninstallDB();
    }

    /**
     * @param $code
     * @param $key
     * @param $value
     */
    public function addSettingField($code, $key, $value)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = '" . $key . "'");

        if ($query->num_rows) {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `code` = '" . $code . "', `value` = '" . $value . "' WHERE `key` = '" . $key . "'");
        } else {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "setting  (`store_id`,`code`,`key`, `value`,`serialized`) VALUES(0, '" . $code . "', '" . $key . "', '" . $value . "', 0)");
        }

    }


    /**
     * @param $type
     * @param $code
     */
    public function addExtensionField($type, $code)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = '" . $code . "'");
        if ($query->num_rows == 0) {
            $this->db->query("INSERT INTO  " . DB_PREFIX . "extension  (`type`,`code`) VALUES('" . $type . "', '" . $code . "')");
        }

    }
}
