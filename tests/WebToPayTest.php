<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay
 */
class WebToPayTest extends TestCase
{
    public function testGetPaymentUrl()
    {
        $url = WebToPay::getPaymentUrl('LIT');
        $this->assertEquals($url, WebToPay::PAY_URL);
        $url = WebToPay::getPaymentUrl('ENG');
        $this->assertEquals($url, 'https://bank.paysera.com/pay/');
    }

    /**
     * Exception should be thrown if project id is not given
     */
    public function testBuildRequestWithoutProjectId()
    {
        $this->expectException(WebToPayException::class);
        WebToPay::buildRequest([
            'orderid' => '123',
            'accepturl' => 'http://local.test/accept',
            'cancelurl' => 'http://local.test/cancel',
            'callbackurl' => 'http://local.test/callback',

            'sign_password' => 'asdfghjkl',
        ]);
    }

    /**
     * Exception should be thrown if order id is not given
     */
    public function testBuildRepeatRequestWithoutProjectId()
    {
        $this->expectException(WebToPayException::class);
        WebToPay::buildRepeatRequest([
            'sign_password' => 'asdfghjkl',
            'projectid' => '123',
        ]);
    }
}
