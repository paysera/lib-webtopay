<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class Functional_PaymentMethodListProviderTest extends TestCase
{
    protected WebToPay_PaymentMethodListProvider $paymentMethodListProvider;

    /**
     * @throws WebToPayException
     */
    public function testGetPaymentMethodList(): void
    {
        $projectId = 123;
        $amount = 100;
        $currency = 'EUR';
        $xmlAsString = file_get_contents(__DIR__ . '/data/payment-methods.xml');

        $webClient = $this->createMock(WebToPay_WebClient::class);
        $webClient->expects($this->once())
            ->method('get')
            ->with('https://sandbox.paysera.com/new/api/paymentMethods/?projectid=123&amount=100&currency=EUR')
            ->willReturn($xmlAsString);

        $urlBuilder = $this->createMock(WebToPay_UrlBuilder::class);
        $urlBuilder->expects($this->once())
            ->method('buildForPaymentsMethodList')
            ->with($projectId, (string) $amount, $currency)
            ->willReturn('https://sandbox.paysera.com/new/api/paymentMethods/?projectid=123&amount=100&currency=EUR');

        $paymentMethodListProvider = new WebToPay_PaymentMethodListProvider($projectId, $webClient, $urlBuilder);
        $paymentMethodList = $paymentMethodListProvider->getPaymentMethodList($amount, $currency);

        $this->assertEquals($projectId, $paymentMethodList->getProjectId());
        $this->assertEquals($currency, $paymentMethodList->getCurrency());

        $filterForAmount = $paymentMethodList->filterForAmount(10000, 'EUR');
        $filteredPaymentMethods = $filterForAmount
            ->getCountry('lt')
            ->getGroup('e-banking')
            ->getPaymentMethods();

        $this->assertCount(1, $filteredPaymentMethods);
        $this->assertArrayHasKey('wallet', $filteredPaymentMethods);
        $this->assertArrayNotHasKey('no_trustly', $filteredPaymentMethods);

        $this->assertInstanceOf(WebToPay_PaymentMethodList::class, $paymentMethodList);

        $this->assertCount(1, $paymentMethodList->getCountries());
        $this->assertArrayHasKey('lt', $paymentMethodList->getCountries());
        $this->assertEquals('lt', $paymentMethodList->getDefaultLanguage());

        $paymentsForCountry = $paymentMethodList->getCountry('lt');
        $this->assertInstanceOf(WebToPay_PaymentMethodCountry::class, $paymentsForCountry);
        $this->assertEquals('lt', $paymentsForCountry->getDefaultLanguage());
        $this->assertFalse($paymentsForCountry->isEmpty());
        $this->assertEquals('Norway', $paymentsForCountry->getTitle('en'));
        $this->assertSame(
            $paymentsForCountry->getTitle($paymentsForCountry->getDefaultLanguage()),
            $paymentsForCountry->getTitle('non-existent language key')
        );

        $filterForAmount = $paymentsForCountry->filterForAmount(10, 'EUR');
        $this->assertCount(2, $filterForAmount->getGroups());
        $this->assertArrayHasKey('e-banking', $filterForAmount->getGroups());
        $this->assertArrayHasKey('other', $filterForAmount->getGroups());

        $filterForAmount = $paymentsForCountry->filterForAmount(10000, 'EUR');
        $this->assertCount(2, $filterForAmount->getGroups());
        $this->assertArrayHasKey('e-banking', $filterForAmount->getGroups());
        $this->assertArrayHasKey('other', $filterForAmount->getGroups());

        $filterForIban = $paymentsForCountry->filterForIban(true);
        $this->assertCount(1, $filterForIban->getGroups());
        $this->assertArrayHasKey('other', $filterForIban->getGroups());
        $this->assertCount(1, $filterForIban->getPaymentMethods());
        $this->assertArrayHasKey('vb2', $filterForIban->getPaymentMethods());

        $groups = $paymentsForCountry->getGroups();
        $this->assertCount(2, $groups);
        $this->assertArrayHasKey('e-banking', $groups);
        $this->assertArrayHasKey('other', $groups);

        $paymentMethodGroup = $groups['e-banking'];
        $this->assertEquals($paymentMethodGroup, $paymentsForCountry->getGroup('e-banking'));
        $paymentMethods = $paymentMethodGroup->getPaymentMethods();
        $this->assertCount(2, $paymentMethods);
        $this->assertArrayHasKey('wallet', $paymentMethods);
        $this->assertArrayHasKey('no_trustly', $paymentMethods);

        $paymentMethod = $paymentMethodGroup->getPaymentMethod('wallet');
        $this->assertNotNull($paymentMethod);
        $this->assertEquals('wallet', $paymentMethod->getKey());
    }

    public function testExceptionExpectationDueEmptyRootNode(): void
    {
        $webClient = $this->createMock(WebToPay_WebClient::class);
        $webClient->expects($this->once())
            ->method('get')
            ->willReturn('');

        $urlBuilder = $this->createMock(WebToPay_UrlBuilder::class);
        $urlBuilder->expects($this->once())
            ->method('buildForPaymentsMethodList');

        $paymentMethodListProvider = new WebToPay_PaymentMethodListProvider(123, $webClient, $urlBuilder);

        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('Unable to load XML from remote server');
        $paymentMethodListProvider->getPaymentMethodList(0, 'EUR');
    }
}
