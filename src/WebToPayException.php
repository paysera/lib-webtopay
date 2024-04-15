<?php

/**
 * Base exception class for all exceptions in this library
 */
class WebToPayException extends Exception
{
    /**
     * Missing field.
     */
    public const E_MISSING = 1;

    /**
     * Invalid field value.
     */
    public const E_INVALID = 2;

    /**
     * Max length exceeded.
     */
    public const E_MAXLEN = 3;

    /**
     * Regexp for field value doesn't match.
     */
    public const E_REGEXP = 4;

    /**
     * Missing or invalid user given parameters.
     */
    public const E_USER_PARAMS = 5;

    /**
     * Logging errors
     */
    public const E_LOG = 6;

    /**
     * SMS answer errors
     */
    public const E_SMS_ANSWER = 7;

    /**
     * Macro answer errors
     */
    public const E_STATUS = 8;

    /**
     * Library errors - if this happens, bug-report should be sent; also you can check for newer version
     */
    public const E_LIBRARY = 9;

    /**
     * Errors in remote service - it returns some invalid data
     */
    public const E_SERVICE = 10;

    /**
     * Deprecated usage errors
     */
    public const E_DEPRECATED_USAGE = 11;

    /**
     * @var string|bool
     */
    protected $fieldName = false;

    /**
     * Sets field which failed
     */
    public function setField(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    /**
     * Gets field which failed
     *
     * @return string|bool
     */
    public function getField()
    {
        return $this->fieldName;
    }
}
