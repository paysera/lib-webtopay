<?php

/**
 * Test for class WebToPay_CallbackValidator
 */
class WebToPay_CallbackValidatorTest extends PHPUnit_Framework_TestCase {

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
        $this->signer = $this->getMock('WebToPay_Sign_SignCheckerInterface');
        $this->util = $this->getMock('WebToPay_Util', array('decodeSafeUrlBase64', 'parseHttpQuery'));
        $this->validator = new WebToPay_CallbackValidator(123, $this->signer, $this->util);
    }

    /**
     * Exception should be thrown on invalid sign
     *
     * @expectedException WebToPay_Exception_Callback
     */
    public function testValidateAndParseDataWithInvalidSign() {
        $request = array('data' => 'abcdef', 'sign' => 'qwerty');

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(false));
        $this->util->expects($this->never())->method($this->anything());

        $this->validator->validateAndParseData($request);
    }

    /**
     * Exception should be thrown if project ID does not match expected one
     *
     * @expectedException WebToPay_Exception_Callback
     */
    public function testValidateAndParseDataWithInvalidProject() {
        $request = array('data' => 'abcdef', 'sign' => 'qwerty');
        $parsed = array('projectid' => 456);

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->at(0))->method('decodeSafeUrlBase64')->with('abcdef')->will($this->returnValue('zxc'));
        $this->util->expects($this->at(1))->method('parseHttpQuery')->with('zxc')->will($this->returnValue($parsed));

        $this->validator->validateAndParseData($request);
    }

    /**
     * Tests validateAndParseData method
     */
    public function testValidateAndParseData() {
        $request = array('data' => 'abcdef', 'sign' => 'qwerty');
        $parsed = array('projectid' => 123, 'someparam' => 'qwerty123', 'type' => 'micro');

        $this->signer->expects($this->once())->method('checkSign')->with($request)->will($this->returnValue(true));
        $this->util->expects($this->at(0))->method('decodeSafeUrlBase64')->with('abcdef')->will($this->returnValue('zxc'));
        $this->util->expects($this->at(1))->method('parseHttpQuery')->with('zxc')->will($this->returnValue($parsed));

        $this->assertEquals($parsed, $this->validator->validateAndParseData($request));
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