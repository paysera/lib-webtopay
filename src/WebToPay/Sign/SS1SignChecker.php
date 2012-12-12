<?php

/**
 * Sign checker which checks SS1 signature. SS1 does not depend on SSL functions
 */
class WebToPay_Sign_SS1SignChecker implements WebToPay_Sign_SignCheckerInterface {

    /**
     * @var string
     */
    protected $projectPassword;

    /**
     * Constructs object
     *
     * @param string $projectPassword
     */
    public function __construct($projectPassword) {
        $this->projectPassword = $projectPassword;
    }

    /**
     * Check for SS1, which is not depend on openssl functions.
     *
     * @param array $request
     *
     * @return boolean
     *
     * @throws WebToPay_Exception_Callback
     */
    public function checkSign(array $request) {
        if (!isset($request['data']) || !isset($request['ss1'])) {
            throw new WebToPay_Exception_Callback('Not enough parameters in callback. Possible version mismatch');
        }

        return md5($request['data'] . $this->projectPassword) === $request['ss1'];
    }
}