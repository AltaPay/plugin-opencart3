<?php

require_once(__DIR__ . '/altapay-libs/autoload.php');

use Altapay\Api\Others\Terminals;
use Altapay\Api\Test\TestAuthentication;
use GuzzleHttp\Exception\ClientException;
use Altapay\Authentication;

// Settings
$apiUser = '~gatewayusername~';
$apiPass = '~gatewaypass~';
$url = '~gatewayurl~';

try {
    $api = new TestAuthentication(new Authentication($apiUser, $apiPass, $url));
    $response = $api->call();
    if (!$response) {
        echo "API credentials are incorrect";
        exit();
    }
} catch (ClientException $e) {
    echo "Error:" . $e->getMessage();
    exit();
} catch (Exception $e) {
    echo "Error:" . $e->getMessage();
    exit();
}

require_once(__DIR__ . '/admin/config.php');
require_once(__DIR__ . '/system/startup.php');

// Registry
$registry = new Registry();
// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);
// Config
$config = new Config();
$registry->set('config', $config);
// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE, DB_PORT);
$registry->set('db', $db);

$loader->model('setting/setting');
$loader->model('design/layout');
$loader->model('extension/module/altapay');
$loader->model('user/user_group');
$modelUserGroup = new ModelUserUserGroup($registry);

// API settings
addSettingField($db, 'module_altapay', 'module_altapay_gateway_url', $url);
addSettingField($db, 'module_altapay', 'module_altapay_gateway_username', $apiUser);
addSettingField($db, 'module_altapay', 'module_altapay_gateway_password', $apiPass);
addSettingField($db, 'module_altapay', 'module_altapay_status', 1);
// Add currency field
$currencyId = addCurrencyField($db, "Danish Krone", "DKK", "DKK");
// Set currency
addSettingField($db, 'config', 'config_currency', 'DKK');
// Register module
addExtensionField($db, 'module', 'altapay');

// Create orders table
$db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "altapay_orders` ( `altapay_order_id` int(11) NOT NULL AUTO_INCREMENT, `order_id` int(11) NOT NULL, `created` DATETIME NOT NULL, `modified` DATETIME NOT NULL, `amount` DECIMAL( 10, 2 ) NOT NULL, `currency_code` CHAR(3) NOT NULL, `transaction_id` VARCHAR(24) NOT NULL, `capture_status` INT(1) DEFAULT NULL, `void_status` INT(1) DEFAULT NULL, `refund_status` INT(1) DEFAULT NULL, PRIMARY KEY (`altapay_order_id`) ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;");

try {
    $api = new Terminals(new Authentication($apiUser, $apiPass, $url));
    $response = $api->call();
    $i = 1;

    foreach ($response->Terminals as $key => $terminal) {
        if ($terminal->Country == 'DK') {
            $forbiddenChars = array("'", '"', '-');
            $termName = str_replace($forbiddenChars, "", $terminal->Title);
            preg_match_all("#([A-Z]+)#", str_replace(' ', '', ucwords($termName)), $output);
            $termKey = implode('', $output[1]);

            // Remove single and double quotes - avoid the placeholders replacement issue from templates
            // where single quotes are used for strings
            $termName = str_replace($forbiddenChars, "", $terminal->Title);
            preg_match_all("#([A-Z]+)#", str_replace(' ', '', ucwords($termName)), $output);
            $termKey = implode('', $output[1]);
            $termKeyWithUnderscore = str_replace(' ', '_', $termName);

            // Add settings for each terminal
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_title', $termName);
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_currency_id', $currencyId);
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_order_status_id', '15');
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_geo_zone_id', '0');
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_payment_action', 'authorize');
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_status', '1');
            addSettingField($db, 'payment_Altapay_' . $termKey, 'payment_Altapay_' . $termKey . '_sort_order', $i);
            addExtensionField($db, 'payment', 'Altapay_' . $termKey);

            // Check if file exists
            $dir = __DIR__;
            $tmpdir = sys_get_temp_dir();
            // ADMIN templates
            $path = $dir . '/admin/controller/extension/payment/Altapay_' . $termKey . '.php';

            if (file_exists($path)) {
                unlink($path);
            }

            $language_path = $dir . '/admin/language/en-gb/extension/payment/Altapay_' . $termKey . '.php';
            if (file_exists($language_path)) {
                unlink($language_path);
            }

            $view_template_path = $dir . '/admin/view/template/extension/payment/Altapay_' . $termKey . '.twig';
            if (file_exists($view_template_path)) {
                unlink($view_template_path);
            }

            $order_view_template_path = $dir . '/admin/view/template/extension/payment/Altapay_' . $termKey . '_order.twig';
            if (file_exists($order_view_template_path)) {
                unlink($order_view_template_path);
            }

            $payment_template = file_get_contents($dir . '/admin/controller/altapay/templates/admin/controller/altapay.twig.php');
            $payment_template_language = file_get_contents($dir . '/admin/controller/altapay/templates/admin/language/altapay.twig.php');
            $payment_template_view_template = file_get_contents($dir . '/admin/controller/altapay/templates/admin/view/altapay.twig');
            $payment_template_order_view_template = file_get_contents($dir . '/admin/controller/altapay/templates/admin/view/altapay_order.twig');

            // FRONTEND / catalog templates
            $frontend_model_path = $dir . '/catalog/model/extension/payment/Altapay_' . $termKey . '.php';
            if (file_exists($frontend_model_path)) {
                unlink($frontend_model_path);
            }

            $frontend_language_path = $dir . '/catalog/language/en-gb/extension/payment/Altapay_' . $termKey . '.php';
            if (file_exists($frontend_language_path)) {
                unlink($frontend_language_path);
            }

            $frontend_controller_path = $dir . '/catalog/controller/extension/payment/Altapay_' . $termKey . '.php';
            if (file_exists($frontend_controller_path)) {
                unlink($frontend_controller_path);
            }

            $frontend_view_path = $dir . '/catalog/view/theme/default/template/extension/payment/Altapay_' . $termKey . '.twig';
            if (file_exists($frontend_view_path)) {
                unlink($frontend_view_path);
            }

            $frontend_payment_model = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/model/altapay.twig.php');
            $frontend_template_language = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/language/altapay.twig.php');
            $frontend_template_controller = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/controller/altapay.twig.php');
            $frontend_template_view = file_get_contents($dir . '/admin/controller/altapay/templates/catalog/view/altapay_button.twig');

            // Replace patterns
            $content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $payment_template);
            $payment_dir = $dir . '/admin/controller/extension/payment';

            // Check if terminals folder is writable
            if ($payment_dir) {
                file_put_contents($path, $content);
                chmod($path, 0664);
            }

            $language_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $payment_template_language);
            $language_dir = $dir . '/admin/language/en-gb/extension/payment';

            // Check if language folder is writable
            if ($language_dir) {
                file_put_contents($language_path, $language_content);
                chmod($language_path, 0664);
            }

            $view_template_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $payment_template_view_template);
            $view_template_dir = $dir . '/admin/view/template/extension/payment';

            // Check if language folder is writable
            if (is_writable($view_template_dir)) {
                file_put_contents($view_template_path, $view_template_content);
                chmod($view_template_path, 0664);
            }

            $order_view_template_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $payment_template_order_view_template);
            $order_view_template_dir = $dir . '/admin/view/template/extension/payment';

            // Check if language folder is writable
            if (is_writable($order_view_template_dir)) {
                file_put_contents($order_view_template_path, $order_view_template_content);
                chmod($order_view_template_path, 0664);
            }

            // FRONTEND Templates
            $frontend_model_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $frontend_payment_model);
            $frontend_model_dir = $dir . '/catalog/model/extension/payment';
            // Check if language folder is writable
            if (is_writable($frontend_model_dir)) {
                file_put_contents($frontend_model_path, $frontend_model_content);
                chmod($frontend_model_path, 0664);
            }

            $frontend_language_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $frontend_template_language);
            $frontend_language_dir = $dir . '/catalog/language/en-gb/extension/payment';
            // Check if language folder is writable
            if (is_writable($frontend_language_dir)) {
                file_put_contents($frontend_language_path, $frontend_language_content);
                chmod($frontend_language_path, 0664);
            }

            $frontend_controller_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $frontend_template_controller);
            $frontend_controller_dir = $dir . '/catalog/controller/extension/payment';
            // Check if language folder is writable
            if (is_writable($frontend_controller_dir)) {
                file_put_contents($frontend_controller_path, $frontend_controller_content);
                chmod($frontend_controller_path, 0664);
            }

            $frontend_view_content = str_replace(array('{key}', '{_key_}', '{name}'), array($termKey, $termKeyWithUnderscore, $termName), $frontend_template_view);
            $frontend_view_dir = $dir . '/catalog/view/theme/default/template/extension/payment';
            // Check if language folder is writable
            if (is_writable($frontend_view_dir)) {
                file_put_contents($frontend_view_path, $frontend_view_content);
                chmod($frontend_view_path, 0664);
            }

            // Add Access/Modify permissions to the admin user group
            $modelUserGroup->addPermission(1, 'access', 'extension/payment/Altapay_' . $termKey);
            $modelUserGroup->addPermission(1, 'modify', 'extension/payment/Altapay_' . $termKey);

            $i++;
        }
    }

    // Add Access/Modify permissions to the admin user group
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/admin/controller/altapay.twig');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/admin/controller/altapay.twig');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/admin/language/altapay.twig');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/admin/language/altapay.twig');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/admin/view/altapay');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/admin/view/altapay');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/admin/view/altapay_order');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/admin/view/altapay_order');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/catalog/controller/altapay.twig');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/catalog/controller/altapay.twig');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/catalog/language/altapay.twig');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/catalog/language/altapay.twig');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/catalog/model/altapay.twig');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/catalog/model/altapay.twig');
    $modelUserGroup->addPermission(1, 'access', 'altapay/templates/catalog/view/altapay_button');
    $modelUserGroup->addPermission(1, 'modify', 'altapay/templates/catalog/view/altapay_button');
    $modelUserGroup->addPermission(1, 'access', 'extension/module/altapay');
    $modelUserGroup->addPermission(1, 'modify', 'extension/module/altapay');
    
} catch (ClientException $e) {
    echo "Error:" . $e->getMessage();
} catch (Exception $e) {
    echo "Error:" . $e->getMessage();
}

echo 'Settings are imported successfully';

/**
 * @param $db
 * @param $code
 * @param $key
 * @param $value
 */
function addSettingField($db, $code, $key, $value)
{
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` = '" . $key . "'");

    if ($query->num_rows) {
        $db->query("UPDATE " . DB_PREFIX . "setting SET `code` = '" . $code . "', `value` = '" . $value . "' WHERE `key` = '" . $key . "'");
    } else {
        $db->query("INSERT INTO  " . DB_PREFIX . "setting  (`store_id`,`code`,`key`, `value`,`serialized`) VALUES(0, '" . $code . "', '" . $key . "', '" . $value . "', 0)");
    }

}

/**
 * @param $db
 * @param $type
 * @param $code
 */
function addExtensionField($db, $type, $code)
{
    $query = $db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `code` = '" . $code . "'");
    if ($query->num_rows == 0) {
        $db->query("INSERT INTO  " . DB_PREFIX . "extension  (`type`,`code`) VALUES('" . $type . "', '" . $code . "')");
    }

}

/**
 * @param $db
 * @param $title
 * @param $code
 * @param $symbol
 * @return int
 */
function addCurrencyField($db, $title, $code, $symbol)
{
    $query = $db->query("SELECT `currency_id` FROM " . DB_PREFIX . "currency WHERE `code` = '" . $code . "'");
    if ($query->num_rows) {
        $db->query("UPDATE " . DB_PREFIX . "currency  SET `title` = '" . $title . "', `symbol_left` = '" . $symbol . "', `date_modified` = NOW() WHERE `code` = '" . $code . "'");
        return $query->row['currency_id'];
    } else {
        $db->query("INSERT INTO " . DB_PREFIX . "currency (`title`, `code`, `symbol_left`, `symbol_right`, `decimal_place`, `value`, `status`, `date_modified`) VALUES ('" . $title . "', '" . $code . "', '" . $symbol . "', '', '2', '1.00000000', '1', NOW())");
        return $db->getLastId();
    }
}
