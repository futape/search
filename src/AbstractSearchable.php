<?php


namespace Futape\Search;


use Futape\Search\Matcher\AbstractValue;

abstract class AbstractSearchable implements SearchableInterface
{
    /** @var AbstractValue[] */
    private $matcherValues = [];

    public function __construct()
    {
        $this->initMatcherValues();
    }

    abstract protected function initMatcherValues(): void;

    /**
     * @param mixed $key
     * @param AbstractValue $value
     * @return self
     */
    protected function registerMatcherValue($key, AbstractValue $value): self
    {
        $this->matcherValues[$key] = $value;

        return $this;
    }

    /**
     * @return AbstractValue[]
     */
    public function getMatcherValues(): array
    {
        return $this->matcherValues;
    }

    /**
     * @param mixed $key
     * @return AbstractValue|null
     */
    public function getMatcherValue($key): ?AbstractValue
    {
        return $this->matcherValues[$key] ?? null;
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
