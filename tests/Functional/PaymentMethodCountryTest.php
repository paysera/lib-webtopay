<?php

declare(strict_types=1);

class Functional_PaymentMethodCountryTest extends \PHPUnit\Framework\TestCase
{
    public function testSetDefaultLanguage(): void
    {
        $paymentMethodCountry = new WebToPay_PaymentMethodCountry('lt', [
            'en' => 'Norway',
            'lt' => 'Norvegija',
        ], 'lt');

        $group = new WebToPay_PaymentMethodGroup('e-banking', [
            'en' => 'E-banking',
            'lt' => 'Elektroninė bankininkystė',
        ], 'lt');

        $paymentMethodCountry->addGroup($group);

        $paymentMethodCountry->setDefaultLanguage('en');

        $this->assertEquals('en', $paymentMethodCountry->getDefaultLanguage());
        $this->assertEquals('en', $paymentMethodCountry->getGroup('e-banking')->getDefaultLanguage());
    }
}
