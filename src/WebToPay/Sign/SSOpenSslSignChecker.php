<?php

declare(strict_types=1);

/**
 * Checks SS2 signature. Depends on SSL functions
 */
class WebToPay_Sign_SSOpenSslSignChecker implements WebToPay_Sign_SignCheckerInterface
{
    public const SIGN_TYPE_TO_HASH_ALGO_MAP = [
        'ss2' => OPENSSL_ALGO_SHA1,
        'ss3' => OPENSSL_ALGO_SHA256,
    ];
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
        $signTypeKeysAvailable = array_intersect(
            array_keys(self::SIGN_TYPE_TO_HASH_ALGO_MAP),
            array_keys($request)
        );
        if (!isset($request['data']) || count($signTypeKeysAvailable) === 0) {
            throw new WebToPay_Exception_Callback('Not enough parameters in callback. Possible version mismatch');
        }

        $signTypeKey = end($signTypeKeysAvailable);
        $ssValue = $this->util->decodeSafeUrlBase64($request[$signTypeKey]);
        $ok = openssl_verify(
            $request['data'],
            $ssValue,
            $this->publicKey,
            self::SIGN_TYPE_TO_HASH_ALGO_MAP[$signTypeKey]
        );

        if ($ok !== 1) {
            $error = openssl_error_string();
            if ($error !== false) {
                throw new WebToPay_Exception_Callback('OpenSLL ' . strtoupper($signTypeKey) . ' sign check error: ' . $error);
            }
        }

        return $ok === 1;
    }
}
