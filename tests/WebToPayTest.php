<?php

require_once 'WebToPay.php';

date_default_timezone_set('Europe/Vilnius');

require_once 'PHPUnit/Framework.php';
class WebToPayTest extends PHPUnit_Framework_TestCase {

    // Here you can set your real data to test
    public $projectid       = 0;
    public $accepturl       = 'http://myhost/webtopay/accept/';
    public $cancelurl       = 'http://myhost/webtopay/cancel/';
    public $callbackurl     = 'http://myhost/webtopay/callback/';
    public $sign_password   = 'd41d8cd98f00b204e9800998ecf8427e';
    public $test            = 1;

    // Here you can put callbackurls from webtopay.com
    public $callbacks = array(
        );


    public function testRequest() {
        $form_data = WebToPay::buildRequest(array(
                'projectid'     => $this->projectid,
                'orderid'       => 1,
                'accepturl'     => $this->accepturl,
                'cancelurl'     => $this->cancelurl,
                'callbackurl'   => $this->callbackurl,
                'sign_password' => $this->sign_password,
                'test'          => $this->test,
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
                    'projectid'     => $this->projectid,
                    'orderid'       => 1,
                    'accepturl'     => $this->accepturl,
                    'cancelurl'     => $this->cancelurl,
                    'callbackurl'   => $this->callbackurl,
                    'sign_password' => $this->sign_password,
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
                    'sign_password' => $this->sign_password,
                ));
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            $this->assertEquals('Error: Wrong id', $e->getMessage());
        }
    }

    public function testCallbacks() {
        foreach ($this->callbacks as $callback) {
            $callback = parse_url($callback);
            $callback = explode('&', $callback['query']);
            $data = array();
            foreach ($callback as $item) {
                list($key, $val) = explode('=', $item, 2);
                $data[urldecode($key)] = urldecode($val);
            }

            // Makro
            if (isset($data[WebToPay::PREFIX.'projectid'])) {
                $this->assertEquals($data[WebToPay::PREFIX.'projectid'], $this->projectid);

                WebToPay::toggleSS2(true);
                $_data = WebToPay::checkResponse($data, array(
                        'projectid'     => $this->projectid,
                        'sign_password' => $this->sign_password,
                    ));

                WebToPay::toggleSS2(false);
                $_data = WebToPay::checkResponse($data, array(
                        'projectid'     => $this->projectid,
                        'sign_password' => $this->sign_password,
                    ));

                WebToPay::toggleSS2(true);
                $_data = WebToPay::checkResponse($data, array(
                        'projectid'     => $this->projectid,
                        'sign_password' => $this->sign_password,
                        'orderid'       => $data[WebToPay::PREFIX.'orderid'],
                        'amount'        => $data[WebToPay::PREFIX.'amount'],
                        'currency'      => $data[WebToPay::PREFIX.'currency'],
                    ));
            }

            // Mikro
            else {
                WebToPay::toggleSS2(true);
                $_data = WebToPay::checkResponse($data, array(
                        'sign_password' => $this->sign_password,
                    ));

                WebToPay::toggleSS2(false);
                $_data = WebToPay::checkResponse($data, array(
                        'sign_password' => $this->sign_password,
                    ));
            }

        }
    }

}

