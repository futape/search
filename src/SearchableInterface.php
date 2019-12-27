<?php


namespace Futape\Search;


use Futape\Search\Matcher\AbstractValue;

interface SearchableInterface
{
    /**
     * @return AbstractValue[]
     */
    public function getMatcherValues(): array;

    /**
     * @return int
     */
    public function getScore(): int;

    /**
     * @return self
     */
    public function reset(): self;
}
