<?php


namespace Futape\Search\Highlighter;


class PlainHighlighter extends AbstractStringHighlighter
{
    /** @var string */
    protected $opening = '**';

    /** @var string */
    protected $closing = '**';
}
