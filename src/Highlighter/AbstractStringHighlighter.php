<?php


namespace Futape\Search\Highlighter;


abstract class AbstractStringHighlighter implements HighlighterInterface
{
    /** @var string */
    protected $opening;

    /** @var string */
    protected $closing;

    /**
     * @param string $value
     * @return string
     */
    public function highlight($value): string
    {
        return $this->opening . $value . $this->closing;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function lowlight($value): string
    {
        return (string)$value;
    }
}
