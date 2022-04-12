<?php

use PHPUnit\Framework\TestCase;

/**
 * Test for class WebToPay_Util
 */
class WebToPay_UtilTest extends TestCase {

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * Sets up this test
     */
    public function setUp(): void {
        $this->util = new WebToPay_Util();
    }

    /**
     * Tests decodeSafeUrlBase64. Must work on non-url-safe base64 too
     */
    public function testDecodeSafeUrlBase64() {
        $this->assertEquals(
            "\x33\0\1\2\3\4\5\x92\xFF\xAA\0\1\2\3\4\5\x92\xFE\xAA\xEE",
            $this->util->decodeSafeUrlBase64('MwABAgMEBZL_qgABAgMEBZL-qu4=')
        );
        $this->assertEquals(
            "\x33\0\1\2\3\4\5\x92\xFF\xAA\0\1\2\3\4\5\x92\xFE\xAA\xEE",
            $this->util->decodeSafeUrlBase64('MwABAgMEBZL/qgABAgMEBZL+qu4=')
        );
    }

    /**
     * Tests encodeSafeUrlBase64
     */
    public function testEncodeSafeUrlBase64() {
        $this->assertEquals(
            'MwABAgMEBZL_qgABAgMEBZL-qu4=',
            $this->util->encodeSafeUrlBase64("\x33\0\1\2\3\4\5\x92\xFF\xAA\0\1\2\3\4\5\x92\xFE\xAA\xEE")
        );
    }

    /**
     * Tests that encode and decode are compatible
     */
    public function testEncodeAndDecodeAreCompatible() {
        $values = array(
            'Some long string with UTF-8 ąččėę проверка',
            "Some binary symbols \0\1\3\xFF\xE0\xD0\xC0\xB0\xA0\x90\x10\x0A ",
            'Some other symbols %=?/-_)22Wq',
        );
        foreach ($values as $text) {
            $this->assertEquals(
                $text,
                $this->util->decodeSafeUrlBase64($this->util->encodeSafeUrlBase64($text))
            );
        }
    }

    /**
     * Tests parseHttpQuery. Must work with and without gpc_magic_quotes
     */
    public function testParseHttpQuery() {
        $this->assertEquals(
            array(
                'param1' => 'some string',
                'param2' => 'special symbols !!%(@_-+/=',
                'param3' => 'slashes \\\'"',
            ),
            $this->util->parseHttpQuery(
                'param1=some+string&param2=special+symbols+%21%21%25%28%40_-%2B%2F%3D&param3=slashes+%5C%27%22'
            )
        );
    }

    public function testDecryptGCM()
    {
        $key = 'encryption_key';
        $dataString = http_build_query(
            array(
                'firstParam' => 'first',
                'secondParam' => 'second',
            )
        );
        $encryptedData = $this->getEncryptedData($dataString, $key);

        $this->assertEquals(
            $dataString,
            $this->util->decryptGCM($encryptedData, $key)
        );
    }

    public function testDecryptGCMFailed()
    {
        $dataString = http_build_query(
            array(
                'firstParam' => 'first',
                'secondParam' => 'second',
            )
        );
        $encryptedData = $this->getEncryptedData($dataString, 'encryption_key');

        $this->assertNull($this->util->decryptGCM($encryptedData, 'wrong_key'));
    }

    /**
     * @param string $data - callback data string to encrypt
     *
     * @return string - encrypted string
     */
    private function getEncryptedData($data, $key)
    {
        // move to util test
        $ivLength = openssl_cipher_iv_length(WebToPay_Util::GCM_CIPHER);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $tag = '';

        $ciphertext = openssl_encrypt(
            $data,
            WebToPay_Util::GCM_CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            WebToPay_Util::GCM_AUTH_KEY_LENGTH
        );

        return base64_encode($iv.$ciphertext.$tag);
    }
}
