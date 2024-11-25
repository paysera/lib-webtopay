lib-webtopay
========

The Checkout API (Payment Gateway API) allows for the collection of online payments with many payment methods. 
The Checkout API is easy to integrate – simply use one of our methods and the checkout processes will be performed 
automatically. The library can be used to check all the necessary security parameters of transferred and received data.
More information can be found in [the documentation](https://developers.paysera.com/en/checkout/basic).

Installation
============

Easiest way to use library is to include merged all-in-one file.
It is located in base libwebtopay directory, "WebToPay.php".
In this case you only need this one file.
Example:
```php
<?php

require_once('WebToPay.php');
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
// to install the latest version
"composer require webtopay/libwebtopay

// to install the oldest supported version (in some projects)
"composer require webtopay/libwebtopay "^1.6"

// to install version with new interface
"composer require webtopay/libwebtopay "^2.0"
```

And then:
```php
<?php

use WebToPay;
```

Pay attention that the ^3.0 version has the same interface as the ^2.0 but newest one is brought up to 
PHP 7.4 standards  and code style like `strict_types`, type hints etc

Testing
=======

    $ bash run_tests.sh
