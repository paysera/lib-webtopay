<?php

require_once 'WebToPay.php';

date_default_timezone_set('Europe/Vilnius');

require_once 'PHPUnit/Framework.php';
class WebToPayTest extends PHPUnit_Framework_TestCase {

    // Here you can set your real data to test
    public $projectid      = 1;
    public $callbackurl     = '/callback';


    public function testRequest() {
        $form_data = WebToPay::buildRequest(array(
                'projectid'    => $this->projectid,
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
            $this->assertEquals(WebToPayException::E_MISSING, $e->getCode());
        }

        try {
            WebToPay::buildRequest(array(
                    'projectid'    => str_repeat('32', '9'),
                ));
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            $this->assertEquals(WebToPayException::E_MAXLEN, $e->getCode());
        }

        try {
            WebToPay::buildRequest(array(
                    'projectid'    => $this->projectid,
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
            $this->assertEquals(WebToPayException::E_MAXLEN, $e->getCode());
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


    public function testSmsAnswer() {
        try {
            WebToPay::smsAnswer(array(
                    'id'            => 0,
                    'msg'           => 'msg',
                    'sign_password' => 'secret',
                ));
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            $this->assertEquals('Error: Wrong id', $e->getMessage());
        }
    }

}

