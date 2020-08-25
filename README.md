WebToPay
========

This is WebToPay library package for the checkout functionality. 
More information can be found in documentation: https://developers.paysera.com/en/checkout/basic

Requirements
============

You must have PHP version 5.0 or higher


Installation
============

Easiest way to use library is to include merged all-in-one file.
It is located in base libwebtopay directory, "WebToPay.php".
In this case you only need this one file.
Example:
```php
    <?php

    require_once('WebToPay.php');

    // Your code goes here
    
    ?>
```
Alternatively, you can use files in the "src" folder.
Either set-up autoloader or include file "includes.php" in "src" directory.
Example:
```php
    <?php

    require_once('libwebtopay/src/includes.php');

    // Your code goes here
    
    ?>
```
Testing
=======

    $ cd /path/to/libwebtopay
    $ phpunit


Demo
===============

demo_shop is a simple example how you can integrate library to your project.
It also shows how to get payment methods available for your project and specific amount.

Demo needs write permissions to folder /var to function properly.
If you want to test demo online, also change parameters in includes/config.php file to your project's.
If you are testing offline, demos will still work, but webtopay.com will be unable to send callback to your site - 
you can login to your account and copy-and-paste the callback link in your browser in that case.
