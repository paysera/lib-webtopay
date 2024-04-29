<?php

declare(strict_types=1);

use Mockery\MockInterface;
if (!class_exists(AbstractTestCase::class)) {
    include(dirname(__FILE__) . '/AbstractTestCase.php');
}

class StaticMethods_WebToPayCase extends AbstractTestCase
{
    /**
     * @var WebToPay_Factory|MockInterface
     */
    protected $factoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factoryMock = Mockery::mock('overload:' . WebToPay_Factory::class);
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

    /**
     * @throws WebToPayException
     */
    public function testBuildRequest(): void
    {
        $requestBuilderMock = $this->createMock(WebToPay_RequestBuilder::class);
        $requestBuilderMock->expects($this->once())
            ->method('buildRequest')
            ->with([]);

        $this->factoryMock->shouldReceive('getRequestBuilder')
            ->once()
            ->andReturn($requestBuilderMock);

        WebToPay::buildRequest([
            'projectid' => '123',
            'sign_password' => 'asdfghjkl',
        ]);
    }

    public function getDataForTestingRedirectToPayment(): iterable
    {
        yield 'headers are sent' => [
            'headers sent' => true,
            'output' => '<script type="text/javascript">window.location = "https://example.com";</script>'
                . 'Redirecting to <a href="https://example.com">https://example.com</a>. Please wait.',
            'expectedHeader' => null,
        ];

        yield 'headers are not sent' => [
            'headers sent' => false,
            'output' => 'Redirecting to <a href="https://example.com">https://example.com</a>. Please wait.',
            'expectedHeader' => 'Location: https://example.com',
        ];
    }

    /**
     * @dataProvider getDataForTestingRedirectToPayment
     * @throws WebToPayException
     */
    public function testRedirectToPayment(bool $headersSent, string $expectedOutput, ?string $expectedHeaders): void
    {
        $url = 'https://example.com';
        $requestBuilderMock = $this->createMock(WebToPay_RequestBuilder::class);
        $requestBuilderMock->expects($this->once())
            ->method('buildRequestUrlFromData')
            ->with([])
            ->willReturn($url);

        $this->factoryMock->shouldReceive('getRequestBuilder')
            ->once()
            ->andReturn($requestBuilderMock);

        $functionsMock = Mockery::mock('alias:' . WebToPay_Functions::class);
        $functionsMock->shouldReceive('headers_sent')
            ->withNoArgs()
            ->andReturn($headersSent);

        WebToPay::redirectToPayment([
            'projectid' => '123',
            'sign_password' => 'asdfghjkl',
        ]);

        $this->expectOutputString($expectedOutput);

        if (!$headersSent) {
            $sentHeaders = xdebug_get_headers();
            $this->assertContains($expectedHeaders, $sentHeaders);
        }
    }

    /**
     * @throws WebToPayException
     */
    public function testBuildRepeatRequest(): void
    {
        $orderId = 111111;

        $requestBuilderMock = $this->createMock(WebToPay_RequestBuilder::class);
        $requestBuilderMock->expects($this->once())
            ->method('buildRepeatRequest')
            ->with($orderId);

        $this->factoryMock->shouldReceive('getRequestBuilder')
            ->once()
            ->andReturn($requestBuilderMock);

        WebToPay::buildRepeatRequest([
            'projectid' => '123',
            'sign_password' => 'asdfghjkl',
            'orderid' => $orderId,
        ]);
    }

    /**
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     * @throws WebToPay_Exception_Configuration
     */
    public function testValidateAndParseData(): void
    {
        $request = [
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret'),
            'ss2' => 'bad-ss2',
        ];

        $callbackValidatorMock = $this->createMock(WebToPay_CallbackValidator::class);
        $callbackValidatorMock->expects($this->once())
            ->method('validateAndParseData')
            ->with($request);

        $this->factoryMock->shouldReceive('getCallbackValidator')
            ->once()
            ->andReturn($callbackValidatorMock);

        WebToPay::validateAndParseData($request, 123, 'password');
    }

    /**
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     * @throws WebToPay_Exception_Configuration
     */
    public function testGetPaymentMethodList(): void
    {
        $amount = 1000;
        $currency = 'EUR';

        $paymentMethodListProviderMock = $this->createMock(WebToPay_PaymentMethodListProvider::class);
        $paymentMethodListProviderMock->expects($this->once())
            ->method('getPaymentMethodList')
            ->with($amount, $currency);

        $this->factoryMock->shouldReceive('getPaymentMethodListProvider')
            ->once()
            ->andReturn($paymentMethodListProviderMock);

        WebToPay::getPaymentMethodList(123, $amount, $currency);
    }
}
