<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPay_EnvReaderTest extends TestCase
{
    /**
     * @dataProvider getAsArrayData
     */
    public function testGetAsString(string $key, ?string $expected, ?string $actual): void
    {
        $this->assertEquals($expected, $actual);
    }

    public function getAsArrayData(): iterable
    {
        $envReader = new WebToPay_EnvReader();

        $key = 'testVar';
        unset($_ENV[$key]);
        putenv($key . '=');

        yield 'not exists' => [
            $key,
            null,
            $envReader->getAsString($key),
        ];

        $expected = 'defaultValue';

        yield 'with default' => [
            $key,
            $expected,
            $envReader->getAsString($key, $expected),
        ];

        $expected = 'testValue1';

        $_ENV[$key] = $expected;

        yield 'read from $_ENV' => [
            $key,
            $expected,
            $envReader->getAsString($key),
        ];

        unset($_ENV[$key]);

        putenv(sprintf('%s=%s', $key, $expected));

        yield 'read from getenv()' => [
            $key,
            $expected,
            $envReader->getAsString($key),
        ];
    }
}
