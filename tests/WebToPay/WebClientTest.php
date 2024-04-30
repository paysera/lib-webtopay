<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPay_WebClientTest extends TestCase
{
    protected WebToPay_WebClient $webClientMock;

    public function setUp(): void
    {
        $this->webClientMock = $this->getMockBuilder(WebToPay_WebClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['openSocket', 'getContentFromSocket'])
            ->getMock();
    }

    public function testGet_NoSocket()
    {
        $this->webClientMock->expects($this->once())
            ->method('openSocket')
            ->with('ssl://example.com', 443)
            ->willReturn(false);

        $this->expectException(WebToPayException::class);
        $this->expectExceptionMessage('Cannot connect to https://example.com?param1=value1&param2=value2');
        $this->webClientMock->get('https://example.com', ['param1' => 'value1', 'param2' => 'value2']);
    }

    public function getDataForTestingGet(): iterable
    {
        yield 'not secure' => [
            'uri' => '',
            'host' => '',
            'host prefix' => '',
            'port' => 80,
            'scheme' => 'http',
        ];

        yield 'secure' => [
            'uri' => 'https://example.com',
            'host' => 'example.com',
            'host prefix' => 'ssl://',
            'port' => 443,
            'scheme' => 'https',
        ];
    }

    /**
     * @dataProvider getDataForTestingGet
     *
     * @throws WebToPayException
     */
    public function testGet(string $uri, string $host, string $hostPrefix, int $port, string $scheme)
    {
        $this->webClientMock->expects($this->once())
            ->method('openSocket')
            ->with($hostPrefix . $host, $port)
            ->willReturn('socket');

        $this->webClientMock->expects($this->once())
            ->method('getContentFromSocket')
            ->with(
                'socket',
                "GET ?param1=value1&param2=value2 HTTP/1.0\r\nHost: {$host}\r\nConnection: Close\r\n\r\n"
            )
            ->willReturn("HTTP/1.1 200 OK\r\n\r\nContent");

        $this->assertEquals(
            'Content',
            $this->webClientMock->get($uri, ['param1' => 'value1', 'param2' => 'value2'])
        );
    }
}
