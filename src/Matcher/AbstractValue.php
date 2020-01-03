<?php


namespace Futape\Search\Matcher;


use Futape\Search\Highlighter\DummyHighlighter;
use Futape\Search\Highlighter\HighlighterInterface;

abstract class AbstractValue
{
    /** @var mixed */
    private $value;

    /** @var mixed */
    private $highlighted;

    /** @var int */
    private $score;

    /** @var HighlighterInterface */
    private $highlighter;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        // Don't use setValue() here since the setter would call reset() which calls resetHighlighted() in turn which is
        // likely to call getHighlighter() which requires a value for $highlighter, which doesn't exist at this point.
        // Initialize $highlighter since setValue() calls reset() which calls resetHighlighted() in turn which is likely
        // to call getHighlighter() which requires a value for $highlighter.
        // Not using the setter(s) isn't an option since subclasses may override the setter to process the value.
        // Using setHighlighter() here isn't possible since it calls reset(), too. However, call as the final method
        // to benefit of a subclass's overridden setHighlighter() method, whose logic will affect $highlighted since
        // that method calls reset().
        $this->highlighter = new DummyHighlighter();
        $this->setValue($value);
        $this->setHighlighter(null);
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
     * @param HighlighterInterface $highlighter
     * @return self
     */
    public function setHighlighter(?HighlighterInterface $highlighter): self
    {
        $this->highlighter = $highlighter ?? new DummyHighlighter();

        return $this->reset();
    }

    /**
     * @return HighlighterInterface
     */
    public function getHighlighter(): HighlighterInterface
    {
        return $this->highlighter;
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
     * @param mixed $highlighted
     * @return mixed
     */
    abstract protected function resetHighlighted($highlighted);

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
