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
     * Exception should be thrown on invalid sign
     */
    public function testValidateAndParseDataWithInvalidSign(): void
    {
        $this->expectException(WebToPay_Exception_Callback::class);
        $request = [
            'data' => 'abcdef',
            'sign' => 'qwerty',
            'ss1' => 'zxcvb',
        ];

        $this->signer->expects($this->once())
            ->method('checkSign')
            ->with($request)
            ->willReturn(false);
        $this->util->expects($this->never())->method($this->anything());

        $this->validator->validateAndParseData($request);
    }

    /**
     * Exception should be thrown if project ID does not match expected
     */
    public function testValidateAndParseDataWithInvalidProject(): void
    {
        $this->expectException(WebToPay_Exception_Callback::class);

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

        $this->validator->validateAndParseData($request);
    }

    /**
     * Tests validateAndParseData method with callback data decoding
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
     * Exception should be thrown if decryption has failed
     */
    public function testValidateAndParseDataWithDecryptionFailure(): void
    {
        $this->expectException(WebToPay_Exception_Callback::class);

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
            ->willReturn(null);

        $this->validator->validateAndParseData($request);
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
