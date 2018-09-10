# Stockbase Magento 2 Module

For information and features please visit the [online documentation](https://stockbase-connect.github.io/magento2-module/)
or refer to the `docs` folder.

[![Build Status](https://travis-ci.org/Stockbase-Connect/magento2-module.svg?branch=master)](https://travis-ci.org/Stockbase-Connect/magento2-module)
[![Code Coverage](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Stockbase-Connect/magento2-module/?branch=master)
[![Packagist](https://img.shields.io/packagist/v/stockbase/magento2-module.svg)](https://packagist.org/packages/stockbase/magento2-module)
[![Packagist](https://img.shields.io/packagist/l/doctrine/orm.svg)](https://github.com/Stockbase-Connect/magento2-module/blob/master/LICENSE)

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

### Enable the Module

Execute the following commands in the Magento installation directory:

```
php bin/magento module:enable Stockbase_Integration
php bin/magento setup:upgrade
```

After successful installation, you can [configure](#configuration) the module via the admin panel.

## License
This module is licensed under the [MIT License](https://github.com/Stockbase-Connect/magento2-module/blob/master/LICENSE)

## Contributing
If you're interested in contributing please read our [Contributing Guidelines](https://github.com/Stockbase-Connect/magento2-module/blob/master/CONTRIBUTING.md)
