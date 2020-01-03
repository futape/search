<?php


namespace Futape\Search\Tests\Unit\Matcher;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Search\Matcher\AbstractValue;
use Futape\Search\Matcher\Exception\UnsupportedValueException;
use Futape\Search\Matcher\Token\TokenValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\AbstractMatcher
 */
class AbstractMatcherTest extends TestCase
{
    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\AbstractValue
     */
    public function testMatch()
    {
        $value = AbstractValueTest::getAbstractValueMock('foo')
            ->setHighlighter(new PlainHighlighter());
        $matcher = self::getAbstractMatcherMock(get_class($value));

        $matcher->match($value, 'foo');

        $this->assertEquals('**foo**', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'bar');

        $this->assertEquals('foo', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Matcher\Token\TokenValue
     * @uses \Futape\Search\Matcher\AbstractValue
     */
    public function testPreventUnsupportedValues()
    {
        $this->expectException(UnsupportedValueException::class);

        (self::getAbstractMatcherMock(get_class(AbstractValueTest::getAbstractValueMock('foo'))))
            ->match(
                new TokenValue(['foo']),
                'foo'
            );
    }

    public static function getAbstractMatcherMock(string $supportedValue, ...$arguments): AbstractMatcher
    {
        /** @var MockObject|AbstractMatcher $matcher */
        $matcher = (new self())->getMockForAbstractClass(
            AbstractMatcher::class,
            $arguments,
            '',
            true,
            true,
            true,
            ['accept']
        );
        $matcher
            ->expects(self::any())
            ->method('accept')
            ->will(
                self::returnCallback(
                    function (AbstractValue $value) use ($supportedValue): bool {
                        return get_class($value) == $supportedValue;
                    }
                )
            );
        $matcher
            ->expects(self::any())
            ->method('matchValue')
            ->will(
                self::returnCallback(
                    function (
                        $value,
                        $term,
                        HighlighterInterface $highlighter,
                        &$highlighted,
                        int &$score
                    ) use ($matcher): void {
                        if ($value === $term) {
                            $highlighted = $highlighter->highlight($highlighted);
                            $score++;
                        }
                    }
                )
            );

        return $matcher;
    }
}
