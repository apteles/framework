<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ApTeles\Database\StringSanitize;

class StringSanitizeTest extends TestCase
{
    public function testItCanTransformStringInSQLDataValid()
    {
        $data1 = new StringSanitize('1970-12-31');

        $this->assertEquals("'1970-12-31'", $data1->transform());
    }
}
