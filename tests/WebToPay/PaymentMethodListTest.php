<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPay_PaymentMethodListTest extends TestCase
{
    public function testSetDefaultLanguage(): void
    {
        $paymentMethodCountryMock = $this->createMock(WebToPay_PaymentMethodCountry::class);
        $paymentMethodCountryMock->expects($this->once())
            ->method('setDefaultLanguage')
            ->with('lt');

        $paymentMethodList = new WebToPay_PaymentMethodList(1, 'EUR');
        $paymentMethodList->addCountry($paymentMethodCountryMock);

        $paymentMethodList->setDefaultLanguage('lt');
    }

    public function testFilterForAmount_currenciesMismatch(): void
    {
        $paymentMethodListMock = $this->getMockBuilder(WebToPay_PaymentMethodList::class)
            ->setConstructorArgs([123, 'EUR', 'lt', 100])
            ->onlyMethods(['isFiltered'])
            ->getMock();

        $paymentMethodListMock->expects($this->never())
            ->method('isFiltered');

        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('Currencies do not match. Given currency: USD, currency in list: EUR');

        $paymentMethodListMock->filterForAmount(100, 'USD');
    }

    public function testFilterForAmount_amountsMismatch(): void
    {
        $paymentMethodListMock = $this->getMockBuilder(WebToPay_PaymentMethodList::class)
            ->setConstructorArgs([123, 'EUR', 'lt', 100])
            ->onlyMethods(['isFiltered'])
            ->getMock();

        $paymentMethodListMock->expects($this->once())
            ->method('isFiltered')
            ->willReturn(true);

        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('This list is already filtered, use unfiltered list instead');

        $paymentMethodListMock->filterForAmount(1000, 'EUR');
    }

    /**
     * @throws WebToPayException
     */
    public function testFilterForAmount_doubleFiltering(): void
    {
        $paymentMethodListMock = $this->getMockBuilder(WebToPay_PaymentMethodList::class)
            ->setConstructorArgs([123, 'EUR', 'lt', 100])
            ->onlyMethods(['isFiltered'])
            ->getMock();

        $paymentMethodListMock->expects($this->once())
            ->method('isFiltered')
            ->willReturn(true);

        $filteredPaymentMethodList = $paymentMethodListMock->filterForAmount(100, 'EUR');

        $this->assertSame($paymentMethodListMock, $filteredPaymentMethodList);
    }
}
