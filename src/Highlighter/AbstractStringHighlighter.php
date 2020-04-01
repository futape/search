<?php


namespace Futape\Search\Highlighter;


use Futape\Search\Highlighter\Exception\HighlighterException;

abstract class AbstractStringHighlighter implements HighlighterInterface
{
    /** @var string */
    protected $opening;

    /** @var string */
    protected $closing;

    /**
     * @param mixed $value
     * @return string
     */
    public function highlight($value): string
    {
        return $this->opening . $value . $this->closing;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function lowlight($value): string
    {
        return (string)$value;
    }

    /**
     * @param string $value
     * @param array $areas
     * @return string
     * @throws HighlighterException
     */
    public function highlightAreas(string $value, array $areas): string
    {
        $areas = HighlighterHelper::processAreas($areas);

        $highlighted = '';
        $pointer = 0;

        foreach ($areas as $position) {
            $highlighted .= $this->lowlight(mb_substr($value, $pointer, abs($position) - $pointer));
            $highlighted .= $position >= 0 ? $this->opening : $this->closing;

            $pointer = abs($position);
        }

        $highlighted .= $this->lowlight(mb_substr($value, $pointer));

        return $highlighted;
    }
}
