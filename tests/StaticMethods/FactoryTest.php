<?php

declare(strict_types=1);

if (!class_exists(AbstractTestCase::class)) {
    include(dirname(__FILE__) . '/AbstractTestCase.php');
}

class StaticMethods_FactoryCase extends AbstractTestCase
{
    protected WebToPay_Factory $factory;

    protected WebToPay_Factory $factoryWithoutPasswordInConfiguration;

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
        $functionsMock = Mockery::mock('alias:' . WebToPay_Functions::class);
        $functionsMock->shouldReceive('function_exists')
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
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetCallbackValidator_CorrectConfiguration_OpenSslExists_NoPublicKey()
    {
        $functionsMock = Mockery::mock('alias:' . WebToPay_Functions::class);
        $functionsMock->shouldReceive('function_exists')
            ->with('openssl_pkey_get_public')
            ->andReturn(true);

        $webClientMock = $this->createMock(WebToPay_WebClient::class);
        $webClientMock->expects($this->once())
            ->method('get')
            ->willReturn('');

        $factoryMock = $this->getMockBuilder(WebToPay_Factory::class)
            ->setConstructorArgs([['projectId' => 123, 'password' => 'abc']])
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
        $functionsMock = Mockery::mock('alias:' . WebToPay_Functions::class);
        $functionsMock->shouldReceive('function_exists')
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
        $functionsMock = Mockery::mock('alias:' . WebToPay_Functions::class);
        $functionsMock->shouldReceive('function_exists')
            ->with('openssl_pkey_get_public')
            ->andReturn(false);

        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project password if OpenSSL is unavailable');
        $this->factoryWithoutPasswordInConfiguration->getCallbackValidator();
    }
}
