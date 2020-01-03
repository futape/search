<?php


namespace Futape\Search\Matcher\Token;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;

class TokenMatcher extends AbstractMatcher
{
    const SUPPORTED_VALUE = TokenValue::class;

    /**
     * @var bool
     */
    protected $ignoreCase = false;

    /**
     * @param mixed $value
     * @param mixed $term
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue(
        $value,
        $term,
        HighlighterInterface $highlighter,
        &$highlighted,
        int &$score
    ): void {
        foreach ($value as $key => $token) {
            if (
                $token === $term ||
                $this->isIgnoreCase() &&
                is_string($term) &&
                mb_strtolower($token) == mb_strtolower($term)
            ) {
                $highlighted[$key] = $highlighter->highlight($token);
                $score++;
            }
        }
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
