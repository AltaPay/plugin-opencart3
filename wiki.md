# AltaPay OpenCart Plugin

AltaPay, headquartered in Denmark, is an internationally focused fintech company within payments with the mission to make payments less complicated. We help our merchants grow and expand their business across payment channels by offering a fully integrated seamless omni-channel experience for online, mobile and instore payments, creating transparency and reducing the need for manual tasks with one centralized payment platform.

AltaPay’s platform automizes, simplifies, and protects the transaction flow for shop owners and global retail and e-commerce companies, supporting and integrating smoothly into the major ERP systems. AltaPay performs as a Payment Service Provider operating under The Payment Card Industry Data Security Standard (PCI DSS).

OpenCart is an open-source eCommerce platform.

We offer a plugin that allows you to seamlessly integrate it into your AltaPay solution.

# OpenCart Payment plugin installation guide

Installing this plug-in will enable the web shop to handle card transactions through AltaPay's gateway.


**Table of Contents**

[Prerequisites](#prerequisites)

[Installation](#installation)

[Configuration](#configuration)
* [Configure fraud detection](#configure-fraud-detection)

* [Synchronize payment methods](#synchronize-payment-methods)

[Reconcile Orders](#reconcile-orders)

[Supported versions](#supported-versions)

[Troubleshooting](#troubleshooting)



# Prerequisites

We highly recommend gathering all the below information before starting the installation:

- AltaPay credentials for the payment gateway, terminals and custom gateway (i.e. yourShopName.altapaysecure.com). These will be provided by AltaPay.


# Installation

## Uploading the build package:
- From the admin panel, navigate to **'Extensions' > 'Installer'** and click on **"Upload"**.
- Choose the 'altapay.ocmod.zip' file.

 ![opencart_extension_upload](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_extension_upload.png)

- From the menu, go to **'Extensions' > 'Extensions'** and choose  **'Modules'** from the extension type dropdown.

 ![opencart_select_extension_module](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_select_extension_module.png)

- In the AltaPay line, click on Install (the green plus icon) button.

 ![opencart_module_install](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_module_install.png)

# Configuration

You can configure the plugin to meet your (the merchant's) needs, adding payment methods and configuring payments.

The standard configuration connects the plugin with the test gateway. Take the following steps to connect it with your custom payment gateway.

## Connect the plugin to the custom payment gateway

- From the menu, go to **'Extensions' > 'Extensions'** and choose  **'Modules'** from the extension type dropdown.

  ![opencart_select_extension_module](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_select_extension_module.png)

- In the AltaPay line, click on Edit.
- Fill in the credentials, change the **Gateway status** to **Enabled** and click on 'Save'.

 ![opencart_altapay_enable](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_altapay_enable.png)

- Click on **Refresh Terminals**

 ![opencart_altapay_refresh_terminals](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_altapay_refresh_terminals.png)


## Configure the terminals for the checkout page

- From the menu, go to **'Extensions' > 'Extensions'** and choose  **'Payments'** from the extension type dropdown.

  ![opencart_select_extension_payments](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_select_extension_payments.png)

- You'll see all AltaPay terminals here, click the install (the green plus icon) button for all required terminals.

  ![opencart_altapay_terminal_install](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_altapay_terminal_install.png)

- Next, you need to set up & enable each terminal. Click the Edit button and update the details, using the notes below and screenshot for guidance.

  ![opencart_altapay_terminal_enable](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_altapay_terminal_enable.png)

<table>
<tbody>
  <tr>
    <td><strong>Title</strong></td>
    <td>Assign a title to the terminal</td>
  </tr>
  <tr>
  <tr>
    <td><strong>Secret</strong></td>
    <td>Add the payment method secret as defined in the AltaPay payment gateway to enable checksum validation. To disable checksum validation leave it empty.</td>
  </tr>
  <tr>
  <tr>
    <td><strong>Custom Message</strong></td>
    <td>In this optional field, you can add custom message for the customers. e.g. guidelines from Danish Forbrugerombudsmanden.</td>
  </tr>
  <tr>
    <td><strong>Currency</strong></td>
    <td>Select a currency from the drop down list</td>
  </tr>
  <tr>
    <td><strong>Order Status</strong></td>
    <td>Set up the order status (for most payment methods it should be ‘Processing’)</td>
  </tr>
  <tr>
    <td><strong>Status</strong></td>
    <td>Change the status to ‘Enabled’</td>
  </tr>
</tbody>
</table>

- Save the changes.
- Now you are ready to process transactions through AltaPay.

 ![opencart_checkout_page](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_checkout_page.png)


> _Note: In the case of a new installation, remember to refresh the terminals (go to **‘Extensions’ > ‘Extensions’ > ‘Modules’ >** edit **‘AltaPay’** and click on **‘Refresh terminals’**)._
![opencart_altapay_refresh_terminals](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_altapay_refresh_terminals.png)


This will populate all the available terminals for the current API user.
If you face any issue click on **Refresh connection** button, this will fetch the terminals again and populate the list.

### Configure fraud detection

If you wish to enable fraud detection service and release/refund if fraud is detected then refer to the below screenshot.
![fraud_detection_service.png](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_fraud_detection_service.png)

### Synchronize payment methods

To synchronize the terminals with the gateway, click on the **Sync terminals** button. This will fetch the latest terminals from the gateway and will automatically configure based on the store country.

![synchronize_payment_methods](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/synchronize_payment_methods.png)



# Reconcile Orders
In order to reconcile payments please follow the steps below:

1. Navigate to the OpenCart admin **Dashboard** page.
2. Select **Sales** > **Orders** from the left menu.
3. Select the order you want to view.
4. Select the AltaPay terminal tabs from **Order History** section.
5. Copy the **Reconciliation Identifier** from the **Payment Information** section.

![opencart_order_view](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_order_view.jpg)

Or export the reconciliation data to CSV using the `Export Reconciliation Data` button on the Orders page.

![opencart_export_reconciliation_data](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_export_reconciliation_data.jpg)

![opencart_reconciliation_data](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_reconciliation_data.png)

6. Navigate to AltaPay Gateway dashboard.
7. Click on **FUNDING FILES** under **FINANCES** menu.
8. Download the CSV file.
9. Or you can find the payment in the transaction list, open the reconciliation file from there and download a csv file.
10. Open the downloaded CSV file and match the **Reconciliation Identifier** with OpenCart's **Reconciliation Identifier**.

**Sample AltaPay Gateway CSV:**

![funding_list_csv](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/funding_list_csv.png)


# Supported versions

Minimum system requirements are:
- OpenCart version 3.x
- PHP 5.6+

The latest tested version is:
- Opencart 3.0.3.7 with PHP 7.4


# Troubleshooting

### PHP Warning: Input variables exceeded 1000
For orders that contain many products, this PHP warning may be issued. In the file "php.ini" there is a setting called "max_input_vars" that need to have the limit increased (i.e. from 1000 to 3000). Once the changes are made a restart to the web server is required.

### Description/UnitPrice/Quantity is required for each orderline, but it was not set for line: xxxx
The same problem as above: the request has been truncated because the number of variables are exceeding the max_input_vars limit.

### Locating the merchant error message of a declined card
In the OpenCart backend, follow the instructions below:
- Go to ‘Sales’ > ‘Orders’
- Click on the ‘View button’ of the Failed/Declined order
- Scroll down to see the ‘Order History’ section
- The merchant error message is located in the ‘Comment’ column

![opencart_troubleshooting](https://github.com/AltaPay/plugin-opencart3/blob/main/docs/opencart_troubleshooting.png)


## Providing error logs to support team

**You can find the CMS logs by following the below steps:**

From Admin Dashboard navigate to **"System > Maintenance > Error Logs"**

**Web server error logs**

**For Apache server** You can find it on **/var/log/apache2/error.log**

**For Nginx** it would be **/var/log/nginx/error.log**

**_Note: Your path may vary from the mentioned above._**
