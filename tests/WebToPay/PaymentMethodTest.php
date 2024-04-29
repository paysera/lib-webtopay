<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPay_PaymentMethodTest extends Testcase
{
    protected function createPaymentMethod(
        string $key = 'wallet',
        ?int $minAmount = 1,
        ?int $maxAmount = 10000,
        ?string $currency = 'EUR',
        array $logoList = [
            'en' => 'https://bank.paysera.com/assets/image/payment_types/wallet.png',
            'lt' => 'https://bank.paysera.com/assets/image/payment_types/wallet.png',
        ],
        array $titleTranslations = [
            'en' => 'Paysera account',
            'lt' => 'Paysera sąskaita',
        ],
        string $defaultLanguage = 'lt',
        bool $isIban = false,
        ?string $baseCurrency = 'EUR'
    ): WebToPay_PaymentMethod {
        return new WebToPay_PaymentMethod(
            $key,
            $minAmount,
            $maxAmount,
            $currency,
            $logoList,
            $titleTranslations,
            $defaultLanguage,
            $isIban,
            $baseCurrency
        );
    }

    public function testGetDefaultLanguage(): void
    {
        $paymentMethod = $this->createPaymentMethod();

        $this->assertEquals('lt', $paymentMethod->getDefaultLanguage());
    }

    public function getDataForGettingTitle(): iterable
    {
        yield 'language key exists' => [
            'defaultLanguage' => 'lt',
            'languageKey' => 'lt',
            'expectedResult' => 'Paysera sąskaita',
        ];

        yield 'language key does not exists; default language translation exists' => [
            'defaultLanguage' => 'lt',
            'languageKey' => 'non-existent language key',
            'expectedResult' => 'Paysera sąskaita',
        ];

        yield 'no language ey; no default language translation' => [
            'defaultLanguage' => 'non-existent language',
            'languageKey' => 'non-existent language key',
            'expectedResult' => 'wallet',
        ];
    }

    /**
     * @dataProvider getDataForGettingTitle
     */
    public function testGetTitle(string $defaultLanguage, string $languageKey, string $expectedResult): void
    {
        $paymentMethod = $this->createPaymentMethod()
            ->setDefaultLanguage($defaultLanguage);

        $this->assertEquals($expectedResult, $paymentMethod->getTitle($languageKey));
    }

    public function testIsAvailableForAmount(): void
    {
        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage(
            'Currencies does not match. You have to get payment types for the currency you are checking. '
            . 'Given currency: USD, available currency: EUR'
        );

        $this->createPaymentMethod()
            ->isAvailableForAmount(1000, 'USD');
    }

    public function testGetMinAmountAsString(): void
    {
        $paymentMethod = $this->createPaymentMethod();
        $this->assertEquals('1 EUR', $paymentMethod->getMinAmountAsString());

        $paymentMethod = $this->createPaymentMethod('wallet', null);
        $this->assertEquals('', $paymentMethod->getMinAmountAsString());
    }

    public function testGetMaxAmountAsString(): void
    {
        $paymentMethod = $this->createPaymentMethod('wallet', null, null);
        $this->assertEquals('', $paymentMethod->getMaxAmountAsString());

        $paymentMethod = $this->createPaymentMethod('wallet', null, 10000);
        $this->assertEquals('10000 EUR', $paymentMethod->getMaxAmountAsString());
    }

    public function getDataForGettingLogoUrl(): iterable
    {
        yield 'language key exists' => [
            'defaultLanguage' => 'lt',
            'languageKey' => 'en',
            'expectedResult' => 'https://bank.paysera.com/assets/image/payment_types/wallet_en.png',
        ];

        yield 'language key does not exists; default language translation exists' => [
            'defaultLanguage' => 'lt',
            'languageKey' => 'non-existent language key',
            'expectedResult' => 'https://bank.paysera.com/assets/image/payment_types/wallet_lt.png',
        ];

        yield 'no language ey; no default language translation' => [
            'defaultLanguage' => 'non-existent language',
            'languageKey' => 'non-existent language key',
            'expectedResult' => null,
        ];
    }

    /**
     * @dataProvider getDataForGettingLogoUrl
     */
    public function testGetLogoUrl(string $defaultLanguage, string $languageKey, ?string $expectedResult): void
    {
        $paymentMethod = $this->createPaymentMethod(
            'wallet',
            null,
            null,
            null,
            [
                'en' => 'https://bank.paysera.com/assets/image/payment_types/wallet_en.png',
                'lt' => 'https://bank.paysera.com/assets/image/payment_types/wallet_lt.png',
            ],
        );
        $paymentMethod->setDefaultLanguage($defaultLanguage);

        $this->assertEquals($expectedResult, $paymentMethod->getLogoUrl($languageKey));
    }

    public function testGetBaeCurrency(): void
    {
        $paymentMethod = $this->createPaymentMethod();

        $this->assertEquals('EUR', $paymentMethod->getBaseCurrency());

        $paymentMethod = $this->createPaymentMethod(
            'wallet',
            null,
            null,
            null,
            [],
            [],
            'lt',
            false,
            null
        );

        $this->assertEquals(null, $paymentMethod->getBaseCurrency());
    }
}
