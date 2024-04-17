<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class StaticMethods_BaseTest extends TestCase
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
