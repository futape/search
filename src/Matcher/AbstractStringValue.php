<?php


namespace Futape\Search\Matcher;


abstract class AbstractStringValue extends AbstractValue
{
    /**
     * @param array $value
     */
    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return parent::getValue();
    }

    /**
     * @return string
     */
    public function getHighlighted(): string
    {
        return parent::getHighlighted();
    }

    /**
     * @param mixed $highlighted
     * @return self
     */
    public function setHighlighted($highlighted): AbstractValue
    {
        return parent::setHighlighted((string)$highlighted);
    }
}
