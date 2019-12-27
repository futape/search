<?php


namespace Futape\Search\Highlighter;


interface HighlighterInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function highlight($value);
}
