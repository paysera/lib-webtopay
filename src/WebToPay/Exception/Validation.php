<?php

/**
 * Raised on validation error in passed data when building the request
 */
class WebToPay_Exception_Validation extends WebToPayException {

    public function __construct($message, $code = 0, $field = null, Exception $previousException = null) {
        parent::__construct($message, $code, $previousException);
        if ($field) {
            $this->setField($field);
        }
    }
}