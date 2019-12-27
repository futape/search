<?php


namespace Futape\Search\Tests\Unit\Highlighter;


use Futape\Search\Highlighter\HtmlHighlighter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Highlighter\HtmlHighlighter
 */
class HtmlHighlighterTest extends TestCase
{
    public function testHighlight()
    {
        $this->assertEquals('<mark>foobar</mark>', (new HtmlHighlighter())->highlight('foobar'));
    }
}
