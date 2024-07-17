<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PaymentMethodListProviderTest extends TestCase
{
    /**
     * @dataProvider dataProviderGetPaymentMethodListForExpectedUrl
     */
    public function testGetPaymentMethodListForExpectedUrl(
        string $expectedUrl,
        int $projectId,
        ?int $amount,
        string $currency
    ): void {
        $webClient = $this->createMock(WebToPay_WebClient::class);
        $urlBuilder = new WebToPay_UrlBuilder(
            [
                'routes' => [
                    'test' => [
                        'paymentMethodList' => 'https://www.paysera.com/new/api/paymentMethods/',
                    ],
                ],
            ],
            'test'
        );

        $provider = new WebToPay_PaymentMethodListProvider(
            $projectId,
            $webClient,
            $urlBuilder
        );

        $webClient->expects($this->once())->method('get')->with($expectedUrl)->willReturn(
            file_get_contents(dirname(__DIR__) . '/Functional/data/payment-methods.xml')
        );

        $provider->getPaymentMethodList($amount, $currency);
    }

    public function dataProviderGetPaymentMethodListForExpectedUrl(): array
    {
        return [
            [
                'https://www.paysera.com/new/api/paymentMethods/6028/currency:USD/amount:0',
                6028,
                0,
                'USD',
            ],
            [
                'https://www.paysera.com/new/api/paymentMethods/6028/currency:EUR/amount:1000000',
                6028,
                1000000,
                'EUR',
            ],
            [
                'https://www.paysera.com/new/api/paymentMethods/6028/currency:EUR',
                6028,
                null,
                'EUR',
            ],
        ];
    }
}
