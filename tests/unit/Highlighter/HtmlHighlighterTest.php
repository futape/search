<?php


namespace Futape\Search\Tests\Unit\Highlighter;


use Futape\Search\Highlighter\Exception\HighlighterException;
use Futape\Search\Highlighter\HtmlHighlighter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Highlighter\HtmlHighlighter
 */
class HtmlHighlighterTest extends TestCase
{
    public function testHighlight()
    {
        $this->assertEquals('<mark>fo&lt;&gt;bar</mark>', (new HtmlHighlighter())->highlight('fo<>bar'));
        $this->assertSame('<mark>142</mark>', (new HtmlHighlighter())->highlight(142));
    }

    public function testLowlight()
    {
        $this->assertEquals('fo&lt;&gt;bar', (new HtmlHighlighter())->lowlight('fo<>bar'));
        $this->assertSame('142', (new HtmlHighlighter())->lowlight(142));
    }

    public function testHighlightAreas()
    {
        $this->assertEquals('<mark>fo&lt;</mark>&gt;bar', (new HtmlHighlighter())->highlightAreas('fo<>bar', [0, -3]));

        $this->expectException(HighlighterException::class);
        (new HtmlHighlighter())->highlightAreas('fo<>bar', [0, 3]);
    }
}
