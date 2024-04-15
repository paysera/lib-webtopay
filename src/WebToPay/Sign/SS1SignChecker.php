<?php

/**
 * Sign checker which checks SS1 signature. SS1 does not depend on SSL functions
 */
class WebToPay_Sign_SS1SignChecker implements WebToPay_Sign_SignCheckerInterface
{
    protected string $projectPassword;

    /**
     * Constructs object
     */
    public function __construct(string $projectPassword)
    {
        $this->projectPassword = $projectPassword;
    }

    /**
     * Check for SS1, which is not depend on openssl functions.
     *
     * @param array<string, mixed> $request
     *
     * @return bool
     *
     * @throws WebToPay_Exception_Callback
     */
    public function checkSign(array $request): bool
    {
        if (!isset($request['data']) || !isset($request['ss1'])) {
            throw new WebToPay_Exception_Callback('Not enough parameters in callback. Possible version mismatch');
        }

        return md5($request['data'] . $this->projectPassword) === $request['ss1'];
    }
}
