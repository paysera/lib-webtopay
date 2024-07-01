<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_Factory
 */
class WebToPay_FactoryTest extends TestCase
{
    protected WebToPay_Factory $factory;

    protected WebToPay_Factory $factoryWithoutPasswordInConfiguration;

    protected WebToPay_Factory $factoryWithoutProjectIdInConfiguration;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->factory = new WebToPay_Factory([
            'projectId' => 123,
            'password' => 'abc',
        ]);
        $this->factoryWithoutPasswordInConfiguration = new WebToPay_Factory([
            'projectId' => 123,
        ]);
        $this->factoryWithoutProjectIdInConfiguration = new WebToPay_Factory([
            'password' => 'abc',
        ]);
    }

    public function testUseSandbox()
    {
        $this->factory->useSandbox(true);
        $urlBuilder = $this->factory->getUrlBuilder();
        $this->assertEquals('sandbox', $urlBuilder->getEnvironment());

        $this->factory->useSandbox(false);
        $urlBuilder = $this->factory->getUrlBuilder();
        $this->assertEquals('production', $urlBuilder->getEnvironment());
    }

    /**
     * Tests getUrlBuilder
     */
    public function testGetUrlBuilder()
    {
        $builder = $this->factory->getUrlBuilder();
        $this->assertSame($builder, $this->factory->getUrlBuilder());

        $this->factory->useSandbox(true);
        $this->assertNotSame($builder, $this->factory->getUrlBuilder());
    }

    /**
     * Tests getRequestBuilder
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetRequestBuilder()
    {
        $builder = $this->factory->getRequestBuilder();

        $this->assertSame($builder, $this->factory->getRequestBuilder());
    }

    /**
     * Tests getSmsAnswerSender
     *
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetSmsAnswerSender()
    {
        $sender = $this->factory->getSmsAnswerSender();

        $this->assertSame($sender, $this->factory->getSmsAnswerSender());
    }

    /**
     * Tests getPaymentMethodListProvider
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetPaymentMethodListProvider()
    {
        $provider = $this->factory->getPaymentMethodListProvider();

        $this->assertSame($provider, $this->factory->getPaymentMethodListProvider());
    }

    /**
     * Tests exception
     * @throws WebToPayException
     */
    public function testGetCallbackValidatorWithoutConfiguration()
    {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project ID');

        $this->factoryWithoutProjectIdInConfiguration->getCallbackValidator();
    }

    public function testGetRequestBuilderWithoutPasswordInConfiguration()
    {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project password to sign request');

        $this->factoryWithoutPasswordInConfiguration->getRequestBuilder();
    }

    public function testGetRequestBuilderWithoutProjectIdInConfiguration()
    {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project ID');

        $this->factoryWithoutProjectIdInConfiguration->getRequestBuilder();
    }

    /**
     * Tests exception
     */
    public function testGetSmsAnswerSenderWithoutConfiguration()
    {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project password');

        $this->factoryWithoutPasswordInConfiguration->getSmsAnswerSender();
    }

    /**
     * Tests exception
     * @throws WebToPayException
     */
    public function testGetPaymentMethodListProviderWithoutConfiguration()
    {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project ID');

        $this->factoryWithoutProjectIdInConfiguration->getPaymentMethodListProvider();
    }
}
