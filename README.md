# Stockbase Magento 2 Module

This module allows to extend the default Magento stock with products available on the Stockbase stock.
When you don't have enough product amount available on your Magento stock, this module allows to order missing items from
the Stockbase to satisfy customer's needs.


## Installation

### 1. Download the module

**Using the Composer** (preferred way):

Execute the following command in the Magento installation directory:  
```
composer require stockbase/magento2-integration
```  

**Or manually** (recommended only for development):

1. Download the zip archive with the extension.
2. Extract it into the `<magento root folder>/app/code/Stockbase/Integration` directory inside your Magento installation.
   If you don't have such folder, create one.
3. Install all required dependencies from the extension's `composer.json` file into your main magento project.
   For example: `composer require dividebv/phpdivideiq:^0.5.0 beberlei/assert:^2.4`.
   See the `require` section in the module's `composer.json` file.


### 2. Install the module

Execute the following commands in the Magento installation directory:  
```
php bin/magento module:enable Stockbase_Integration
php bin/magento setup:upgrade
```

### 3. Configure

Prerequisites:

* You should have a Stockbase username and password with suppliers connected to the Stockbase account.
* The Magento cron set up and running.
* You have to have a product attribute that contains a valid EAN for products that you're planning to integrate with
  Stockbase. If you don't have one, just create it and fill it in for every product you wish to integrate.


To property configure the module, you should:

1. Navigate to your Magento admin panel and open the stores configuration (**Stores** -> **Configuration**).
2. Select the **Stockbase** -> **Configuration** section and properly fill in the configuration options.
3. Edit each product you want to integrate with Stockbase:
    1. Enable the **Stockbase product** option (only simple products are allowed).
    2. Fill in the EAN attribute that you selected during the configuration.




