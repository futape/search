<?php


namespace Futape\Search\Matcher;


abstract class AbstractValue
{
    /** @var mixed */
    private $value;

    /** @var mixed */
    private $highlighted;

    /** @var int */
    private $score;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return self
     */
    protected function setValue($value): self
    {
        $this->value = $value;

        return $this->reset();
    }

    /**
     * @return mixed
     */
    public function getHighlighted()
    {
        return $this->highlighted;
    }

    /**
     * @param $highlighted
     * @return self
     */
    public function setHighlighted($highlighted): self
    {
        $this->highlighted = $highlighted;

        return $this;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @param int $score
     * @return self
     */
    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    /**
     * @return self
     */
    public function reset(): self
    {
        return $this
            ->setHighlighted($this->cloneValue($this->getValue()))
            ->setScore(0);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function cloneValue($value)
    {
        if (is_object($value)) {
            return clone $value;
        }
        if(is_array($value)) {
            array_walk_recursive(
                $value,
                function (&$val) {
                    $val = $this->cloneValue($val);
                }
            );

            return $value;
        }

        return $value;
    }
}
