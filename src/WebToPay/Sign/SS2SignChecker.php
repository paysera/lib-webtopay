<?php

declare(strict_types=1);

/**
 * Checks SS2 signature. Depends on SSL functions
 */
class WebToPay_Sign_SS2SignChecker implements WebToPay_Sign_SignCheckerInterface
{
    protected string $publicKey;

    protected WebToPay_Util $util;

    /**
     * Constructs object
     */
    public function __construct(string $publicKey, WebToPay_Util $util)
    {
        $this->publicKey = $publicKey;
        $this->util = $util;
    }

    /**
     * Checks signature
     *
     * @param array<string, mixed> $request
     *
     * @return bool
     *
     * @throws WebToPay_Exception_Callback
     */
    public function checkSign(array $request): bool
    {
        if (!isset($request['data']) || !isset($request['ss2'])) {
            throw new WebToPay_Exception_Callback('Not enough parameters in callback. Possible version mismatch');
        }

        $ss2 = $this->util->decodeSafeUrlBase64($request['ss2']);
        $ok = openssl_verify($request['data'], $ss2, $this->publicKey);

        return $ok === 1;
    }
}
