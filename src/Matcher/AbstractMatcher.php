<?php


namespace Futape\Search\Matcher;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\Exception\UnsupportedValueException;
use Futape\Search\TermCollection;

abstract class AbstractMatcher
{
    const SUPPORTED_VALUE = '';

    /**
     * @param AbstractValue $value
     * @param mixed $terms
     * @return self
     * @throws UnsupportedValueException
     */
    public function match(AbstractValue $value, $terms): self
    {
        if (!$this->accept($value)) {
            throw new UnsupportedValueException($value, static::SUPPORTED_VALUE, 1577115322);
        }

        $highlighted = $value
            ->reset()
            ->getHighlighted();
        $score = $value->getScore();

        if ($this instanceof TermCollectionAware) {
            $terms = $this->processTermCollection(
                $terms instanceof TermCollection ? $terms : new TermCollection([$terms])
            );
        }

        if (!$terms instanceof TermCollection || $this instanceof TermCollectionAware) {
            $this->matchValue($value->getValue(), $terms, $value->getHighlighter(), $highlighted, $score);
        } else {
            foreach ($terms as $term) {
                $currentHighlighted = $highlighted;
                $currentScore = $score;

                $this->matchValue(
                    $value->getValue(),
                    $term,
                    $value->getHighlighter(),
                    $currentHighlighted,
                    $currentScore
                );

                if ($currentScore != $score) {
                    $score = $currentScore;
                    $highlighted = $currentHighlighted;

                    break;
                }
            }
        }

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
