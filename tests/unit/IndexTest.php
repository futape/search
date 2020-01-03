<?php


namespace Futape\Search\Tests\Unit;


use Futape\Search\AbstractSearchable;
use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Index;
use Futape\Search\Matcher\AbstractValue;
use Futape\Search\Matcher\Token\TokenMatcher;
use Futape\Search\Matcher\Token\TokenValue;
use Futape\Search\SearchableInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Index
 */
class IndexTest extends TestCase
{
    /**
     * @uses \Futape\Search\Matcher\Token\TokenValue
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     */
    public function testForwardHighlighter()
    {
        $value = new TokenValue([]);
        $searchable = self::getAbstractSearchableMock(['value1' => $value]);
        $index = (new Index())
            ->addSearchable($searchable);

        $this->assertSame($index->getHighlighter(), $value->getHighlighter());

        $index->setHighlighter(new PlainHighlighter());
        $this->assertSame($index->getHighlighter(), $value->getHighlighter());
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
        $searchable = self::getAbstractSearchableMock(
            [
                'value1' => $value1,
                'value2' => $value2
            ]
        );
        $index = (new Index(new PlainHighlighter()))
            ->attachMatcher(new TokenMatcher())
            ->addSearchable($searchable)
            ->search('bar');

        $this->assertSame($index->getSearchables()[0], $searchable);
        $this->assertEquals(3, $index->getSearchables()[0]->getScore());

        $this->assertSame($index->getSearchables()[0]->getMatcherValue('value1'), $value1);
        $this->assertEquals(1, $index->getSearchables()[0]->getMatcherValue('value1')->getScore());
        $this->assertEquals(
            ['foo', '**bar**', 'baz'],
            $index->getSearchables()[0]->getMatcherValue('value1')->getHighlighted()
        );

        $this->assertSame($index->getSearchables()[0]->getMatcherValue('value2'), $value2);
        $this->assertEquals(2, $index->getSearchables()[0]->getMatcherValue('value2')->getScore());
        $this->assertEquals(
            ['foo', 'baz', '**bar**', '**bar**'],
            $index->getSearchables()[0]->getMatcherValue('value2')->getHighlighted()
        );
    }

    /**
     * @uses \Futape\Search\Matcher\Token\TokenValue
     * @uses \Futape\Search\Matcher\Token\TokenMatcher
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\AbstractSearchable
     */
    public function testMatchingSearchables()
    {
        $searchable1 = self::getAbstractSearchableMock(['value1' => new TokenValue(['foo', 'bar', 'baz'])]);
        $searchable2 = self::getAbstractSearchableMock(['value1' => new TokenValue(['baz', 'bar', 'bar'])]);
        $index = (new Index())
            ->attachMatcher(new TokenMatcher())
            ->addSearchable($searchable1)
            ->addSearchable($searchable2)
            ->search('bar');

        $this->assertSame([$searchable2, $searchable1], $index->getMatching());

        $index->search('foo');
        $this->assertSame([$searchable1], $index->getMatching());

        $index->search('bam');
        $this->assertCount(0, $index->getMatching());
    }

    /**
     * @uses \Futape\Search\SearchableInterface
     * @uses \Futape\Search\AbstractSearchable
     */
    public function testSearchableFilter()
    {
        $searchable = self::getAbstractSearchableMock([]);
        $index = (new Index())
            ->addSearchable($searchable);

        $this->assertContains($searchable, $index->getSearchables());

        $index->setSearchableFilter(get_class($searchable));
        $this->assertContains($searchable, $index->getSearchables());

        $index->setSearchableFilter('\Foo\Bar\MySearchable');
        $this->assertNotContains($searchable, $index->getSearchables());

        $index->setSearchableFilter(SearchableInterface::class);
        $this->assertContains($searchable, $index->getSearchables());

        $index->setSearchableFilter(null);
        $this->assertContains($searchable, $index->getSearchables());
    }

    /**
     * @uses \Futape\Search\Matcher\Token\TokenMatcher
     * @uses \Futape\Search\Matcher\Token\TokenValue
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     */
    public function testResetSearchableWhenAdded()
    {
        $value = new TokenValue(['foo', 'bar', 'baz']);
        $searchable = self::getAbstractSearchableMock(['value1' => $value]);

        (new Index(new PlainHighlighter()))
            ->attachMatcher(new TokenMatcher())
            ->addSearchable($searchable)
            ->search('bar');

        $this->assertEquals(1, $value->getScore());
        $this->assertEquals(['foo', '**bar**', 'baz'], $value->getHighlighted());

        (new Index())
            ->addSearchable($searchable);

        $this->assertEquals(0, $value->getScore());
        $this->assertEquals(['foo', 'bar', 'baz'], $value->getHighlighted());
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
            ['getMatcherValues', 'getMatcherValue']
        );
        $searchable
            ->expects(self::any())
            ->method('getMatcherValues')
            ->will(
                self::returnValue($matcherValues)
            );
        $searchable
            ->expects(self::any())
            ->method('getMatcherValue')
            ->will(
                self::returnCallback(
                    function ($key) use ($matcherValues): ?AbstractValue {
                        return $matcherValues[$key] ?? null;
                    }
                )
            );

        return $searchable;
    }
}
