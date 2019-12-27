<?php


namespace Futape\Search\Tests\Unit\Highlighter;


use Futape\Search\Highlighter\PlainHighlighter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Highlighter\PlainHighlighter
 */
class PlainHighlighterTest extends TestCase
{
    public function testHighlight()
    {
        $this->assertEquals('**foobar**', (new PlainHighlighter())->highlight('foobar'));
    }
}
