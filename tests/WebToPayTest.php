<?php

require_once 'WebToPay.php';

date_default_timezone_set('Europe/Vilnius');

require_once 'PHPUnit/Framework.php';
class WebToPayTest extends PHPUnit_Framework_TestCase {

    // Here you can set your real data to test
    public $projectid       = 2753;
    public $accepturl       = 'http://www.webtopay.com/?testwp_answer=accept';
    public $cancelurl       = 'http://www.webtopay.com/?testwp_answer=cancel';
    public $callbackurl     = 'http://www.webtopay.com/?testwp_answer=callback';
    public $sign_password   = '6b4dc5bf1d9a597282401edfe334c4f1';
    public $test            = 1;

    // Here you can put your callbackurls
    public $callbacks = array(
            // Makro test callback url
            'http://www.webtopay.com/?testwp_answer=callback&wp_projectid=2753&wp_orderid=1&wp_lang=lit&wp_amount=10000&wp_currency=LTL&wp_country=LT&wp_paytext=U%C5%BE+prekes+ir+paslaugas+%28u%C5%BE+nr.+1%29+%28Mantas+Zimnickas%29+%282753%29&wp_test=1&wp_version=1.3&wp_p_email=mantas%40evp.lt&wp_type=EMA&wp_payment=maximalt&wp__ss1=3205e6116157a2fdfc9bec349654ecb1&wp_status=1&wp_name=&wp_surename=&wp_payamount=10000&wp_paycurrency=LTL&wp__ss2=lnLGC95Zh4tJGMt2xVYLXf0Khp2esrwLlWh3RxVz19h2kkKzc9XLwJY%2Ftw4aAtdJJFjt1NstZEXJ6Pbr8uHR5FUntwLneYNdXmgoUIa7xEVLrLE3V9HRfhoOGp6kfKIQMBFYlxii2TsUhc9d2o0iBbU4N2Ots8Znwyi8OOxl0Dw%3D',

            // Mikro test callback url
            'http://www.webtopay.com/?testwp_answer=callback&wp_test=0&wp_country=LT&wp__ss1=8cb1c109876b6e1bddc6b5f6e00c254e&wp_amount=50&wp_currency=LTL&wp_id=5722546&wp_key=TESTWEBAN&wp_sms=testweban&wp_from=%2B37067011122&wp_to=1679&wp_provider=test&wp_operator=test&wp_version=1.3&wp__ss2=C8IEb%2BC%2F2gLtptXqJPNM15Eno0Qd8wma7kGbe6i1YGTeDOF1GIaF7tHqeiZo7ic3RvWSamXrJelyZGrOeql9xs3tWYGUR0xi56IqCTBuaCkOzySxBR3CxoD5viSkocsuA3PuQKvVM4fcQbvImPNUSSJ0veoyUb5cmDxx55GH7pY%3D',
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

    public function testRepeatRequest() {
        $request = WebToPay::buildRepeatRequest(array(
                'projectid'     => $this->projectid,
                'sign_password' => $this->sign_password,
                'orderid'       => 1,
                'foo'           => 'bar',
            ));
        $this->assertEquals('1', $request['repeat_request']);
        $this->assertEquals(32, strlen($request['sign']));
        $this->assertEquals(5, sizeof($request));

        try {
            WebToPay::buildRepeatRequest(array(
                    'projectid'     => $this->projectid,
                    'orderid'       => 1,
                ));
            $this->fail('WebToPayException expected.');
        }
        catch (WebToPayException $e) {
            // pass
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
                $this->assertEquals($this->projectid, $_data['projectid']);

                WebToPay::toggleSS2(false);
                $_data = WebToPay::checkResponse($data, array(
                        'projectid'     => $this->projectid,
                        'sign_password' => $this->sign_password,
                    ));
                $this->assertEquals($this->projectid, $_data['projectid']);

                WebToPay::toggleSS2(true);
                $_data = WebToPay::checkResponse($data, array(
                        'projectid'     => $this->projectid,
                        'sign_password' => $this->sign_password,
                        'orderid'       => $data[WebToPay::PREFIX.'orderid'],
                        'amount'        => $data[WebToPay::PREFIX.'amount'],
                        'currency'      => $data[WebToPay::PREFIX.'currency'],
                    ));
                $this->assertEquals($this->projectid, $_data['projectid']);
            }

            // Mikro
            else {
                WebToPay::toggleSS2(true);
                $_data = WebToPay::checkResponse($data, array(
                        'sign_password' => $this->sign_password,
                    ));
                $this->assertEquals($data[WebToPay::PREFIX.'sms'], $_data['sms']);

                WebToPay::toggleSS2(false);
                $_data = WebToPay::checkResponse($data, array(
                        'sign_password' => $this->sign_password,
                    ));
                $this->assertEquals($data[WebToPay::PREFIX.'sms'], $_data['sms']);
            }

        }
    }

}

