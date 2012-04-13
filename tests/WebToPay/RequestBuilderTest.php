<?php

/**
 * Test for class WebToPay_RequestBuilder
 */
class WebToPay_RequestBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * @var WebToPay_RequestBuilder
     */
    protected $builder;

    /**
     * Sets up this test
     */
    public function setUp() {
        $this->util = $this->getMock('WebToPay_Util', array('encodeSafeUrlBase64'));
        $this->builder = new WebToPay_RequestBuilder(123, 'secret', $this->util);
    }

    /**
     * Test build request when no orderid is passed
     *
     * @expectedException WebToPay_Exception_Validation
     */
    public function testBuildRequestWithNoOrderId() {
        $this->builder->buildRequest(array(
            'accepturl' => 'http://local.test/',
            'cancelurl' => 'http://local.test/',
            'callbackurl' => 'http://local.test/',
        ));
    }

    /**
     * Test build request when invalid currency is passed
     *
     * @expectedException WebToPay_Exception_Validation
     */
    public function testBuildRequestWithInvalidCurrency() {
        $this->builder->buildRequest(array(
            'orderid' => 123,
            'accepturl' => 'http://local.test/',
            'cancelurl' => 'http://local.test/',
            'callbackurl' => 'http://local.test/',
            'currency' => 'litai',
        ));
    }

    /**
     * Tests buildRequest method
     */
    public function testBuildRequest() {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with(
                'orderid=123&accepturl=http%3A%2F%2Flocal.test%2F&cancelurl=http%3A%2F%2Flocal.test%2F'
                    . '&callbackurl=http%3A%2F%2Flocal.test%2F&amount=100&some-other-parameter=abc'
                    . '&version=1.6&projectid=123'
            )
            ->will($this->returnValue('encoded'));
        $this->assertEquals(
            array('data' => 'encoded', 'sign' => md5('encodedsecret')),
            $this->builder->buildRequest(array(
                'orderid' => 123,
                'accepturl' => 'http://local.test/',
                'cancelurl' => 'http://local.test/',
                'callbackurl' => 'http://local.test/',
                'amount' => 100,
                'some-other-parameter' => 'abc',
            ))
        );
    }

    /**
     * Tests buildRepeatRequest method
     */
    public function testBuildRepeatRequest() {
        $this->util
            ->expects($this->once())
            ->method('encodeSafeUrlBase64')
            ->with('orderid=123&version=1.6&projectid=123&repeat_request=1')
            ->will($this->returnValue('encoded'));
        $this->assertEquals(
            array('data' => 'encoded', 'sign' => md5('encodedsecret')),
            $this->builder->buildRepeatRequest(123)
        );
    }
}