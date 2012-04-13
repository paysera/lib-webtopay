<?php

/**
 * Checks SS2 signature. Depends on SSL functions
 */
class WebToPay_Sign_SS2SignChecker implements WebToPay_Sign_SignCheckerInterface {

    /**
     * @var string
     */
    protected $publicKey;

    /**
     * @var WebToPay_Util
     */
    protected $util;

    /**
     * Constructs object
     *
     * @param string        $publicKey
     * @param WebToPay_Util $util
     */
    public function __construct($publicKey, WebToPay_Util $util) {
        $this->publicKey = $publicKey;
        $this->util = $util;
    }

    /**
     * Checks signature
     *
     * @param array $request
     *
     * @return boolean
     *
     * @throws WebToPay_Exception_Callback
     */
    public function checkSign(array $request) {
        if (!isset($request['data']) || !isset($request['ss2'])) {
            throw new WebToPay_Exception_Callback('Not enough parameters in callback. Possible version mismatch');
        }

        $ss2 = $this->util->decodeSafeUrlBase64($request['ss2']);
        $ok = openssl_verify($request['data'], $ss2, $this->publicKey);
        return $ok === 1;
    }
}