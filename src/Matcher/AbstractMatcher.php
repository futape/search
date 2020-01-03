<?php


namespace Futape\Search\Matcher;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\Exception\UnsupportedValueException;

abstract class AbstractMatcher
{
    const SUPPORTED_VALUE = '';

    /**
     * @param AbstractValue $value
     * @param mixed $term
     * @return self
     * @throws UnsupportedValueException
     */
    public function match(AbstractValue $value, $term): self
    {
        if (!$this->accept($value)) {
            throw new UnsupportedValueException($value, static::SUPPORTED_VALUE, 1577115322);
        }

        $highlighted = $value
            ->reset()
            ->getHighlighted();
        $score = $value->getScore();

        $this->matchValue($value->getValue(), $term, $value->getHighlighter(), $highlighted, $score);

        $value
            ->setHighlighted($highlighted)
            ->setScore($score);

        return $this;
    }

    /**
     * @param mixed $value
     * @param mixed $term
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    abstract protected function matchValue(
        $value,
        $term,
        HighlighterInterface $highlighter,
        &$highlighted,
        int &$score
    ): void;

    /**
     * @param AbstractValue $value
     * @return bool
     */
    public function accept(AbstractValue $value): bool
    {
        return get_class($value) == static::SUPPORTED_VALUE;
    }
}
