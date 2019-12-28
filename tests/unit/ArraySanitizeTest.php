<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ApTeles\Database\ArraySanitize;

class ArraySanitizeTest extends TestCase
{
    public function testItCanTransformArrayInSQLDataValid()
    {
        $data1 = new ArraySanitize(['18,20,30']);
        $data2 = new ArraySanitize([18,20,30]);

        $this->assertEquals("('18,20,30')", $data1->transform());
        $this->assertEquals("(18,20,30)", $data2->transform());
    }
}
