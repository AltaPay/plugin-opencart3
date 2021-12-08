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

[Supported versions](#supported-versions)

[Troubleshooting](#troubleshooting)



# Prerequisites

We highly recommend gathering all the below information before starting the installation:

- AltaPay credentials for the payment gateway, terminals and custom gateway (i.e. yourShopName.altapaysecure.com). These will be provided by AltaPay.


# Installation

## Uploading the build package:
- From the admin panel, navigate to **'Extensions' > 'Installer'** and click on **"Upload"**.
- Choose the 'altapay.ocmod.zip' file.
 ![opencart_extension_upload](docs/opencart_extension_upload.png)
- From the menu, go to **'Extensions' > 'Extensions'** and choose  **'Modules'** from the extension type dropdown.
 ![opencart_select_extension_module](docs/opencart_select_extension_module.png)
- In the AltaPay line, click on Install (the green plus icon) button.
 ![opencart_module_install](docs/opencart_module_install.png)

# Configuration

You can configure the plugin to meet your (the merchant's) needs, adding payment methods and configuring payments.

The standard configuration connects the plugin with the test gateway. Take the following steps to connect it with your custom payment gateway.

## Connect the plugin to the custom payment gateway

- From the menu, go to **'Extensions' > 'Extensions'** and choose  **'Modules'** from the extension type dropdown.
  ![opencart_select_extension_module](docs/opencart_select_extension_module.png)
- In the AltaPay line, click on Edit.
- Fill in the credentials, change the **Gateway status** to **Enabled** and click on 'Save'.
 ![opencart_altapay_enable](docs/opencart_altapay_enable.png)
- Click on **Refresh Terminals**
 ![opencart_altapay_refresh_terminals](docs/opencart_altapay_refresh_terminals.png)


## Configure the terminals for the checkout page

- From the menu, go to **'Extensions' > 'Extensions'** and choose  **'Payments'** from the extension type dropdown.
  ![opencart_select_extension_payments](docs/opencart_select_extension_payments.png)
- You'll see all AltaPay terminals here, click the install (the green plus icon) button for all required terminals.
  ![opencart_altapay_terminal_install](docs/opencart_altapay_terminal_install.png)
- Next, you need to set up & enable each terminal. Click the Edit button and update the details, using the notes below and screenshot for guidance.
  ![opencart_altapay_terminal_enable](docs/opencart_altapay_terminal_enable.png)

<table>
<tbody>
  <tr>
    <td><strong>Title</strong></td>
    <td>Assign a title to the terminal</td>
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
 ![opencart_checkout_page](docs/opencart_checkout_page.png)


> _Note: In the case of a new installation, remember to refresh the terminals (go to **‘Extensions’ > ‘Extensions’ > ‘Modules’ >** edit **‘AltaPay’** and click on **‘Refresh terminals’**)._
![opencart_altapay_refresh_terminals](docs/opencart_altapay_refresh_terminals.png)


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

![opencart_troubleshooting](docs/opencart_troubleshooting.png)


## Providing error logs to support team

**You can find the CMS logs by following the below steps:**

From Admin Dashboard navigate to **"System > Maintenance > Error Logs"**

**Web server error logs**

**For Apache server** You can find it on **/var/log/apache2/error.log**

**For Nginx** it would be **/var/log/nginx/error.log**

**_Note: Your path may vary from the mentioned above._**