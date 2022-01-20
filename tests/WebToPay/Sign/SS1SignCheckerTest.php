<?php

/**
 * Test for class WebToPay_Sign_SS1SignChecker
 */
class WebToPay_SS1SignCheckerTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var WebToPay_Sign_SS1SignChecker
     */
    protected $signChecker;

    /**
     * Sets up this test
     */
    protected function setUp(): void {
        $this->signChecker = new WebToPay_Sign_SS1SignChecker('secret');
    }

    /**
     * Should throw exception if not all required parameters are passed
     */
    public function testCheckSignWithoutInformation() {
        $this->expectException(WebToPay_Exception_Callback::class);
        $this->signChecker->checkSign(array(
            'projectid' => '123',
            'ss1' => 'asd',
            'ss2' => 'zxc',
        ));
    }

    /**
     * Tests checkSign
     */
    public function testCheckSign() {
        $this->assertTrue($this->signChecker->checkSign(array(
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret'),
            'ss2' => 'bad-ss2',
        )));
        $this->assertFalse($this->signChecker->checkSign(array(
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret1'),
            'ss2' => 'bad-ss2',
        )));
    }
}