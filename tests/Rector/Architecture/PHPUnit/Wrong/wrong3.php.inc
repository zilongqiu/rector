<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class MyTestCase extends TestCase
{
    public function provideWrongToFixedFiles(): array
    {
        return [[__DIR__ . '/Wrong/wrong.php.inc', __DIR__ . '/Correct/correct.php.inc']];
    }
}
