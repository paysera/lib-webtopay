<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_Factory
 */
class WebToPay_FactoryTest extends TestCase {

    /**
     * @var WebToPay_Factory
     */
    protected $factory;

    /**
     * @var WebToPay_Factory
     */
    protected $factoryWithoutConfiguration;

    /**
     * Sets up this test
     */
    public function setUp(): void {
        $this->factory = new WebToPay_Factory(array(
            'projectId' => '123',
            'password' => 'abc',
        ));
        $this->factoryWithoutConfiguration = new WebToPay_Factory();
    }

    /**
     * Tests getCallbackValidator
     */
    public function testGetCallbackValidator() {
        $validator = $this->factory->getCallbackValidator();
        $this->assertSame($validator, $this->factory->getCallbackValidator());
        $this->assertInstanceOf('WebToPay_CallbackValidator', $validator);
    }

    /**
     * Tests getRequestBuilder
     */
    public function testGetRequestBuilder() {
        $builder = $this->factory->getRequestBuilder();
        $this->assertSame($builder, $this->factory->getRequestBuilder());
        $this->assertInstanceOf('WebToPay_RequestBuilder', $builder);
    }

    /**
     * Tests getSmsAnswerSender
     */
    public function testGetSmsAnswerSender() {
        $sender = $this->factory->getSmsAnswerSender();
        $this->assertSame($sender, $this->factory->getSmsAnswerSender());
        $this->assertInstanceOf('WebToPay_SmsAnswerSender', $sender);
    }

    /**
     * Tests getPaymentMethodListProvider
     */
    public function testGetPaymentMethodListProvider() {
        $provider = $this->factory->getPaymentMethodListProvider();
        $this->assertSame($provider, $this->factory->getPaymentMethodListProvider());
        $this->assertInstanceOf('WebToPay_PaymentMethodListProvider', $provider);
    }

    /**
     * Tests exception
     */
    public function testGetCallbackValidatorWithoutConfiguration() {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->factoryWithoutConfiguration->getCallbackValidator();
    }

    /**
     * Tests exception
     */
    public function testGetRequestBuilderWithoutConfiguration() {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->factoryWithoutConfiguration->getRequestBuilder();
    }

    /**
     * Tests exception
     */
    public function testGetSmsAnswerSenderWithoutConfiguration() {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->factoryWithoutConfiguration->getSmsAnswerSender();
    }

    /**
     * Tests exception
     */
    public function testGetPaymentMethodListProviderWithoutConfiguration() {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->factoryWithoutConfiguration->getPaymentMethodListProvider();
    }
}
