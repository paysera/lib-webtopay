<?php

declare(strict_types=1);

/**
 * Interface for sign checker
 */
interface WebToPay_Sign_SignCheckerInterface
{
    /**
     * Checks whether request is signed properly
     *
     * @param array<string, mixed> $request
     *
     * @return boolean
     */
    public function checkSign(array $request): bool;
}
