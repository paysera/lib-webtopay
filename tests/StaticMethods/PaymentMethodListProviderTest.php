<?php

declare(strict_types=1);

class StaticMethods_PaymentMethodListProviderTest extends StaticMethods_BaseTest
{
    /**
     * Tests getCallbackValidator
     *
     * @throws ReflectionException
     * @throws WebToPayException
     * @throws WebToPay_Exception_Configuration
     */
    public function testCheckingLibXmlExtension()
    {
        $m = Mockery::mock('alias:' . WebToPay_Functions::class);
        $m->shouldReceive('function_exists')
            ->with('simplexml_load_string')
            ->andReturn(false);

        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('You have to install libxml to use payment methods API');

        new WebToPay_PaymentMethodListProvider(
            123,
            $this->createMock(WebToPay_WebClient::class),
            $this->createMock(WebToPay_UrlBuilder::class)
        );
    }
}
