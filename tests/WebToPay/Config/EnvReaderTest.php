<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class WebToPay_EnvReaderTest extends TestCase
{
    private const TEST_VAR_1 = 'testVar1';
    private const TEST_VAR_1_VALUE = 'testValue1';
    private const TEST_VAR_2 = 'testVar2';
    private const TEST_VAR_2_VALUE = 'testValue2';
    private const TEST_VAR_3 = 'testVar3';
    private const TEST_VAR_3_VALUE = 'testDefaultValue';
    private const TEST_NULL_VAR = 'nullVar';

    private WebToPay_EnvReader $envReader;

    protected function setUp(): void
    {
        $this->envReader = new WebToPay_EnvReader();

        $_ENV[self::TEST_VAR_1] = self::TEST_VAR_1_VALUE;
        putenv(self::TEST_VAR_2 . '=' . self::TEST_VAR_2_VALUE);
    }

    /**
     * @dataProvider getAsArrayData
     */
    public function testGetAsString(string $key, ?string $expected, ?string $default = null): void
    {
        $this->assertEquals($expected, $this->envReader->getAsString($key, $default));
    }

    public function getAsArrayData(): iterable
    {

        yield 'read from $_ENV' => [
            self::TEST_VAR_1,
            self::TEST_VAR_1_VALUE,
        ];

        yield 'read from getenv()' => [
            self::TEST_VAR_2,
            self::TEST_VAR_2_VALUE,
        ];

        yield 'with default' => [
            self::TEST_VAR_3,
            self::TEST_VAR_3_VALUE,
            self::TEST_VAR_3_VALUE,
        ];

        yield 'not exists' => [
            self::TEST_NULL_VAR,
            null,
        ];
    }

    public function tearDown(): void
    {
        unset($_ENV[self::TEST_VAR_1]);
        putenv(self::TEST_VAR_2 . '=');
    }
}
