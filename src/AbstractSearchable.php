<?php


namespace Futape\Search;


use Futape\Search\Matcher\AbstractValue;

abstract class AbstractSearchable implements SearchableInterface
{
    /** @var AbstractValue[] */
    protected $matcherValues = [];

    public function __construct()
    {
        $this->initMatcherValues();
    }

    abstract protected function initMatcherValues(): void;

    /**
     * @return AbstractValue[]
     */
    public function getMatcherValues(): array
    {
        return $this->matcherValues;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return array_sum(
            array_map(
                function (AbstractValue $value) {
                    return $value->getScore();
                },
                $this->getMatcherValues()
            )
        );
    }

    /**
     * @return SearchableInterface
     */
    public function reset(): SearchableInterface
    {
        foreach ($this->getMatcherValues() as $value) {
            $value->reset();
        }

        return $this;
    }
}
