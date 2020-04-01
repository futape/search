<?php


namespace Futape\Search\Tests\Unit\Highlighter;


use Futape\Search\Highlighter\Exception\HighlighterException;
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
        $this->assertSame('**142**', (new PlainHighlighter())->highlight(142));
    }

    public function testLowlight()
    {
        $this->assertEquals('foobar', (new PlainHighlighter())->lowlight('foobar'));
        $this->assertSame('142', (new PlainHighlighter())->lowlight(142));
    }

    public function testHighlightAreas()
    {
        $this->assertEquals('**foo**bar', (new PlainHighlighter())->highlightAreas('foobar', [0, -3]));

        $this->expectException(HighlighterException::class);
        (new PlainHighlighter())->highlightAreas('foobar', [0, 3]);
    }
}
