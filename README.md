lib-webtopay
========

The Checkout API (Payment Gateway API) allows for the collection of online payments with many payment methods. 
The Checkout API is easy to integrate – simply use one of our methods and the checkout processes will be performed 
automatically. The library can be used to check all the necessary security parameters of transferred and received data.
More information can be found in [the documentation](https://developers.paysera.com/en/checkout/basic).

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
```
Alternatively, you can use files in the "src" folder.
Either set-up autoloader or include file "includes.php" in "src" directory.
Example:
```php
<?php

require_once('libwebtopay/src/includes.php');

// Your code goes here
```
Another way to install library is using composer:
```
"composer require webtopay/libwebtopay "^1.6"
```

Testing
=======

    $ phpunit


Demo
===============

demo_shop is a simple example how you can integrate library to your project.
It also shows how to get payment methods available for your project and specific amount.

Demo needs write permissions to folder /var to function properly.
If you want to test demo online, also change parameters in includes/config.php file to your project's.
If you are testing offline, demos will still work, but webtopay.com will be unable to send callback to your site - 
you can login to your account and copy-and-paste the callback link in your browser in that case.
