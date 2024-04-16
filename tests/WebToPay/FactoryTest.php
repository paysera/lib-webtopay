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
            'projectId' => '123',
            'password' => 'abc',
        ]);
        $this->factoryWithoutPasswordInConfiguration = new WebToPay_Factory([
            'projectId' => '123',
        ]);
        $this->factoryWithoutProjectIdInConfiguration = new WebToPay_Factory([
            'password' => 'abc',
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $container = Mockery::getContainer();
        if ($container !== null) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        Mockery::close();
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
     * Tests getCallbackValidator
     *
     * @throws ReflectionException
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetCallbackValidator_CorrectConfiguration_OpenSslExists()
    {
        $m = Mockery::mock('alias:' . WebToPay_Functions::class);
        $m->shouldReceive('function_exists')
            ->with('openssl_pkey_get_public')
            ->andReturn(true);

        $validator = $this->factory->getCallbackValidator();
        $this->assertSame($validator, $this->factory->getCallbackValidator());

        $reflectionClass = new \ReflectionClass($validator);
        $reflectionProperty = $reflectionClass->getProperty('signer');
        $reflectionProperty->setAccessible(true);

        $this->assertInstanceOf(
            WebToPay_Sign_SS2SignChecker::class,
            $reflectionProperty->getValue($validator)
        );
    }

    /**
     * Tests getCallbackValidator
     *
     * @throws ReflectionException
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetCallbackValidator_CorrectConfiguration_OpenSslExists_NoPublicKey()
    {
        $m = Mockery::mock('alias:' . WebToPay_Functions::class);
        $m->shouldReceive('function_exists')
            ->with('openssl_pkey_get_public')
            ->andReturn(true);

        $webClientMock = $this->createMock(WebToPay_WebClient::class);
        $webClientMock->expects($this->once())
            ->method('get')
            ->willReturn('');

        $factoryMock = $this->getMockBuilder(WebToPay_Factory::class)
            ->setConstructorArgs([['projectId' => '123', 'password' => 'abc']])
            ->onlyMethods(['getWebClient'])
            ->getMock();
        $factoryMock->expects($this->once())
            ->method('getWebClient')
            ->willReturn($webClientMock);

        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('Cannot download public key from WebToPay website');
        $factoryMock->getCallbackValidator();
    }

    /**
     * Tests getCallbackValidator
     *
     * @throws ReflectionException
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetCallbackValidator_CorrectConfiguration_OpenSslDoesNotExist()
    {
        $m = Mockery::mock('alias:' . WebToPay_Functions::class);
        $m->shouldReceive('function_exists')
            ->with('openssl_pkey_get_public')
            ->andReturn(false);

        $validator = $this->factory->getCallbackValidator();
        $this->assertSame($validator, $this->factory->getCallbackValidator());

        $reflectionClass = new \ReflectionClass($validator);
        $reflectionProperty = $reflectionClass->getProperty('signer');
        $reflectionProperty->setAccessible(true);

        $this->assertInstanceOf(
            WebToPay_Sign_SS1SignChecker::class,
            $reflectionProperty->getValue($validator)
        );
    }

    /**
     * Tests getCallbackValidator
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetCallbackValidator_ConfigurationDoesNotContainPassword_OpenSslDoesNotExist()
    {
        $m = Mockery::mock('alias:' . WebToPay_Functions::class);
        $m->shouldReceive('function_exists')
            ->with('openssl_pkey_get_public')
            ->andReturn(false);

        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project password if OpenSSL is unavailable');
        $validator = $this->factoryWithoutPasswordInConfiguration->getCallbackValidator();
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
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetPaymentMethodListProvider()
    {
        $provider = $this->factory->getPaymentMethodListProvider();
        $this->assertSame($provider, $this->factory->getPaymentMethodListProvider());
    }

    /**
     * Tests exception
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
        $this->factoryWithoutPasswordInConfiguration->getSmsAnswerSender();
    }

    /**
     * Tests exception
     */
    public function testGetPaymentMethodListProviderWithoutConfiguration()
    {
        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->factoryWithoutProjectIdInConfiguration->getPaymentMethodListProvider();
    }
}
