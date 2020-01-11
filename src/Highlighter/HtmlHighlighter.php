<?php


namespace Futape\Search\Highlighter;


class HtmlHighlighter extends AbstractStringHighlighter
{
    /** @var string */
    protected $opening = '<mark>';

    /** @var string */
    protected $closing = '</mark>';

    /**
     * @param string $value
     * @return string
     */
    public function highlight($value): string
    {
        return $this->opening . htmlspecialchars((string)$value) . $this->closing;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function lowlight($value): string
    {
        return htmlspecialchars(parent::lowlight($value));
    }
}
