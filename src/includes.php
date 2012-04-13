<?php

if (!class_exists('WebToPay')) {
    include(dirname(__FILE__) . '/WebToPay.php');
    include(dirname(__FILE__) . '/WebToPayException.php');
    include(dirname(__FILE__) . '/WebToPay/Exception/Callback.php');
    include(dirname(__FILE__) . '/WebToPay/Exception/Configuration.php');
    include(dirname(__FILE__) . '/WebToPay/Exception/Validation.php');
    include(dirname(__FILE__) . '/WebToPay/Sign/SignCheckerInterface.php');
    include(dirname(__FILE__) . '/WebToPay/Sign/SS1SignChecker.php');
    include(dirname(__FILE__) . '/WebToPay/Sign/SS2SignChecker.php');
    include(dirname(__FILE__) . '/WebToPay/CallbackValidator.php');
    include(dirname(__FILE__) . '/WebToPay/Factory.php');
    include(dirname(__FILE__) . '/WebToPay/PaymentMethod.php');
    include(dirname(__FILE__) . '/WebToPay/PaymentMethodCountry.php');
    include(dirname(__FILE__) . '/WebToPay/PaymentMethodGroup.php');
    include(dirname(__FILE__) . '/WebToPay/PaymentMethodList.php');
    include(dirname(__FILE__) . '/WebToPay/PaymentMethodListProvider.php');
    include(dirname(__FILE__) . '/WebToPay/RequestBuilder.php');
    include(dirname(__FILE__) . '/WebToPay/SmsAnswerSender.php');
    include(dirname(__FILE__) . '/WebToPay/Util.php');
    include(dirname(__FILE__) . '/WebToPay/WebClient.php');
}