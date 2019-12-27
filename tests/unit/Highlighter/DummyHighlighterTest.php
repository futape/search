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
        $this->assertEquals(false, (new DummyHighlighter())->highlight(false));
    }
}
