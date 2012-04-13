<?php

/**
 * Test for class WebToPay_Factory
 */
class WebToPay_FactoryTest extends PHPUnit_Framework_TestCase {

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
    public function setUp() {
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
     *
     * @expectedException WebToPay_Exception_Configuration
     */
    public function testGetCallbackValidatorWithoutConfiguration() {
        $this->factoryWithoutConfiguration->getCallbackValidator();
    }

    /**
     * Tests exception
     *
     * @expectedException WebToPay_Exception_Configuration
     */
    public function testGetRequestBuilderWithoutConfiguration() {
        $this->factoryWithoutConfiguration->getRequestBuilder();
    }

    /**
     * Tests exception
     *
     * @expectedException WebToPay_Exception_Configuration
     */
    public function testGetSmsAnswerSenderWithoutConfiguration() {
        $this->factoryWithoutConfiguration->getSmsAnswerSender();
    }

    /**
     * Tests exception
     *
     * @expectedException WebToPay_Exception_Configuration
     */
    public function testGetPaymentMethodListProviderWithoutConfiguration() {
        $this->factoryWithoutConfiguration->getPaymentMethodListProvider();
    }
}
