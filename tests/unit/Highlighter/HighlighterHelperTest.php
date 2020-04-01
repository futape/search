<?php


namespace Futape\Search\Tests\Unit\Highlighter;


use Futape\Search\Highlighter\Exception\HighlighterException;
use Futape\Search\Highlighter\HighlighterHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Highlighter\HighlighterHelper
 */
class HighlighterHelperTest extends TestCase
{
    public function testProcessAreas()
    {
        $this->assertEquals(
            [5, -7, 10, -15, 20, -30, 30, -40],
            HighlighterHelper::processAreas([5, 10, -7, 20, 30, -15, -30, -40])
        );
    }

    public function testProcessAreasFailsOnUnevenAreasCount()
    {
        $this->expectException(HighlighterException::class);
        HighlighterHelper::processAreas([5, 10, -7]);
    }

    public function testProcessAreasFailsOnUnterminatedArea()
    {
        $this->expectException(HighlighterException::class);
        HighlighterHelper::processAreas([5, 10, -7, 20]);
    }

    public function testProcessAreasFailsOnTerminatingUninitializedArea()
    {
        $this->expectException(HighlighterException::class);
        HighlighterHelper::processAreas([5, 10, -7, -9]);
    }
}
