<?php


namespace Futape\Search\Tests\Unit\Matcher\Token;


use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Matcher\Token\TokenMatcher;
use Futape\Search\Matcher\Token\TokenValue;
use Futape\Search\TermCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\Token\TokenMatcher
 */
class TokenMatcherTest extends TestCase
{
    public function testSupportedValue()
    {
        $this->assertEquals(TokenValue::class, TokenMatcher::SUPPORTED_VALUE);
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Token\TokenValue
     */
    public function testMatch()
    {
        $matcher = new TokenMatcher();
        $value = (new TokenValue(['foo', 'bar', 'baz', 'bar']))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, 'foo');

        $this->assertEquals(['**foo**', 'bar', 'baz', 'bar'], $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'bar');

        $this->assertEquals(['foo', '**bar**', 'baz', '**bar**'], $value->getHighlighted());
        $this->assertEquals(2, $value->getScore());

        $matcher->match($value, 'bam');

        $this->assertEquals(['foo', 'bar', 'baz', 'bar'], $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Matcher\Token\TokenValue
     */
    public function testIgnoreCase()
    {
        $matcher = new TokenMatcher();
        $value = (new TokenValue(['FOO', 'bar']))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, 'foo');
        $this->assertEquals(['FOO', 'bar'], $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());

        $matcher->setIgnoreCase(true);

        $matcher->match($value, 'foo');
        $this->assertEquals(['**FOO**', 'bar'], $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Token\TokenValue
     * @uses \Futape\Search\TermCollection
     */
    public function testMatchTermCollection()
    {
        $matcher = new TokenMatcher();
        $value = (new TokenValue(['foo', 'bar', 'baz', 'bar']))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, new TermCollection(['foo', 'bar']));
        $this->assertEquals(['**foo**', '**bar**', 'baz', '**bar**'], $value->getHighlighted());
        $this->assertEquals(3, $value->getScore());
    }
}
