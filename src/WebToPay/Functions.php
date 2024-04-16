<?php

declare(strict_types=1);

class WebToPay_Functions
{
    public static function function_exists(string $functionName): bool
    {
        return \function_exists($functionName);
    }
}
