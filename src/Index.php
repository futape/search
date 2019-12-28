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
        return $this->searchables;
    }

    /**
     * @param SearchableInterface $searchable
     * @return self
     */
    public function addSearchable(SearchableInterface $searchable): self
    {
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

        $matcher->setHighlighter($this->getHighlighter());
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
            $matcher->setHighlighter(null);
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
        return array_filter(
            $this->getSearchables(),
            function (SearchableInterface $searchable) {
                return $searchable->getScore() > 0;
            }
        );
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
