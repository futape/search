<?php


namespace Futape\Search\Highlighter;


class HtmlHighlighter extends AbstractStringHighlighter
{
    /** @var string */
    protected $opening = '<mark>';

    /** @var string */
    protected $closing = '</mark>';
}
