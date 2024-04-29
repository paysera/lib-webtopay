<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        $container = Mockery::getContainer();
        if ($container !== null) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }
        Mockery::close();
    }
}
