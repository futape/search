<?php


namespace Futape\Search\Tests\Unit\Matcher;


use Futape\Search\Highlighter\HtmlHighlighter;
use Futape\Search\Matcher\AbstractValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Futape\Search\Matcher\AbstractValue
 */
class AbstractValueTest extends TestCase
{
    public function testReset()
    {
        $value = self::getAbstractValueMock('fo<>bar');
        $value
            ->setHighlighter(new HtmlHighlighter())
            ->setHighlighted($value->getHighlighter()->highlight($value->getValue()))
            ->setScore(1);

        $this->assertEquals('<mark>fo&lt;&gt;bar</mark>', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $value->reset();

        $this->assertEquals('fo&lt;&gt;bar', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @dataProvider objectsClonedForHighlightDataProvider
     *
     * @param $managedValue
     */
    public function testObjectsClonedForHighlight($managedValue)
    {
        $this->assertNotSame($managedValue, self::getAbstractValueMock($managedValue)->getHighlighted());
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
        /** @var MockObject|AbstractValue $value */
        $value = (new self())->getMockForAbstractClass(
            AbstractValue::class,
            $arguments,
            '',
            true,
            true,
            true,
            ['resetHighlighted']
        );
        $value
            ->expects(self::any())
            ->method('resetHighlighted')
            ->will(
                self::returnCallback(
                    function ($highlighted) use ($value) {
                        return $value->getHighlighter()->lowlight($highlighted);
                    }
                )
            );

        return $value;
    }
}
