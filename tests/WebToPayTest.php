<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay
 */
class WebToPayTest extends TestCase
{
    public function getBuildRequestDataForChecking(): iterable
    {
        yield 'no projectid' => [
            [
                'sign_password' => 'asdfghjkl',
            ],
        ];

        yield 'no sign_password' => [
            [
                'projectid' => '123',
            ],
        ];
    }

    /**
     * Exception should be thrown if either projectid or sign_password is not given
     *
     * @dataProvider getBuildRequestDataForChecking
     */
    public function testBuildRequestCheckRequiredParameters(array $data)
    {
        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('sign_password or projectid is not provided');
        WebToPay::buildRequest($data);
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

    public function testGetPaymentUrl()
    {
        $url = WebToPay::getPaymentUrl('LIT');
        $this->assertEquals($url, 'https://bank.paysera.com/pay/');
        $url = WebToPay::getPaymentUrl('ENG');
        $this->assertEquals($url, 'https://bank.paysera.com/pay/');
    }
}
