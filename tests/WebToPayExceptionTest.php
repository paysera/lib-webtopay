<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPayExceptionTest extends TestCase
{
    /**
     * @throws WebToPayException
     */
    public function testExceptionWithField(): void
    {
        $requestBuilder = new WebToPay_RequestBuilder(
            123,
            'asdfghjkl',
            $this->createMock(WebToPay_Util::class),
            $this->createMock(WebToPay_UrlBuilder::class)
        );

        try {
            $requestBuilder->buildRequest([]);
        } catch (WebToPay_Exception_Validation $e) {
            $this->assertEquals("'orderid' is required but missing.", $e->getMessage());
            $this->assertEquals('orderid', $e->getField());
        }
    }

    public function testExceptionWithoutField(): void
    {
        try {
            WebToPay::buildRequest([]);
        } catch (WebToPayException $e) {
            $this->assertEquals('sign_password or projectid is not provided', $e->getMessage());
            $this->assertNull($e->getField());
        }
    }
}
