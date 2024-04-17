<?php

declare(strict_types=1);

/**
 * The class is used for manipulating with behavior of functions in the global namespace.
 * It is used for testing purposes. No payload.
 *
 * @codeCoverageIgnore
 */
class WebToPay_Functions
{
    public static function function_exists(string $functionName): bool
    {
        return \function_exists($functionName);
    }

    public static function headers_sent(): bool
    {
        return \headers_sent();
    }
}
