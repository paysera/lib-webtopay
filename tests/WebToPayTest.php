<?php

require_once 'WebToPay.php';

require_once 'PHPUnit/Framework.php';
class WebToPayTest extends PHPUnit_Framework_TestCase {

    // Here you can set your real data to test
    public $merchantid      = 1;
    public $callbackurl     = '/callback';


    public function testRequest() {
        $form_data = WebToPay::buildRequest(array(
                'merchantid'    => $this->merchantid,
                'orderid'       => 1,
                'accepturl'     => '/accept',
                'cancelurl'     => '/cancel',
                'callbackurl'   => $this->callbackurl,
                'sign_password' => '123456789',
            ));
    }

    public function testRequestException() {
        try {
            WebToPay::buildRequest(array());
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            $this->assertEquals(WebToPayException::E_REQ_MISSING, $e->getCode());
        }

        try {
            WebToPay::buildRequest(array(
                    'merchantid'    => str_repeat('32', '9'),
                ));
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            $this->assertEquals(WebToPayException::E_REQ_INVALID, $e->getCode());
        }

        try {
            WebToPay::buildRequest(array(
                    'merchantid'    => $this->merchantid,
                    'orderid'       => 1,
                    'accepturl'     => '/accept',
                    'cancelurl'     => '/cancel',
                    'callbackurl'   => $this->callbackurl,
                    'sign_password' => '123456789',
                    'test'          => 'test',
                ));
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            $this->assertEquals(WebToPayException::E_REQ_INVALID, $e->getCode());
        }
    }

    public function testResponse() {
        $response = array(
                'merchantid'    => $this->merchantid,
                'orderid'       => 1,
                'amount'        => 10,
                'currency'      => 'LTL',
                '_ss2'           => '0564856786nitaurar',
            );
        WebToPay::checkResponse($response, array(
                'merchantid'    => $this->merchantid,
                'orderid'       => 1,
                'amount'        => 10,
                'currency'      => 'LTL',
            ));
    }

    public function testPaymentTypes() {
        $types = WebToPay::getPaymentTypes();
        foreach ($types as $type) {
            $this->assertEquals(5, sizeof($type));

            list(
                    $country_code, $payment_code,
                    $min_amount, $max_amount, $describtion
                ) = $type;

            if ('' != $country_code) {
                $this->assertEquals(2, strlen($country_code));
            }
            $this->assertTrue(is_int($min_amount));
            $this->assertTrue(is_int($max_amount));
        }
    }


    public function testRequestSpec() {
        $specs = WebToPay::getRequestSpec();
        foreach ($specs as $spec) {
            $this->assertEquals(6, sizeof($spec));

            list(
                    $name, $maxlen, $required, $user, $isrequest, $regexp
                ) = $spec;

            $this->assertTrue(is_int($maxlen));
            $this->assertTrue(is_bool($required));
            $this->assertTrue(is_bool($user));
            $this->assertTrue(is_bool($isrequest));
        }
    }

}

