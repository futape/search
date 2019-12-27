<?php


namespace Futape\Search\Tests\Unit\Matcher;


use Futape\Search\Matcher\AbstractValue;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Futape\Search\Matcher\AbstractValue
 */
class AbstractValueTest extends TestCase
{
    public function testReset()
    {
        $value = self::getAbstractValueMock('foobar')
            ->setHighlighted('**foo**bar')
            ->setScore(1);

        $this->assertEquals('**foo**bar', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $value->reset();

        $this->assertEquals('foobar', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @dataProvider objectsClonedForHighlightDataProvider
     *
     * @param $managedValue
     */
    public function testObjectsClonedForHighlight($managedValue)
    {
        $this->assertNotSame($managedValue, $this->getAbstractValueMock($managedValue)->getHighlighted());
    }

    public function objectsClonedForHighlightDataProvider(): array
    {
        return [
            'Object' => [new stdClass()],
            'Multidimensional array of objects' => [
                [
                    new stdClass(),
                    [
                        new stdClass()
                    ]
                ]
            ]
        ];
    }

    public static function getAbstractValueMock(...$arguments): AbstractValue
    {
        return (new self())->getMockForAbstractClass(AbstractValue::class, $arguments);
    }
}
