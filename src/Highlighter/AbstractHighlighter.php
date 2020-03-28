<?php


namespace Futape\Search\Highlighter;


use Futape\Search\Highlighter\Exception\HighlighterException;

abstract class AbstractHighlighter implements HighlighterInterface
{
    /**
     * @param array $areas
     * @return array
     */
    protected function processAreas(array $areas)
    {
        if (count($areas) % 2 != 0) {
            throw new HighlighterException('Bad areas definition', 1585414752);
        }

        $depth = 0;
        foreach ($areas as $position) {
            $depth += $position >= 0 ? 1 : -1;

            if ($depth < 0) {
                break;
            }
        }
        if ($depth != 0) {
            throw new HighlighterException('Bad areas definition', 1585414751);
        }

        usort(
            $areas,
            function ($a, $b) {
                if (abs($a) == abs($b)) {
                    return $a < 0 ? -1 : 1;
                } else {
                    return abs($a) - abs($b);
                }
            }
        );

        return $areas;
    }
}
