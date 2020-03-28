<?php


namespace Futape\Search\Highlighter;


class DummyHighlighter implements HighlighterInterface
{
    /**
     * @param string $value
     * @return string
     */
    public function highlight($value)
    {
        return $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function lowlight($value)
    {
        return $value;
    }

    /**
     * @param string $value
     * @param array $areas
     * @return mixed
     */
    public function highlightAreas(string $value, array $areas)
    {
        return $value;
    }
}
