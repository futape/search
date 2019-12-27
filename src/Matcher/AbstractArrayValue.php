<?php


namespace Futape\Search\Matcher;


class AbstractArrayValue extends AbstractValue
{
    /** @var array */
    protected $value;

    /** @var array */
    protected $highlighted;

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
