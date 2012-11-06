<?php

/**
 * Base exception class for all exceptions in this library
 */
class WebToPayException extends Exception {

    /**
     * Missing field.
     */
    const E_MISSING = 1;

    /**
     * Invalid field value.
     */
    const E_INVALID = 2;

    /**
     * Max length exceeded.
     */
    const E_MAXLEN = 3;

    /**
     * Regexp for field value doesn't match.
     */
    const E_REGEXP = 4;

    /**
     * Missing or invalid user given parameters.
     */
    const E_USER_PARAMS = 5;

    /**
     * Logging errors
     */
    const E_LOG = 6;

    /**
     * SMS answer errors
     */
    const E_SMS_ANSWER = 7;

    /**
     * Macro answer errors
     */
    const E_STATUS = 8;

    /**
     * Library errors - if this happens, bug-report should be sent; also you can check for newer version
     */
    const E_LIBRARY = 9;

    /**
     * Errors in remote service - it returns some invalid data
     */
    const E_SERVICE = 10;
    
    /**
     * Deprecated usage errors
     */
    const E_DEPRECATED_USAGE = 11;

    /**
     * @var string|boolean
     */
    protected $fieldName = false;

    /**
     * Sets field which failed
     *
     * @param string $fieldName
     */
    public function setField($fieldName) {
        $this->fieldName = $fieldName;
    }

    /**
     * Gets field which failed
     *
     * @return string|boolean false
     */
    public function getField() {
        return $this->fieldName;
    }
}