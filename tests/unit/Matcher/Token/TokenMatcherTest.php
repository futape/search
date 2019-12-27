<?php


namespace Futape\Search\Tests\Unit\Matcher\Token;


use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Matcher\Token\TokenMatcher;
use Futape\Search\Matcher\Token\TokenValue;
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
     */
    public function testMatch()
    {
        $matcher = (new TokenMatcher())
            ->setHighlighter(new PlainHighlighter());
        $value = new TokenValue(['foo', 'bar', 'baz', 'bar']);

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
}
