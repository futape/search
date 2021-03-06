<?php


namespace Futape\Search;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Highlighter\HtmlHighlighter;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Search\Matcher\AbstractValue;

class Index
{
    /** @var SearchableInterface[] */
    private $searchables = [];

    /** @var AbstractMatcher[] */
    private $matchers = [];

    /** @var HighlighterInterface */
    private $highlighter;

    /** @var string|null */
    private $searchableFilter;

    /**
     * @param HighlighterInterface|null $highlighter
     */
    public function __construct(?HighlighterInterface $highlighter = null)
    {
        $this->setHighlighter($highlighter ?? new HtmlHighlighter());
    }

    /**
     * @return SearchableInterface[]
     */
    public function getSearchables(): array
    {
        $searchableFilter = $this->getSearchableFilter();
        if ($searchableFilter !== null) {
            return array_filter(
                $this->searchables,
                function ($searchable) use ($searchableFilter) {
                    return $searchable instanceof $searchableFilter;
                }
            );
        }

        return $this->searchables;
    }

    /**
     * @param SearchableInterface $searchable
     * @return self
     */
    public function addSearchable(SearchableInterface $searchable): self
    {
        $searchable
            ->reset()
            ->setHighlighter($this->getHighlighter());
        $this->searchables[] = $searchable;

        return $this;
    }

    /**
     * @return AbstractMatcher[]
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    /**
     * @param AbstractMatcher $matcher
     * @return self
     */
    public function attachMatcher(AbstractMatcher $matcher): self
    {
        if (isset($this->matchers[$matcher::SUPPORTED_VALUE])) {
            $this->detachMatcher($this->matchers[$matcher::SUPPORTED_VALUE]);
        }

        $this->matchers[$matcher::SUPPORTED_VALUE] = $matcher;

        return $this;
    }

    /**
     * @param AbstractMatcher $matcher
     * @return self
     */
    public function detachMatcher(AbstractMatcher $matcher): self
    {
        $matcherKey = array_search($matcher, $this->matchers, true);

        if ($matcherKey !== false) {
            unset($this->matchers[$matcherKey]);
        }

        return $this;
    }

    /**
     * @return HighlighterInterface
     */
    public function getHighlighter(): HighlighterInterface
    {
        return $this->highlighter;
    }

    /**
     * @param HighlighterInterface $highlighter
     * @return self
     */
    public function setHighlighter(HighlighterInterface $highlighter): self
    {
        $this->highlighter = $highlighter;
        $this->forwardHighlighter();

        return $this;
    }

    /**
     * @return self
     */
    protected function forwardHighlighter(): self
    {
        foreach ($this->getSearchables() as $searchable) {
            $searchable->setHighlighter($this->getHighlighter());
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSearchableFilter(): ?string
    {
        return $this->searchableFilter;
    }

    /**
     * @param string|null $searchableFilter
     * @return self
     */
    public function setSearchableFilter(?string $searchableFilter): self
    {
        $this->searchableFilter = $searchableFilter;

        return $this;
    }

    /**
     * @param mixed $term
     * @return self
     */
    public function search($term): self
    {
        foreach ($this->getSearchables() as $searchable) {
            foreach ($searchable->getMatcherValues() as $value) {
                $matcher = $this->getMatcherForValue($value);

                if ($matcher !== null) {
                    $matcher->match($value, $term);
                }
            }
        }

        return $this;
    }

    /**
     * @return SearchableInterface[]
     */
    public function getMatching(): array
    {
        $matching = array_filter(
            $this->getSearchables(),
            function (SearchableInterface $searchable) {
                return $searchable->getScore() > 0;
            }
        );
        usort(
            $matching,
            function (SearchableInterface $searchable1, SearchableInterface $searchable2) {
                return $searchable2->getScore() - $searchable1->getScore();
            }
        );

        return $matching;
    }

    /**
     * @param AbstractValue $value
     * @return AbstractMatcher|null
     */
    protected function getMatcherForValue(AbstractValue $value): ?AbstractMatcher
    {
        return $this->getMatchers()[get_class($value)] ?? null;
    }
}
