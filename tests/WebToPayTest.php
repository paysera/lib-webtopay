<?php

require_once 'WebToPay.php';

date_default_timezone_set('Europe/Vilnius');

require_once 'PHPUnit/Framework.php';
class WebToPayTest extends PHPUnit_Framework_TestCase {

    // Here you can set your real data to test
    public $projectid        = 13156;
    public $accepturl        = 'http://www.webtopay.com/?testwp_answer=accept';
    public $cancelurl        = 'http://www.webtopay.com/?testwp_answer=cancel';
    public $callbackurl      = 'http://www.webtopay.com/?testwp_answer=callback';
    public $sign_password    = '1c4196d0ff7fe4e94bdca98fb251bc25';
    public $test             = 1;
    public $paymentCurrency  = 'LTL';
    public $paymentAmount    = 345;
    public $lang             = 'en'; //e-shop language

    public $currencyArray    = array (
            '0' => array (
                    'iso'   => USD,
                    'rate'  => 0.417391,
                ),
            '1' => array (
                    'iso'   => EUR,
                    'rate'  => 0.289855,
                ),
            '2' => array (
                    'iso'   => LTL,
                    'rate'  => 1.000000,
                ),
            '3' => array (
                    'iso'   => GBP,
                    'rate'  => 0.252174,
                ),
        );

    public $XML = '<?xml version="1.0" encoding="UTF-8"?>
                    <payment_types_document project_id="2753">
                        <country code="de">
                            <payment_group key="e-banking">
                                <title language="lt">Elektroninė bankininkystė</title>
                                <title language="en">Electronic banking</title>
                                <title language="ru">Эл. банковские системы</title>
                                <payment_type key="directeb">
                                    <logo_url language="lt">https://www.mokejimai.lt/new/img/lt/banks/directeb.gif</logo_url>
                                    <title language="lt">lt_directeb</title>
                                    <logo_url language="en">https://www.mokejimai.lt/new/img/en/banks/directeb.gif</logo_url>
                                    <title language="en">en_directeb</title>
                                    <logo_url language="ru">https://www.mokejimai.lt/new/img/ru/banks/directeb.gif</logo_url>
                                    <title language="ru">ru_directeb</title>
                                    <min amount="345" currency="LTL"/>
                                </payment_type>
                            </payment_group>
                        </country>
                        <country code="ee">
                            <payment_group key="e-banking">
                                <title language="lt">Elektroninė bankininkystė</title>
                                <title language="en">Electronic banking</title>
                                <title language="ru">Эл. банковские системы</title>
                                <payment_type key="hanza">
                                    <logo_url language="lt">https://www.mokejimai.lt/new/img/lt/banks/hanza.gif</logo_url>
                                    <title language="lt">lt_hanza</title>
                                    <logo_url language="en">https://www.mokejimai.lt/new/img/en/banks/hanza.gif</logo_url>
                                    <title language="en">en_hanza</title>
                                    <logo_url language="ru">https://www.mokejimai.lt/new/img/ru/banks/hanza.gif</logo_url>
                                    <title language="ru">ru_hanza</title>
                                    <max amount="200000" currency="LTL"/>
                                </payment_type>
                            </payment_group>
                        </country>
                    </payment_types_document>';

    public $parameterArray = array (
            'de' => array (
                    'e-banking' => array (
                            'translate' => array (
                                    'lt' => 'Elektroninė bankininkystė',
                                    'en' => 'Electronic banking',
                                    'ru' => 'Эл. банковские системы',
                                ),
                            'directeb' => array (
                                    'logo' => array (
                                            'lt' => 'https://www.mokejimai.lt/new/img/lt/banks/directeb.gif',
                                            'en' => 'https://www.mokejimai.lt/new/img/en/banks/directeb.gif',
                                            'ru' => 'https://www.mokejimai.lt/new/img/ru/banks/directeb.gif',
                                        ),
                                    'title' => array (
                                            'lt' => 'lt_directeb',
                                            'en' => 'en_directeb',
                                            'ru' => 'ru_directeb',
                                        ),
                                    'amount' => array (
                                            'min_amount' => 345,
                                            'min_amount_currency' => 'LTL',
                                        ),
                                ),
                        ),
                ),
           'ee' => array (
                    'e-banking' => array (
                            'translate' => array (
                                    'lt' => 'Elektroninė bankininkystė',
                                    'en' => 'Electronic banking',
                                    'ru' => 'Эл. банковские системы',
                                ),
                            'hanza' => array (
                                    'logo' => array (
                                            'lt' => 'https://www.mokejimai.lt/new/img/lt/banks/hanza.gif',
                                            'en' => 'https://www.mokejimai.lt/new/img/en/banks/hanza.gif',
                                            'ru' => 'https://www.mokejimai.lt/new/img/ru/banks/hanza.gif',
                                        ),
                                    'title' => array (
                                            'lt' => 'lt_hanza',
                                            'en' => 'en_hanza',
                                            'ru' => 'ru_hanza',
                                        ),
                                    'amount' => array (
                                            'max_amount' => 200000,
                                            'max_amount_currency' => 'LTL',
                                        ),
                                ),
                        ),
                ),
        );

    //kai $lang = 'en' arba $lang = null, $amount >= 345 ar $amount <= 200000
    public $filteredArray = array (
            'de' => array (
                    'Electronic banking' => array (
                            'en_directeb' => array (
                                    'name' => 'directeb',
                                    'logo' => 'https://www.mokejimai.lt/new/img/en/banks/directeb.gif',
                                ),
                        ),
                ),
            'ee' => array (
                    'Electronic banking' => array (
                            'en_hanza' => array (
                                    'name' => 'hanza',
                                    'logo' => 'https://www.mokejimai.lt/new/img/en/banks/hanza.gif',
                                ),
                        ),
                ),
        );

    // Here you can put your callbackurls
    public $callbacks = array(
        // Makro test callback url
        'http://www.webtopay.com/?testwp_answer=callback&wp_projectid=13156&wp_orderid=1&wp_lang=lit&wp_amount=10000&wp_currency=LTL&wp_payment=maximalt&wp_country=LT&wp_p_firstname=Vardenis&wp_p_lastname=Pavardenis&wp_p_email=m.sprunskas%40evp.lt&wp_p_street=M%C4%97nulio+g.7&wp_p_city=Vilnius&wp_test=1&wp_version=1.4&wp_type=EMA&wp_paytext=U%C5%BEsakymas+nr%3A+1+http%3A%2F%2Ftest-project.local+projekte.+%28Pardav%C4%97jas%3A+Libwebtopay+Libwebtopay%29+%2813156%29&wp_receiverid=168328&wp__ss1=c72cffd0345f55fef6595a86e5c7caa6&wp_status=1&wp_requestid=16309376&wp_name=&wp_surename=&wp_payamount=10000&wp_paycurrency=LTL&wp__ss2=oSiHSlnin%2FSSJ7bGaTWZybtHzA6%2FNaZcPtS3f07KZMoTeJteL6rnuw7qfT%2FACGW5Hifu2ieNnCBpu2XLnsR10Ja8%2FxVM5X7j2mg9wBOO1Y0cefKBSBlFoZjLL2ciV32ETCD4Okxv2l%2FwH8tQhDQnJ6AOJkbh2ayKy8yTXOcE1zk%3D',

        // Mikro test callback url
        'http://www.webtopay.com/?wp_test=0&wp_country=LT&wp__ss1=a888c6ed50e1e5ab7d732eac91357efe&wp_amount=50&wp_currency=LTL&wp_id=9622828&wp_key=WTP14&wp_sms=WTP14&wp_from=%2B37000000000&wp_to=1679&wp_provider=test&wp_operator=test&wp_version=1.4&wp__ss2=Trsyyba5%2FyovvnSsrt%2FM290zy3bxmWGueMmlairO2HGAgsNb9CV%2FFoFQLpzKLZWUfHWxMjB9XHxt%2BvPaDMABx1pR975ASrvaWGp7StsgjRp01mIYHJP0k9gkvqtPxT4nRNEC2eUCLJCdkvAYQ6X8sm1rpptereaJoZ4CMuDGxoM%3D',
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

        $this->assertNotEmpty($form_data);
        $this->assertArrayHasKey('sign', $form_data);
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
                'requestid'     => 1,
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

    public function testGetXML() {
        $xml = WebToPay::getXML($this->projectid);
        $this->assertTrue(is_bool($xml));
        $this->assertEquals(true, $xml); //false grazina jei neatsiunte/nesuparsino xml'o
    }

    public function testFilterPaymentMethods() {

        //max amount ribinė reikšmė
        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'LTL', 200000, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'USD', 83478.2, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'GBP', 50434.0, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'EUR', 57970, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        //min amount ribinė reikšmė
        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'LTL', 345, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'USD', 144, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'GBP', 87.1, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'EUR', 100, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        //min ir max amount tarpinės reikšmės
        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'LTL', 500, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'USD', 500, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'GBP', 500, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

        $payMethods = WebToPay::filterPayMethods($this->parameterArray, 'EUR', 500, $this->currencyArray, $this->lang);
        $this->assertEquals($this->filteredArray, $payMethods);

    }

    public function testParseXML() {

        $xmlObj = simplexml_load_string($this->XML);

        try {
            WebToPay::parseXML($xmlObj);
        } catch (WebToPayException $e) {
            echo get_class($e).': '.$e->getMessage();
        }

        $file   = dirname(__FILE__).DIRECTORY_SEPARATOR.'cache.php';
        $fh     = fopen($file, 'r');
        $data   = unserialize(fread($fh,filesize($file)));
        fclose($fh);

        $this->assertEquals($this->parameterArray, $data['data']);

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

    public function testGetPaymentUrl() {
        $url = WebToPay::getPaymentUrl('LT');
        $this->assertEquals($url, WebToPay::PAY_URL);
        $url = WebToPay::getPaymentUrl('ENG');
        $this->assertNotEquals($url, WebToPay::PAY_URL);
        $this->assertEquals($url, 'https://www.webtopay.com/pay/');
    }
}

