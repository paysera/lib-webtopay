<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_RequestBuilder
 */
class WebToPay_RequestBuilderTest extends TestCase
{
    protected WebToPay_UrlBuilder $urlBuilder;

    protected WebToPay_Util $util;

    protected WebToPay_RequestBuilder $builder;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->util = $this->createMock('WebToPay_Util');
        $this->urlBuilder = $this->createMock(WebToPay_UrlBuilder::class);

        $this->builder = new WebToPay_RequestBuilder(123, 'secret', $this->util, $this->urlBuilder);
    }

    public function getDataForCheckingRequiredParameters(): iterable
    {
        yield 'empty data (no orderid)' => [
            'data' => [],
            'error message' => "'orderid' is required but missing.",
        ];

        yield 'missing accepturl' => [
            'data' => [
                'orderid' => 123,
            ],
            'error message' => "'accepturl' is required but missing.",
        ];

        yield 'missing cancelurl' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
            ],
            'error message' => "'cancelurl' is required but missing.",
        ];

        yield 'missing callbackurl' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
            ],
            'error message' => "'callbackurl' is required but missing.",
        ];

        yield 'empty orderid' => [
            'data' => [
                'orderid' => '',
            ],
            'error message' => "'orderid' is required but missing.",
        ];

        yield 'empty accepturl' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => '',
            ],
            'error message' => "'accepturl' is required but missing.",
        ];

        yield 'empty cancelurl' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => '',
            ],
            'error message' => "'cancelurl' is required but missing.",
        ];

        yield 'empty callbackurl' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => '',
            ],
            'error message' => "'callbackurl' is required but missing.",
        ];

        yield 'wrong orderid value' => [
            'data' => [
                'orderid' => str_repeat('a', 41),
            ],
            'error message' => "'orderid' value is too long (41), 40 characters allowed.",
        ];

        yield 'wrong accepturl value' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => str_repeat('a', 256),
            ],
            'error message' => "'accepturl' value is too long (256), 255 characters allowed.",
        ];

        yield 'wrong cancelurl value' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => str_repeat('a', 256),
            ],
            'error message' => "'cancelurl' value is too long (256), 255 characters allowed.",
        ];

        yield 'wrong callbackurl value' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => str_repeat('a', 256),
            ],
            'error message' => "'callbackurl' value is too long (256), 255 characters allowed.",
        ];
    }

    /**
     * @dataProvider getDataForCheckingRequiredParameters
     *
     * @throws WebToPayException
     */
    public function testValidateRequiredParametersOfRequest(array $data, string $errorMessage)
    {
        $this->expectException(WebToPay_Exception_Validation::class);
        $this->expectExceptionMessage($errorMessage);
        $this->builder->buildRequest($data);
    }

    public function getDataForCheckingNonRequiredParameters(): iterable
    {
        yield '`lang` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'lang' => str_repeat('l', 4),
            ],
            'error message' => "'lang' value is too long (4), 3 characters allowed.",
        ];

        yield '`lang` does not match the pattern: too short' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'lang' => 'lt',
            ],
            'error message' => "'lang' value 'lt' is invalid.",
        ];

        yield '`lang` does not match the pattern: contains non-letters' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'lang' => '123',
            ],
            'error message' => "'lang' value '123' is invalid.",
        ];

        yield '`amount` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => str_repeat('1', 12),
            ],
            'error message' => "'amount' value is too long (12), 11 characters allowed.",
        ];

        yield '`amount` does not match the pattern: contains non-digits' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => 'avf444',
            ],
            'error message' => "'amount' value 'avf444' is invalid.",
        ];

        yield '`currency` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'currency' => str_repeat('c', 4),
            ],
            'error message' => "'currency' value is too long (4), 3 characters allowed.",
        ];

        yield '`currency` does not match the pattern: too short' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'currency' => 'lt',
            ],
            'error message' => "'currency' value 'lt' is invalid.",
        ];

        yield '`currency` does not match the pattern: contains non-letters' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'currency' => '123',
            ],
            'error message' => "'currency' value '123' is invalid.",
        ];

        yield '`payment` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'payment' => str_repeat('p', 21),
            ],
            'error message' => "'payment' value is too long (21), 20 characters allowed.",
        ];

        yield '`country` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'country' => str_repeat('c', 3),
            ],
            'error message' => "'country' value is too long (3), 2 characters allowed.",
        ];

        yield '`country` does not match the pattern: contains non-letters' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'country' => '12',
            ],
            'error message' => "'country' value '12' is invalid.",
        ];

        yield '`paytext` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'paytext' => str_repeat('p', 256),
            ],
            'error message' => "'paytext' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_firstname` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_firstname' => str_repeat('p', 256),
            ],
            'error message' => "'p_firstname' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_lastname` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_lastname' => str_repeat('p', 256),
            ],
            'error message' => "'p_lastname' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_email` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_email' => str_repeat('p', 256),
            ],
            'error message' => "'p_email' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_street` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_street' => str_repeat('p', 256),
            ],
            'error message' => "'p_street' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_city` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_city' => str_repeat('p', 256),
            ],
            'error message' => "'p_city' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_state` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_state' => str_repeat('p', 256),
            ],
            'error message' => "'p_state' value is too long (256), 255 characters allowed.",
        ];

        yield '`p_zip` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_zip' => str_repeat('p', 21),
            ],
            'error message' => "'p_zip' value is too long (21), 20 characters allowed.",
        ];

        yield '`p_countrycode` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_countrycode' => str_repeat('p', 3),
            ],
            'error message' => "'p_countrycode' value is too long (3), 2 characters allowed.",
        ];

        yield '`p_countrycode` does not match the pattern: contains non-letters' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_countrycode' => '12',
            ],
            'error message' => "'p_countrycode' value '12' is invalid.",
        ];

        yield '`p_countrycode` does not match the pattern: too short' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'p_countrycode' => 'c',
            ],
            'error message' => "'p_countrycode' value 'c' is invalid.",
        ];

        yield '`test` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'test' => '11',
            ],
            'error message' => "'test' value is too long (2), 1 characters allowed.",
        ];

        yield '`test` does not match the pattern: contains non-digits' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'test' => 't',
            ],
            'error message' => "'test' value 't' is invalid.",
        ];

        yield '`test` does not match the pattern: contains neither 0 nor 1' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'test' => '2',
            ],
            'error message' => "'test' value '2' is invalid.",
        ];

        yield '`time_limit` has wrong length' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'time_limit' => '2021-01-01 00:00:001',
            ],
            'error message' => "'time_limit' value is too long (20), 19 characters allowed.",
        ];

        yield '`time_limit` does not match the pattern: contains invalid date and time format' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'time_limit' => '2021-01-01 0:00:00',
            ],
            'error message' => "'time_limit' value '2021-01-01 0:00:00' is invalid.",
        ];
    }

    /**
     * @dataProvider getDataForCheckingNonRequiredParameters
     *
     * @throws WebToPayException
     */
    public function testValidateNonRequiredParametersOfRequest(array $data, string $errorMessage)
    {
        $this->expectException(WebToPay_Exception_Validation::class);
        $this->expectExceptionMessage($errorMessage);
        $this->builder->buildRequest($data);
    }

    /**
     * Tests buildRequest method
     *
     * @throws WebToPayException
     */
    public function testBuildRequest()
    {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with(
                'orderid=123&accepturl=http%3A%2F%2Flocal.test%2F&cancelurl=http%3A%2F%2Flocal.test%2F'
                    . '&callbackurl=http%3A%2F%2Flocal.test%2F&amount=100&some-other-parameter=abc'
                    . '&version=' . WebToPay::VERSION
                    . '&projectid=123'
            )
            ->willReturn('encoded');
        $this->assertEquals(
            [
                'data' => 'encoded',
                'sign' => md5('encodedsecret'),
            ],
            $this->builder->buildRequest([
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => 100,
                'some-other-parameter' => 'abc',
            ])
        );
    }

    /**
     * Tests buildRepeatRequest method
     */
    public function testBuildRepeatRequest()
    {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with(sprintf('orderid=123&version=%s&projectid=123&repeat_request=1', WebToPay::VERSION))
            ->willReturn('encoded');
        $this->assertEquals(
            [
                'data' => 'encoded',
                'sign' => md5('encodedsecret'),
            ],
            $this->builder->buildRepeatRequest(123)
        );
    }

    public function getDataFroCheckingBuildingRequestUrlFromData(): iterable
    {
        yield 'no language in request data' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => 100,
                'currency' => 'EUR',
            ],
            'expectedQueryString' => 'orderid=123&accepturl=http%3A%2F%2Flocal.test%2F'
                . '&cancelurl=http%3A%2F%2Flocal.test%2F&callbackurl=http%3A%2F%2Flocal.test%2F'
                . '&amount=100&currency=EUR'
                . '&version=' . WebToPay::VERSION
                . '&projectid=123',
            'language' => null,
        ];

        yield 'language is set in request data' => [
            'data' => [
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => 100,
                'currency' => 'EUR',
                'lang' => 'lit',
            ],
            'expectedQueryString' => 'orderid=123&accepturl=http%3A%2F%2Flocal.test%2F'
                . '&cancelurl=http%3A%2F%2Flocal.test%2F&callbackurl=http%3A%2F%2Flocal.test%2F'
                . '&amount=100&currency=EUR&lang=lit'
                . '&version=' . WebToPay::VERSION
                . '&projectid=123',
            'language' => 'lit',
        ];
    }

    /**
     * @dataProvider getDataFroCheckingBuildingRequestUrlFromData
     *
     * @throws WebToPayException
     */
    public function testBuildRequestUrlFromData(array $data, string $expectedQueryString, ?string $language)
    {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with($expectedQueryString)
            ->willReturn('encoded');

        $this->urlBuilder->expects($this->once())
            ->method('buildForRequest')
            ->with([
                'data' => 'encoded',
                'sign' => md5('encodedsecret'),
            ], $language);

        $this->builder->buildRequestUrlFromData($data);
    }

    /**
     * @throws WebToPayException
     */
    public function testBuildRepeatRequestUrlFromOrderId()
    {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with(sprintf('orderid=123&version=%s&projectid=123&repeat_request=1', WebToPay::VERSION))
            ->willReturn('encoded');

        $this->urlBuilder->expects($this->once())
            ->method('buildForRequest')
            ->with([
                'data' => 'encoded',
                'sign' => md5('encodedsecret'),
            ], null);

        $this->builder->buildRepeatRequestUrlFromOrderId(123);
    }
}
