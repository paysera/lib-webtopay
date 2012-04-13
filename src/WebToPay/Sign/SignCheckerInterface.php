<?php

/**
 * Interface for sign checker
 */
interface WebToPay_Sign_SignCheckerInterface {

    /**
     * Checks whether request is signed properly
     *
     * @param array $request
     *
     * @return boolean
     */
    public function checkSign(array $request);
}