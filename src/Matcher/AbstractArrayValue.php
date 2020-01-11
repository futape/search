<?php


namespace Futape\Search\Matcher;


abstract class AbstractArrayValue extends AbstractValue
{
    /**
     * @param array $value
     */
    public function __construct(array $value)
    {
        parent::__construct($value);
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return parent::getValue();
    }

    /**
     * @return array
     */
    public function getHighlighted(): array
    {
        return parent::getHighlighted();
    }

    /**
     * @param mixed $highlighted
     * @return self
     */
    public function setHighlighted($highlighted): AbstractValue
    {
        return parent::setHighlighted((array)$highlighted);
    }
}
