<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ApTeles\Database\BooleanSanitize;

class BooleanSanitizeTest extends TestCase
{
    public function testItCanTransformBooleanInSQLDataValid()
    {
        $data1 = new BooleanSanitize(true);
        $data2 = new BooleanSanitize(false);

        $this->assertEquals("TRUE", $data1->transform());
        $this->assertEquals("FALSE", $data2->transform());
    }
}
