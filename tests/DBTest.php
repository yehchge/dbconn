<?php declare(strict_types=1);

require __DIR__.'/../src/DB.php';

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{

    /**
     * @covers \DB
     */
    public function test_world(): void
    {
        $this->assertEquals('Hello World'.PHP_EOL, DB::world());
    }
}