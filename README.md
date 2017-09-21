# Stockbase Magento 2 Module

For information and features please visit the [online documentation](https://stockbase-connect.github.io/magento2-module/)
or refer to the `_book` folder.

[![Build Status](https://travis-ci.org/Stockbase-Connect/magento2-module.svg?branch=master)](https://travis-ci.org/Stockbase-Connect/magento2-module)
[![Code Coverage](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/?branch=master)

## Installation

### Requirements

* Magento 2.
* A running Magento cron ([official how-to](http://devdocs.magento.com/guides/v2.0/config-guide/cli/config-cli-subcommands-cron.html)).
* A Stockbase username and password with suppliers connected to your account.


### Install with Composer

This is the recommended way to install the module for Magento 2. This method allows you to automatically download the
module and all its dependencies with a single console command.

1. Make sure you have [Composer](https://getcomposer.org/) installed.
2. Navigate to your Magento 2 installation directory (where your `composer.json` and `composer.lock` files are located).
3. Execute the command: `composer require stockbase/magento2-module`

### Install Manually

**Warning!** This installation is method recommended for module development only and we assume that you know how to
work with PHP and Composer. If you're not sure whether you need this type of installation, please follow the
instructions for [Composer installation](#2-2) in Section 1.2.1.

1. Download the zip archive containing the module.
2. Extract it into the `<magento root folder>/app/code/Stockbase/Integration` directory.
3. Install into your main Magento project all dependencies listed in this module's `composer.json` file.
   For example: `composer require dividebv/phpdivideiq:^0.5.0 beberlei/assert:^2.4`.
   See the `require` section of the module's `composer.json` file.

### Enable the Module

Execute the following commands in the Magento installation directory:

```
php bin/magento module:enable Stockbase_Integration
php bin/magento setup:upgrade
```

After successful installation, you can [configure](#configuration) the module via the admin panel.
