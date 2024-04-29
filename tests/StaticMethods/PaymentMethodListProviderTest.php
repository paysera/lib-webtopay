<?php

declare(strict_types=1);
//if (!class_exists(AbstractTestCase::class)) {
//    include(dirname(__FILE__) . '/AbstractTestCase.php');
//}

class StaticMethods_PaymentMethodListProviderCase extends AbstractTestCase
{
    /**
     * Tests getCallbackValidator
     *
     * @throws WebToPayException
     */
    public function testCheckingLibXmlExtension()
    {
        $functionsMock = Mockery::mock('alias:' . WebToPay_Functions::class);
        $functionsMock->shouldReceive('function_exists')
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
