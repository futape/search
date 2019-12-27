<?php


namespace Futape\Search\Matcher\Token;


use Futape\Search\Matcher\AbstractMatcher;

class TokenMatcher extends AbstractMatcher
{
    const SUPPORTED_VALUE = TokenValue::class;

    /**
     * @param mixed $value
     * @param mixed $term
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue($value, $term, &$highlighted, int &$score): void
    {
        foreach ($value as $key => $token) {
            if ($token === $term) {
                $highlighted[$key] = $this->getHighlighter()->highlight($highlighted[$key]);
                $score++;
            }
        }
    }
}
