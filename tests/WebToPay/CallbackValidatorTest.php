<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_CallbackValidator
 */
class WebToPay_CallbackValidatorTest extends TestCase {

    const PROJECT_PASSWORD = 'pass';

    /**
     * @var WebToPay_Sign_SignCheckerInterface
     */
    protected $signer;

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * @var WebToPay_CallbackValidator
     */
    protected $validator;

    /**
     * Sets up this test
     */
    public function setUp() {
        $this->signer = $this->createMock('WebToPay_Sign_SignCheckerInterface');
        $this->util = $this->createMock(
            'WebToPay_Util',
            array('decodeSafeUrlBase64', 'parseHttpQuery', 'decryptGCM')
        );
        $this->validator = new WebToPay_CallbackValidator(123, $this->signer, $this->util, self::PROJECT_PASSWORD);
    }

    /**
     * Exception should be thrown on invalid sign
     */
    public function testValidateAndParseDataWithInvalidSign() {
        $this->expectException(WebToPay_Exception_Callback::class);
        $request = array('data' => 'abcdef', 'sign' => 'qwerty');

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(false));
        $this->util->expects($this->never())->method($this->anything());

        $this->validator->validateAndParseData($request);
    }

    /**
     * Exception should be thrown if project ID does not match expected one
     */
    public function testValidateAndParseDataWithInvalidProject() {
        $this->expectException(WebToPay_Exception_Callback::class);
      
        $request = array('data' => 'abcdef', 'sign' => 'qwerty', 'ss1' => 'randomChecksum');
        $parsed = array('projectid' => 456);

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->once())->method('decodeSafeUrlBase64')->with('abcdef')->will($this->returnValue('zxc'));
        $this->util->expects($this->once())->method('parseHttpQuery')->with('zxc')->will($this->returnValue($parsed));

        $this->validator->validateAndParseData($request);
    }

    /**
     * Tests validateAndParseData method with callback data decoding
     */
    public function testValidateAndParseDataWithDecoding() {
        $request = array('data' => 'abcdef', 'sign' => 'qwerty', 'ss1' => 'randomChecksum');
        $parsed = array('projectid' => 123, 'someparam' => 'qwerty123', 'type' => 'micro');

        $this->assertArrayHasKey('ss1', $request);

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->once())->method('decodeSafeUrlBase64')->with('abcdef')->will($this->returnValue('zxc'));
        $this->util->expects($this->once())->method('parseHttpQuery')->with('zxc')->will($this->returnValue($parsed));

        $this->assertEquals($parsed, $this->validator->validateAndParseData($request));
    }

    /**
     * Tests validateAndParseData method with callback data decryption
     */
    public function testValidateAndParseDataWithDecryption() {
        $data = ['firstParam' => 'first', 'secondParam' => 'second', 'projectid' => 123, 'type' => 'macro'];
        $dataString = http_build_query($data);
        $encryptedDataString = 'ASdzxcawejlqkweQWesa==';
        $request = array('data' => $encryptedDataString, 'sign' => 'qwerty');

        $this->assertArrayNotHasKey('ss1', $request);
        $this->assertArrayNotHasKey('ss2', $request);

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->at(0))->method('decryptGCM')->with($encryptedDataString, self::PROJECT_PASSWORD)->will($this->returnValue($dataString));
        $this->util->expects($this->at(1))->method('parseHttpQuery')->with($dataString)->will($this->returnValue($data));

        $this->assertEquals($data, $this->validator->validateAndParseData($request));
    }

    /**
     * Exception should be thrown if decryption has failed
     *
     * @expectedException WebToPay_Exception_Callback
     */
    public function testValidateAndParseDataWithDecryptionFailure() {
        $encryptedDataString = 'ASdzxcawejlqkweQWesa==';
        $request = array('data' => $encryptedDataString, 'sign' => 'qwerty');

        $this->assertArrayNotHasKey('ss1', $request);
        $this->assertArrayNotHasKey('ss2', $request);

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->at(0))->method('decryptGCM')->with($encryptedDataString, self::PROJECT_PASSWORD)->will($this->returnValue(false));

        $this->validator->validateAndParseData($request);
    }

    /**
     * Tests checkExpectedFields method - it should throw exception (only) when some valus are not as expected or
     * unspecified
     */
    public function testCheckExpectedFields() {
        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                array(
                    'abc' => '123',
                    'def' => '456',
                ),
                array(
                    'def' => 456,
                )
            );
        }  catch (WebToPayException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNull($exception);

        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                array(
                    'abc' => '123',
                    'def' => '456',
                ),
                array(
                    'abc' => '123',
                    'non-existing' => '789',
                )
            );
        } catch (WebToPayException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNotNull($exception);

        $exception = null;
        try {
            $this->validator->checkExpectedFields(
                array(
                    'abc' => '123',
                    'def' => '456',
                ),
                array(
                    'abc' => '1234',
                )
            );
        } catch (WebToPayException $exception) {
            // empty block, $exception variable is set to exception object
        }
        $this->assertNotNull($exception);
    }
}
