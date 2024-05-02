<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_Sign_SS1SignChecker
 */
class WebToPay_Sign_SS1SignCheckerTest extends TestCase
{
    /**
     * @var WebToPay_Sign_SS1SignChecker
     */
    protected $signChecker;

    /**
     * Sets up this test
     */
    public function setUp(): void
    {
        $this->signChecker = new WebToPay_Sign_SS1SignChecker('secret');
    }

    /**
     * Should throw exception if not all required parameters are passed
     */
    public function testCheckSignWithoutInformation()
    {
        $this->expectException(WebToPay_Exception_Callback::class);
        $this->signChecker->checkSign([
            'projectid' => '123',
            'ss1' => 'asd',
            'ss2' => 'zxc',
        ]);
    }

    /**
     * Tests checkSign
     */
    public function testCheckSign()
    {
        $this->assertTrue($this->signChecker->checkSign([
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret'),
            'ss2' => 'bad-ss2',
        ]));
        $this->assertFalse($this->signChecker->checkSign([
            'data' => 'encodedData',
            'ss1' => md5('encodedDatasecret1'),
            'ss2' => 'bad-ss2',
        ]));
    }
}
