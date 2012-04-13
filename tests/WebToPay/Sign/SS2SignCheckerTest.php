<?php

/**
 * Test for class WebToPay_Sign_SS2SignChecker
 */
class WebToPay_Sign_SS2SignCheckerTest extends PHPUnit_Framework_TestCase {

    /**
     * Randomly generated private and public keys pair
     *
     * @var string
     */
    protected static $privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEAv0W1m/xjqMcRmwmiJ4M25J1CVMVr36MWC60jlk821nOcpkZP
Lse29IDyITBfmBqdL9TP1kDFSfWoHzIBzW7lZxj3J6aXuqHbNtEpdt3tqxEtRsi4
XPaYjnWQbGxXlZwPJkWuRvyqeTZAsfpJAGFIZlxlr5pAPER7NTwozR6kWfl8U96/
zIuV9PMIJiIg2rnUQaSN6wZi876aV5oqlq3Ha+p32K9wAxFAx1FsJzOZT77rsjp3
h6riIAuPnW5Ut/LbJ5c/H+X6bGg3ytkk8KB6WH/7s1IG3gHc08EcYjgZVeZrFKat
RYXs8frLsnQPBeuZmQBFxBFUd8L+5vOZo7AP9wIDAQABAoIBAQClZMP7lC0hHrIs
nBHplN78pLdc0jHLehxwEFE7glfq7KHCbf2+d9fOaUn2RPwEbM8LMzxdCjkPEStF
flpsp74afk4JrVZ6fccvCYKPVKxVRk8ebCZvzJRya1ptRuodZor7Dzn6DDXlBnK+
86v4dibCzJbpV7q/4n+fstudMyfu2/xi7pIFTE1HiWHXsnZSank77oKIo6H/Seel
xwg3u1tapJPInRIBZTmegTJpKWQaegCpQCFEMGBgeRw9eKHXw8yZ0mdwJkLPuoUv
/tmyWIBGDWDUCMvlZiuAXGDXdhQt4ETBwgoHgC/7atxzqg/WPK25jR2lYl59zPDt
tN8EIqrBAoGBAN1XGFpGXPkd3WBLp1i+IWGVjdjf2ylUoQhA6JcTWQ1YSdVbJH2Q
yUAsoF8iu6YbZ4bZFRdPT+DIiDQ93DnqCW1Y0UV7mQ6VN0fQJvC6TS18wOBvbkPO
khEPqZKNPo1B88RJb6ZrxIbHr+wXgTpJm249AC5iy5Qi81WGjIjASx5xAoGBAN05
Rsa4gRahJzHopQ4lLMhBtu115U+r1j76s22ptmozSRzxM7IQBczRdzcXOcIOUpdi
DiLYdk5E3iA/SRtfKlLwpi9nuUekkokrnoJypky/WhEYPpRnZhtL7h4cdnRTY0/q
WFthxdi1r57lW0Ztfdcb8vn6hSF2mD+FueJmXRjnAoGAfdl6mEGvtVlcuNbrNNMO
SdzuBSTrCOn8kaPOW6/9j0/m0y/6ZIbBVwLIwK8QANdOGuctTc6jvUxn3URbBnbi
m1DH1Hj9QsRm5SceLPvQzA9F35acHGPEu3yrTw+ORGT+hFm46OgXmwbJKTUIHish
/CElDDrSQ81HjBZvq4WicJECgYEAncKKxowtAoZJ/T1692trVCQI366DqR1R2/fM
nRe6DmIkcY9Q3lquyDFYYuEdP1YXb/1tN0xGkepqvXRkHjDvbdZPrN67MmwaU9fX
Yg+AqJqNEEPJ3Osf1beAR9jkYHBXElZ8TC6deL2YUCgfv0m1xAEadUpCRmrch/BF
bz6whWECgYEAy0Fxp8CoQmbhWa1tiUHXKXzSk0zopfgAnnBIwGOXDRlCUO4Rtr0L
VA2Uhvw+6er+p32vvYfhyt3K7HSkCSVhpnxuTqoT8OJEXhmOwlAyvvjbs8Iqqeha
Uon3d0SR2JQoVzcsUNp2MrbZK1n4LoMqr4jWN91gNk+HDHvhfwyJFMY=
-----END RSA PRIVATE KEY-----';

    /**
     * @var string
     */
    protected static $publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv0W1m/xjqMcRmwmiJ4M2
5J1CVMVr36MWC60jlk821nOcpkZPLse29IDyITBfmBqdL9TP1kDFSfWoHzIBzW7l
Zxj3J6aXuqHbNtEpdt3tqxEtRsi4XPaYjnWQbGxXlZwPJkWuRvyqeTZAsfpJAGFI
Zlxlr5pAPER7NTwozR6kWfl8U96/zIuV9PMIJiIg2rnUQaSN6wZi876aV5oqlq3H
a+p32K9wAxFAx1FsJzOZT77rsjp3h6riIAuPnW5Ut/LbJ5c/H+X6bGg3ytkk8KB6
WH/7s1IG3gHc08EcYjgZVeZrFKatRYXs8frLsnQPBeuZmQBFxBFUd8L+5vOZo7AP
9wIDAQAB
-----END PUBLIC KEY-----';

    /**
     * @var WebToPay_Sign_SS1SignChecker
     */
    protected $signChecker;

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * Sets up this test
     */
    public function setUp() {
        $this->util = $this->getMock('WebToPay_Util', array('decodeSafeUrlBase64'));
        $this->signChecker = new WebToPay_Sign_SS2SignChecker(self::$publicKey, $this->util);
    }

    /**
     * Should throw exception if not all required parameters are passed
     *
     * @expectedException WebToPay_Exception_Callback
     */
    public function testCheckSignWithoutInformation() {
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
        $ss2 = null;
        $privateKey = openssl_pkey_get_private(self::$privateKey);
        openssl_sign('encodedData', $ss2, $privateKey);

        $this->util
            ->expects($this->once())
            ->method('decodeSafeUrlBase64')
            ->with('encoded-ss2')
            ->will($this->returnValue($ss2));

        $this->assertTrue($this->signChecker->checkSign(array(
            'data' => 'encodedData',
            'ss1' => 'bad-ss1',
            'ss2' => 'encoded-ss2',
        )));
    }

    /**
     * Tests checkSign with incorrect ss2
     */
    public function testCheckSignWithBadSignature() {
        $this->util
            ->expects($this->once())
            ->method('decodeSafeUrlBase64')
            ->with('encoded-ss2')
            ->will($this->returnValue('bad-ss2'));

        $this->assertFalse($this->signChecker->checkSign(array(
            'data' => 'encodedData',
            'ss1' => 'bad-ss1',
            'ss2' => 'encoded-ss2',
        )));
    }
}