<?php


namespace Futape\Search\Matcher\Filename;


use Futape\Search\Matcher\AbstractStringValue;
use Futape\Search\Matcher\AbstractValue;
use Futape\Utility\Filesystem\Paths;

class FilenameValue extends AbstractStringValue
{
    /**
     * @param mixed $value
     * @return AbstractValue
     * @throws InvalidPathException
     */
    protected function setValue($value): AbstractValue
    {
        parent::setValue(Paths::normalize($value));

        if (Paths::toUrlPath($this->getValue()) === null) {
            throw new InvalidPathException('The path "' . $this->getValue() . '" isn\'t a descendant of document root');
        }

        return $this;
    }

    /**
     * @return AbstractValue
     */
    public function reset(): AbstractValue
    {
        parent::reset();

        return $this->setHighlighted(
            $this->resetHighlighted(Paths::toUrlPath($this->cloneValue($this->getValue()), false, false))
        );
    }

    /**
     * @param mixed $highlighted
     * @return mixed
     */
    protected function resetHighlighted($highlighted)
    {
        return $this->getHighlighter()->lowlight($highlighted);
    }
}
