<?php
declare(strict_types=1);

use ApTeles\Database\Filter;
use PHPUnit\Framework\TestCase;
use ApTeles\Database\NullSanitize;
use ApTeles\Database\ArraySanitize;
use ApTeles\Database\StringSanitize;
use ApTeles\Database\BooleanSanitize;

class FilterTest extends TestCase
{
    public function testItCanGenerateAFilterTOSQLOperationsWithArrayValues()
    {
        $valueArrayMock = $this->getMockBuilder(ArraySanitize::class)
                        ->setMethods(['transform'])
                            ->setConstructorArgs([[18,20,30]])
                            ->getMock();
        $valueArrayMock->expects($this->once())->method('transform')->willReturn('(18,20,30)');

        //$valueStringMock = $this->getValueSanitize(StringSanitize::class, ['admin']);
        //$valueStringMock->expects($this->once())->method('transform')->willReturn('admin');

        $filter = new Filter('clients_id', 'in', $valueArrayMock);
        // $filter2 = new Filter('permission', '=', $valueStringMock);

        $this->assertEquals('clients_id in (18,20,30)', $filter->dump());
        //  $this->assertEquals("permission = 'admin'", $filter2->dump());
    }


    public function testItCanGenerateAFilterTOSQLOperationsWithStringValues()
    {
        $valueStringMock = $this->getMockBuilder(StringSanitize::class)
                        ->setMethods(['transform'])
                            ->setConstructorArgs(['admin'])
                            ->getMock();
        $valueStringMock->expects($this->once())->method('transform')->willReturn("'admin'");


        $filter = new Filter('permission_name', '=', $valueStringMock);

        $this->assertEquals("permission_name = 'admin'", $filter->dump());
    }

    public function testItCanGenerateAFilterTOSQLOperationsWithBooleanValues()
    {
        $valueBooleanTrueMock = $this->getMockBuilder(BooleanSanitize::class)
                        ->setMethods(['transform'])
                            ->setConstructorArgs([true])
                            ->getMock();
        $valueBooleanTrueMock->expects($this->once())->method('transform')->willReturn("TRUE");

        $valueBooleanFalseMock = $this->getMockBuilder(BooleanSanitize::class)
                        ->setMethods(['transform'])
                            ->setConstructorArgs([false])
                            ->getMock();
        $valueBooleanFalseMock->expects($this->once())->method('transform')->willReturn("FALSE");

        $filter1 = new Filter('admin', '=', $valueBooleanTrueMock);
        $filter2 = new Filter('admin', '=', $valueBooleanFalseMock);
        $this->assertEquals("admin = TRUE", $filter1->dump());
        $this->assertEquals("admin = FALSE", $filter2->dump());
    }

    public function testItCanGenerateAFilterTOSQLOperationsWithNullValues()
    {
        $valueBooleanTrueMock = $this->getMockBuilder(NullSanitize::class)
                        ->setMethods(['transform'])
                            ->setConstructorArgs([null])
                            ->getMock();
        $valueBooleanTrueMock->expects($this->once())->method('transform')->willReturn("NULL");

        $filter1 = new Filter('is_admin', '=', $valueBooleanTrueMock);
        $this->assertEquals("is_admin = NULL", $filter1->dump());
    }
}
