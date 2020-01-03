<?php


namespace Futape\Search;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractValue;

interface SearchableInterface
{
    /**
     * @return AbstractValue[]
     */
    public function getMatcherValues(): array;

    /**
     * @param mixed $key
     * @return AbstractValue|null
     */
    public function getMatcherValue($key): ?AbstractValue;

    /**
     * @param HighlighterInterface|null $highlighter
     * @return self
     */
    public function setHighlighter(?HighlighterInterface $highlighter): self;

    /**
     * @return int
     */
    public function getScore(): int;

    /**
     * @return self
     */
    public function reset(): self;
}
