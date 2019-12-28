<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ApTeles\Database\NullSanitize;

class NullSanitizeTest extends TestCase
{
    public function testItCanTransformNullInSQLDataValid()
    {
        $data1 = new NullSanitize('NULL');
        $data2 = new NullSanitize('null');

        $this->assertEquals("'NULL'", $data1->transform());
        $this->assertEquals("'NULL'", $data2->transform());
    }
}
