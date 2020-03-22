<?php


namespace Futape\Search\Matcher;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\Exception\UnsupportedValueException;
use Futape\Search\TermCollection;

abstract class AbstractMatcher
{
    const SUPPORTED_VALUE = '';
    const SUPPORTS_TERM_COLLECTION = false;

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

        if (static::SUPPORTS_TERM_COLLECTION || !$terms instanceof TermCollection) {
            $this->matchValue($value->getValue(), $terms, $value->getHighlighter(), $highlighted, $score);
        } else {
            foreach ($terms as $term) {
                $this->matchValue($value->getValue(), $term, $value->getHighlighter(), $highlighted, $score);
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
     * @param TermCollection $termCollection
     * @return TermCollection
     */
    abstract protected function processTermCollection(TermCollection $termCollection): TermCollection;

    /**
     * @param AbstractValue $value
     * @return bool
     */
    public function accept(AbstractValue $value): bool
    {
        return get_class($value) == static::SUPPORTED_VALUE;
    }
}
