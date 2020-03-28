<?php


namespace Futape\Search\Matcher\Token;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Search\Matcher\TermCollectionAware;
use Futape\Search\TermCollection;
use Futape\Utility\ArrayUtility\Arrays;

class TokenMatcher extends AbstractMatcher implements TermCollectionAware
{
    const SUPPORTED_VALUE = TokenValue::class;

    /**
     * @var bool
     *
     * @todo BREAKING: Default to true to match FulltextMatcher?
     */
    protected $ignoreCase = false;

    /**
     * @param mixed $value
     * @param TermCollection $terms
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue(
        $value,
        $terms,
        HighlighterInterface $highlighter,
        &$highlighted,
        int &$score
    ): void {
        foreach ($value as $key => $token) {
            if (in_array($this->isIgnoreCase() ? mb_strtolower($token) : $token, $terms->getArrayCopy())) {
                $highlighted[$key] = $highlighter->highlight($token);
                $score++;
            }
        }
    }

    /**
     * @param TermCollection $termCollection
     * @return TermCollection
     */
    public function processTermCollection(TermCollection $termCollection): TermCollection
    {
        $termCollection->exchangeArray(
            Arrays::unique(
                array_filter(
                    $termCollection->getArrayCopy(),
                    function ($val) {
                        return is_string($val);
                    }
                ),
                Arrays::UNIQUE_STRING | ($this->isIgnoreCase() ? Arrays::UNIQUE_LOWERCASE : 0)
            )
        );

        return $termCollection;
    }

    /**
     * @return bool
     */
    public function isIgnoreCase(): bool
    {
        return $this->ignoreCase;
    }

    /**
     * @param bool $ignoreCase
     * @return self
     */
    public function setIgnoreCase(bool $ignoreCase): self
    {
        $this->ignoreCase = $ignoreCase;

        return $this;
    }
}
