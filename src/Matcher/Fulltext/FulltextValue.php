<?php


namespace Futape\Search\Matcher\Fulltext;


use Futape\Search\Matcher\AbstractStringValue;

class FulltextValue extends AbstractStringValue
{
    /**
     * @param mixed $highlighted
     * @return mixed
     */
    protected function resetHighlighted($highlighted)
    {
        return $this->getHighlighter()->lowlight($highlighted);
    }
}
