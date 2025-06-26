<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_CallbackValidator
 */
class WebToPay_CallbackValidatorTest extends TestCase
{
    private const PROJECT_PASSWORD = 'pass';

    protected WebToPay_Sign_SignCheckerInterface $signer;

    protected WebToPay_Util $util;

    protected WebToPay_CallbackValidator $validator;

    /**
     * Sets up this test
     */
    protected function setUp(): void
    {
        $this->signer = $this->createMock(WebToPay_Sign_SignCheckerInterface::class);
        $this->util = $this->createMock(WebToPay_Util::class);
        $this->validator = new WebToPay_CallbackValidator(
            123,
            $this->signer,
            $this->util,
            self::PROJECT_PASSWORD
        );
    }

    /**
     * @throws WebToPayException
     */
    public function testValidateAndParseData_NoDataKey()
    {
        $this->expectException(WebToPay_Exception_Callback::class);
        $this->expectExceptionMessage('"data" parameter not found');
        $this->validator->validateAndParseData([]);
    }

    public function getRequestDataForCheckingSign(): iterable
    {
        yield 'ss1' => [
            'request' => [
                'data' => 'abcdef',
                'ss2' => 'zxcvb',
            ],
        ];

        yield 'ss2' => [
            'request' => [
                'data' => 'abcdef',
                'ss2' => 'zxcvb',
            ],
        ];

        yield 'ss3' => [
            'request' => [
                'data' => 'abcdef',
                'ss3' => 'zxcvb',
            ],
        ];
    }

    /**
     * Exception should be thrown on invalid sign
     *
     * @dataProvider getRequestDataForCheckingSign
     * @throws WebToPayException
     */
    public function testValidateAndParseDataWithInvalidSign(array $request): void
    {
        $this->signer->expects($this->once())
            ->method('checkSign')
            ->with($request)
            ->willReturn(false);

        $this->expectException(WebToPay_Exception_Callback::class);
        $this->expectExceptionMessage(
            sprintf(
                'Invalid sign parameters, check $_GET length limit. Sign checker: %s',
                get_class($this->signer)
            )
        );
        $this->validator->validateAndParseData($request);
    }

    /**
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     */
    public function testValidateAndParseData_NoCredentials()
    {
        $validatorWithoutPassword = new WebToPay_CallbackValidator(
            123,
            $this->signer,
            $this->util
        );

        $this->expectException(WebToPay_Exception_Configuration::class);
        $this->expectExceptionMessage('You have to provide project password');
        $validatorWithoutPassword->validateAndParseData(['data' => '']);
    }

    /**
     * Exception should be thrown if decryption has failed
     *
     * @throws WebToPayException
     */
    public function testValidateAndParseDataWithDecryptionFailure(): void
    {
        $request = ['data' => ''];

        $this->util->expects($this->once())
            ->method('decryptGCM')
            ->willReturn(null);

        $this->expectException(WebToPay_Exception_Callback::class);
        $this->expectExceptionMessage('Callback data decryption failed');
        $this->validator->validateAndParseData($request);
    }

    /**
     * @throws WebToPayException
     */
    public function testValidateAndParseDataWithNoProjectInCallback(): void
    {
        $request = ['data' => 'abcdef'];

        $this->util->expects($this->once())
            ->method('decryptGCM')
            ->willReturn('zxc');
        $this->util->expects($this->once())
            ->method('parseHttpQuery')
            ->with('zxc')
            ->willReturn([]);

        $this->expectException(WebToPay_Exception_Callback::class);
        $this->expectExceptionMessage('Project ID not provided in callback');
        $this->validator->validateAndParseData($request);
    }

    /**
     * Exception should be thrown if project ID does not match expected
     *
     * @throws WebToPayException
     */
    public function testValidateAndParseDataWithInvalidProject(): void
    {
        $request = [
            'data' => 'abcdef',
            'sign' => 'qwerty',
            'ss1' => 'randomChecksum',
        ];
        $parsed = [
            'projectid' => 456,
        ];

        $this->signer->expects($this->once())
            ->method('checkSign')
            ->with($request)
            ->willReturn(true);
        $this->util->expects($this->once())
            ->method('decodeSafeUrlBase64')
            ->with('abcdef')
            ->willReturn('zxc');
        $this->util->expects($this->once())
            ->method('parseHttpQuery')
            ->with('zxc')
            ->willReturn($parsed);

        $this->expectException(WebToPay_Exception_Callback::class);
        $this->expectExceptionMessage('Bad projectid: 456, should be: 123');
        $this->validator->validateAndParseData($request);
    }

    public function getMicroAndMacroRequestData(): iterable
    {
        yield 'type is set but it is neither micro nor macro' => [
            'callback' => [
                'type' => 'abcdef',
            ],
            'expectedType' => 'macro',
        ];

        yield 'type is set and it is micro' => [
            'callback' => [
                'type' => 'micro',
            ],
            'expectedType' => 'micro',
        ];

        yield 'type is set and it is macro' => [
            'callback' => [
                'type' => 'macro',
            ],
            'expectedType' => 'macro',
        ];

        yield 'type is not set; additional parameters are not set' => [
            'callback' => [],
            'expectedType' => 'macro',
        ];

        yield 'type is not set; all additional parameters are set' => [
            'callback' => [
                'to' => '123',
                'from' => '456',
                'sms' => '789',
            ],
            'expectedType' => 'micro',
        ];

        yield 'type is set; all additional parameters are set' => [
            'callback' => [
                'to' => '123',
                'from' => '456',
                'sms' => '789',
            ],
            'expectedType' => 'micro',
        ];

        yield 'type is not set; only additional `to` parameter is set' => [
            'callback' => [
                'to' => '123',
            ],
            'expectedType' => 'macro',
        ];

        yield 'type is not set; only additional `from` parameter is set' => [
            'callback' => [
                'from' => '123',
            ],
            'expectedType' => 'macro',
        ];

        yield 'type is not set; only additional `sms` parameter is set' => [
            'callback' => [
                'sms' => '123',
            ],
            'expectedType' => 'macro',
        ];
    }

    /**
     * @dataProvider getMicroAndMacroRequestData
     *
     * @throws WebToPayException
     */
    public function testValidateAndParseDataWithMicroType(array $callback, string $expectedType): void
    {
        $request = [
            'data' => 'some data',
        ];

        $callback = array_merge($callback, [
            'projectid' => 123,
        ]);

        $this->util->expects($this->once())
            ->method('decryptGCM')
            ->willReturn('zxc');
        $this->util->expects($this->once())
            ->method('parseHttpQuery')
            ->willReturn($callback);

        $result = $this->validator->validateAndParseData($request);

        $this->assertEquals($expectedType, $result['type']);
    }

    /**
     * Tests validateAndParseData method with callback data decoding
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     */
    public function testValidateAndParseDataWithDecoding(): void
    {
        $request = [
            'data' => 'abcdef',
            'sign' => 'qwerty',
            'ss1' => 'randomChecksum',
        ];
        $parsed = [
            'projectid' => 123,
            'someparam' => 'qwerty123',
            'type' => 'micro',
        ];

        $this->signer->expects($this->once())
            ->method('checkSign')
            ->with($request)
            ->willReturn(true);
        $this->util->expects($this->once())
            ->method('decodeSafeUrlBase64')
            ->with('abcdef')
            ->willReturn('zxc');
        $this->util->expects($this->once())
            ->method('parseHttpQuery')
            ->with('zxc')
            ->willReturn($parsed);

        $this->assertEquals($parsed, $this->validator->validateAndParseData($request));
    }

    /**
     * Tests validateAndParseData method with callback data decryption
     *
     * @throws WebToPayException
     * @throws WebToPay_Exception_Callback
     */
    public function testValidateAndParseDataWithDecryption(): void
    {
        $data = ['firstParam' => 'first', 'secondParam' => 'second', 'projectid' => 123, 'type' => 'macro'];
        $dataString = http_build_query($data);
        $urlSafeEncodedString = 'ASdzxc+awej_lqkweQWesa==';
        $encryptedDataString = 'ASdzxc+awej_lqkweQWesa==';
        $request = ['data' => $encryptedDataString];

        $this->util->expects($this->once())
            ->method('decodeSafeUrlBase64')
            ->with($urlSafeEncodedString)
            ->willReturn($encryptedDataString);
        $this->util->expects($this->once())
            ->method('decryptGCM')
            ->with($encryptedDataString, self::PROJECT_PASSWORD)
            ->willReturn($dataString);
        $this->util->expects($this->once())
            ->method('parseHttpQuery')
            ->with($dataString)
            ->willReturn($data);

        $this->assertEquals($data, $this->validator->validateAndParseData($request));
    }

    /**
     * Tests checkExpectedFields method - it should throw exception (only)
     * when some values are not as expected or unspecified
     */
    public function testCheckExpectedFields(): void
    {
        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                [
                    'abc' => '123',
                    'def' => '456',
                ],
                [
                    'def' => 456,
                ]
            );
        } catch (WebToPayException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNull($exception);

        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                [
                    'abc' => '123',
                    'def' => '456',
                ],
                [
                    'abc' => '123',
                    'non-existing' => '789',
                ]
            );
        } catch (WebToPayException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNotNull($exception);

        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                [
                    'abc' => '123',
                    'def' => '456',
                ],
                [
                    'abc' => '1234',
                ]
            );
        } catch (WebToPayException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNotNull($exception);
    }
}
