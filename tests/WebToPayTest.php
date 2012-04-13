<?php

/**
 * Test for class WebToPay
 */
class WebToPayTest extends PHPUnit_Framework_TestCase {

    public function testGetPaymentUrl() {
        $url = WebToPay::getPaymentUrl('LIT');
        $this->assertEquals($url, WebToPay::PAY_URL);
        $url = WebToPay::getPaymentUrl('ENG');
        $this->assertEquals($url, 'https://www.webtopay.com/pay/');
    }

    /**
     * Exception should be thrown if project id is not given
     *
     * @expectedException WebToPayException
     */
    public function testBuildRequestWithoutProjectId() {
        WebToPay::buildRequest(array(
            'orderid' => '123',
            'accepturl' => 'http://local.test/accept',
            'cancelurl' => 'http://local.test/cancel',
            'callbackurl' => 'http://local.test/callback',

            'sign_password' => 'asdfghjkl',
        ));
    }

    /**
     * Exception should be thrown if order id is not given
     *
     * @expectedException WebToPayException
     */
    public function testBuildRepeatRequestWithoutProjectId() {
        WebToPay::buildRepeatRequest(array(
            'sign_password' => 'asdfghjkl',
            'projectid' => '123',
        ));
    }
}

