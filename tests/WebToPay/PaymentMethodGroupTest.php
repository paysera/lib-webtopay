<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPay_PaymentMethodGroupTest extends TestCase
{
    public function testSetDefaultLanguage(): void
    {
        $newDefaultLanguage = 'en';

        $paymentMethodMock = $this->createMock(WebToPay_PaymentMethod::class);
        $paymentMethodMock->expects($this->once())
            ->method('setDefaultLanguage')
            ->with($newDefaultLanguage);

        $paymentMethodGroup = new WebToPay_PaymentMethodGroup('e-banking', [], 'lt');
        $paymentMethodGroup->addPaymentMethod($paymentMethodMock);

        $paymentMethodGroup->setDefaultLanguage($newDefaultLanguage);
    }

    public function getDataForGettingTitle(): iterable
    {
        yield 'language key exists' => [
            'translations' => [
                'en' => 'E-banking',
                'lt' => 'Elektroninė bankininkystė',
            ],
            'languageKey' => 'en',
            'expectedResult' => 'E-banking',
        ];

        yield 'language key does not exists; default language translation exists' => [
            'translations' => [
                'en' => 'E-banking',
                'lt' => 'Elektroninė bankininkystė',
            ],
            'languageKey' => 'non-existent language key',
            'expectedResult' => 'Elektroninė bankininkystė',
        ];

        yield 'no language key; no default language translation' => [
            'translations' => [
                'en' => 'E-banking',
                'some country code' => 'Elektroninė bankininkystė',
            ],
            'languageKey' => 'non-existent language key',
            'expectedResult' => 'E-banking',
        ];
    }

    /**
     * @dataProvider getDataForGettingTitle
     */
    public function testGetTitle(array $translations, string $languageKey, string $expectedResult): void
    {
        $paymentMethodGroup = new WebToPay_PaymentMethodGroup('E-banking', $translations, 'lt');

        $this->assertEquals($expectedResult, $paymentMethodGroup->getTitle($languageKey));
    }
}
