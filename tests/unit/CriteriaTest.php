<?php
declare(strict_types=1);

use ApTeles\Database\Filter;
use ApTeles\Database\Criteria;
use PHPUnit\Framework\TestCase;
use ApTeles\Database\ArraySanitize;
use ApTeles\Database\StringSanitize;
use ApTeles\Database\Contracts\Expression;

class CriteriaTest extends TestCase
{
    private $criteria;

    public function setUp(): void
    {
        $this->criteria = new Criteria;
    }

    public function testItCanCreateFilterWithOROperator()
    {
        $mockValue1Sanitize = $this->getMockBuilder(StringSanitize::class)
            ->setConstructorArgs(['16'])->setMethods(['transform'])->getMock();
        $mockValue1Sanitize->expects($this->any())->method('transform')->willReturn('16');

        $mockValue2Sanitize = $this->getMockBuilder(StringSanitize::class)
        ->setConstructorArgs(['16'])->setMethods(['transform'])->getMock();
        $mockValue2Sanitize->expects($this->any())->method('transform')->willReturn('60');

        $mockFilter1 = $this->getMockBuilder(Filter::class)
                                ->setConstructorArgs(['age', '<', $mockValue1Sanitize])
                                    ->setMethods(['dump'])
                                        ->getMock();
        $mockFilter1->expects($this->once())->method('dump')->willReturn('age < 16');
        $mockFilter2 = $this->getMockBuilder(Filter::class)
                                        ->setConstructorArgs(['age', '>', $mockValue1Sanitize])
                                            ->setMethods(['dump'])
                                                ->getMock();
        $mockFilter2->expects($this->once())->method('dump')->willReturn('age > 60');

        $this->criteria->add($mockFilter1);
        $this->criteria->add($mockFilter2, Expression::AND_OPERATION);

        $this->assertEquals("(age < 16 AND age > 60)", $this->criteria->dump());
    }

    public function testItCanCreateFilterWithOperatorNotAndNotIn()
    {
        $mockValue1Sanitize = $this->getMockBuilder(ArraySanitize::class)
            ->setConstructorArgs([[24,25,26]])->setMethods(['transform'])->getMock();
        $mockValue1Sanitize->expects($this->any())->method('transform')->willReturn("(24,25,26)");

        $mockValue2Sanitize = $this->getMockBuilder(ArraySanitize::class)
        ->setConstructorArgs([[10]])->setMethods(['transform'])->getMock();
        $mockValue2Sanitize->expects($this->any())->method('transform')->willReturn("(10)");

        $mockFilter1 = $this->getMockBuilder(Filter::class)
                                ->setConstructorArgs(['age', 'in', $mockValue1Sanitize])
                                    ->setMethods(['dump'])
                                        ->getMock();
        $mockFilter1->expects($this->once())->method('dump')->willReturn('age in (24,25,26)');
        $mockFilter2 = $this->getMockBuilder(Filter::class)
                                        ->setConstructorArgs(['age', 'not in', $mockValue1Sanitize])
                                            ->setMethods(['dump'])
                                                ->getMock();
        $mockFilter2->expects($this->once())->method('dump')->willReturn('age not in (10)');

        $this->criteria->add($mockFilter1);
        $this->criteria->add($mockFilter2);
        
        $this->assertEquals("(age in (24,25,26) AND age not in (10))", $this->criteria->dump());
    }

    public function testItCanCreateFilterWithOperatorLike()
    {
        $mockValue1Sanitize = $this->getMockBuilder(StringSanitize::class)
            ->setConstructorArgs(['maria%'])->setMethods(['transform'])->getMock();
        $mockValue1Sanitize->expects($this->any())->method('transform')->willReturn("'maria%'");

        $mockValue2Sanitize = $this->getMockBuilder(StringSanitize::class)
        ->setConstructorArgs(['pedro%'])->setMethods(['transform'])->getMock();
        $mockValue2Sanitize->expects($this->any())->method('transform')->willReturn("'pedro%'");

        $mockFilter1 = $this->getMockBuilder(Filter::class)
                                ->setConstructorArgs(['name', 'like', $mockValue1Sanitize])
                                    ->setMethods(['dump'])
                                        ->getMock();
        $mockFilter1->expects($this->once())->method('dump')->willReturn("name like 'maria%'");
        $mockFilter2 = $this->getMockBuilder(Filter::class)
                                        ->setConstructorArgs(['name', 'like', $mockValue1Sanitize])
                                            ->setMethods(['dump'])
                                                ->getMock();
        $mockFilter2->expects($this->once())->method('dump')->willReturn("name like 'pedro%'");

        $this->criteria->add($mockFilter1);
        $this->criteria->add($mockFilter2);
        
        $this->assertEquals("(name like 'maria%' AND name like 'pedro%')", $this->criteria->dump());
    }
}
