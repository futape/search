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
}
