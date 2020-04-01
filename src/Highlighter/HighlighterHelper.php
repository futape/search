<?php


namespace Futape\Search\Highlighter;


use Futape\Search\Highlighter\Exception\HighlighterException;

abstract class HighlighterHelper
{
    /**
     * @param array $areas
     * @return array
     * @throws HighlighterException
     */
    public static function processAreas(array $areas): array
    {
        if (count($areas) % 2 != 0) {
            throw new HighlighterException('Bad areas definition', 1585414752);
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

        return $areas;
    }
}
