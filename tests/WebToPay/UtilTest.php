<?php

/**
 * Test for class WebToPay_Util
 */
class WebToPay_UtilTest extends PHPUnit_Framework_TestCase {

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * Sets up this test
     */
    public function setUp() {
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
}