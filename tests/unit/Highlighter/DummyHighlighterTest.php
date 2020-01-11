<?php


namespace Futape\Search\Tests\Unit\Highlighter;


use Futape\Search\Highlighter\DummyHighlighter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Highlighter\DummyHighlighter
 */
class DummyHighlighterTest extends TestCase
{
    public function testHighlight()
    {
        $this->assertSame(false, (new DummyHighlighter())->highlight(false));
    }

    public function testLowlight()
    {
        $this->assertSame(false, (new DummyHighlighter())->lowlight(false));
    }
}
