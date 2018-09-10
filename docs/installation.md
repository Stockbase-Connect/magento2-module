# Installation

## Requirements

* Magento 2.
* A running Magento cron ([official how-to](http://devdocs.magento.com/guides/v2.0/config-guide/cli/config-cli-subcommands-cron.html)).
* A Stockbase username and password with suppliers connected to your account.


## Install with Composer

This is the recommended way to install the module for Magento 2. This method allows you to automatically download the 
module and all its dependencies with a single console command.

1. Make sure you have [Composer](https://getcomposer.org/) installed.
2. Navigate to your Magento 2 installation directory (where the `composer.json` and `composer.lock` files are located).
3. Execute the command: `composer require stockbase/magento2-module`

## Enable the Module

Execute the following commands in the Magento installation directory:  

```
php bin/magento module:enable Stockbase_Integration
php bin/magento setup:upgrade
``` 

After successful installation, you can [configure](#configuration) the module via the admin panel.
