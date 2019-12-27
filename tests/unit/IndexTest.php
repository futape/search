<?php


namespace Futape\Search\Tests\Unit;


use Futape\Search\AbstractSearchable;
use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Index;
use Futape\Search\Matcher\AbstractValue;
use Futape\Search\Matcher\Token\TokenMatcher;
use Futape\Search\Matcher\Token\TokenValue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Index
 */
class IndexTest extends TestCase
{
    /**
     * @uses \Futape\Search\Matcher\Token\TokenMatcher
     */
    public function testForwardHighlighterToAttachedMatcher()
    {
        $matcher = new TokenMatcher();
        $index = (new Index())
            ->attachMatcher($matcher);

        $this->assertSame($index->getHighlighter(), $matcher->getHighlighter());

        $index->detachMatcher($matcher);

        $this->assertNotSame($index->getHighlighter(), $matcher->getHighlighter());
    }

    /**
     * @uses \Futape\Search\Matcher\Token\TokenMatcher
     */
    public function testOverrideAttachedMatcher()
    {
        $matcher = new TokenMatcher();
        $overrideMatcher = new TokenMatcher();
        $index = (new Index())
            ->attachMatcher($matcher);

        $this->assertContains($matcher, $index->getMatchers());

        $index->attachMatcher($overrideMatcher);

        $this->assertContains($overrideMatcher, $index->getMatchers());
        $this->assertNotContains($matcher, $index->getMatchers());
        $this->assertNotSame($index->getHighlighter(), $matcher->getHighlighter());
    }

    /**
     * @uses \Futape\Search\Matcher\Token\TokenValue
     * @uses \Futape\Search\Matcher\Token\TokenMatcher
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\AbstractSearchable
     */
    public function testMatch()
    {
        $value1 = new TokenValue(['foo', 'bar', 'baz']);
        $value2 = new TokenValue(['foo', 'baz', 'bar', 'bar']);
        $searchable = self::getAbstractSearchableMock([$value1, $value2]);
        $index = (new Index(new PlainHighlighter()))
            ->attachMatcher(new TokenMatcher())
            ->addSearchable($searchable)
            ->search('bar');

        $this->assertSame($index->getSearchables()[0], $searchable);
        $this->assertEquals(3, $index->getSearchables()[0]->getScore());

        $this->assertSame($index->getSearchables()[0]->getMatcherValues()[0], $value1);
        $this->assertEquals(1, $index->getSearchables()[0]->getMatcherValues()[0]->getScore());
        $this->assertEquals(
            ['foo', '**bar**', 'baz'],
            $index->getSearchables()[0]->getMatcherValues()[0]->getHighlighted()
        );

        $this->assertSame($index->getSearchables()[0]->getMatcherValues()[1], $value2);
        $this->assertEquals(2, $index->getSearchables()[0]->getMatcherValues()[1]->getScore());
        $this->assertEquals(
            ['foo', 'baz', '**bar**', '**bar**'],
            $index->getSearchables()[0]->getMatcherValues()[1]->getHighlighted()
        );
    }

    /**
     * @param AbstractValue[] $matcherValues
     * @param mixed ...$arguments
     * @return AbstractSearchable
     */
    public static function getAbstractSearchableMock(array $matcherValues, ...$arguments): AbstractSearchable
    {
        if (count($arguments) == 0) {
            $arguments = [null];
        }

        /** @var MockObject|AbstractSearchable $searchable */
        $searchable = (new self())->getMockForAbstractClass(
            AbstractSearchable::class,
            $arguments,
            '',
            true,
            true,
            true,
            ['getMatcherValues']
        );
        $searchable
            ->expects(self::any())
            ->method('getMatcherValues')
            ->will(
                self::returnValue($matcherValues)
            );

        return $searchable;
    }
}
