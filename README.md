Duell opencart-2x module
=====================

The purpose of this module is to manage & sync stocks at both opencart 2.x webshop and Duell. 

Prerequisites
-------------

This module requires opencart 2.x.

* If you want to use this module with opencart 1.x version, please use [1.x](https://github.com/Kasseservice/opencart-1x).
* If you want to use this module with opencart 3.x version, please use [3.x](https://github.com/Kasseservice/opencart-3x).

Installation
------------

### Step 1: Download the Module

Download the module files.

### Step 2: Upload the Module

* Please take backup of catalog > model > checkout > order.php 
  If you don't want to replace file then add below code to above mentioned file.
  
  Find below function 
  ```php
  public function addOrderHistory
  ```
  
  Find below line 
  ```php
  if (!$order_info['order_status_id'] && $order_status_id) {
  ```
  
  Add below code at the end of **if** condition. Make sure inside the code is inside above mentioned **if** condition
  ```php
  /*
  * Duell Stock sync
  */
  $this->load->library('duell/duell');
  $result = $this->duell->callDuellStockUpdate($order_product_query->rows);
  ```
  
* Upload code to the opencart root directory.


### Step 3: Give access to module

* Take login into admin area. 
* Goto System > Users -> User Groups > Edit "Administrator" user group
* Check checkbox in Access Permission
  ```php
  extension/module/duell_integration 
  ```
* Check checkbox in Modify Permission
  ```php
  extension/module/duell_integration  
  ```
* Save group.

### Step 4: Enable the Module

* Goto > Extensions > Extensions > Choose extension type "Modules"
* Find "Duell Integration" 
* Click on "+" icon to activate the module.

### Step 5: Setup duell credential

**Note:** Make sure you have API related access in Duell application. Find the below details in duell manager area > API-oppsett 

* **Client Number:** Required for API authentication
* **Client Token:** Required for API authentication
* **Department Token:** Copy the department token in which stock need to manage.
* **Log Enable:** In case of enable, it will show all API call logs in opencart logs.
* **Status:** Stock only manage if this flag is enabled

### Step 6: Setup cron job with CURL

* Every 3 hours

  ```bash
  * */3 * * * /usr/bin/curl http://<YOURWEBSHOP.COM>/system/duellcron.php >/dev/null 2>&1
  ```
* Every night 3am

  ```bash
  * 3 * * * /usr/bin/curl http://<YOURWEBSHOP.COM>/system/duellcron.php >/dev/null 2>&1
  ```
  
  
**Note:** Make sure .htaccess file inside system folder have below code.

  Find below code
  ```
  <Files *.*>
    Order Deny,Allow
    Deny from all
  </Files>
  ```
  Add below code 

  ```
  <Files "duellcron.php">
      Allow from all
  </Files>
  ```

 
LICENSE
-------

The module is open source, and made available via an 'GNU GENERAL PUBLIC LICENSE', which basically means you can do whatever you like with it as long as you retain the copyright notice and license description - see [LICENSE](../master/LICENSE) for more information.

