<?php

declare(strict_types=1);

/**
 * A helper tool for reading environment variables
 *
 * @since 3.1.0
 */
class WebToPay_EnvReader
{
    /**
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function getAsString(string $key, string $default = null): ?string
    {
        if (!empty($_ENV[$key])) {
            return (string)$_ENV[$key];
        }

        $value = (string)getenv($key);

        return !empty($value)
            ? $value
            : $default;
    }
}
