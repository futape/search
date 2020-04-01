<?php


namespace Futape\Search\Matcher\Fulltext;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Search\Matcher\TermCollectionAware;
use Futape\Search\TermCollection;

class FulltextMatcher extends AbstractMatcher implements TermCollectionAware
{
    const SUPPORTED_VALUE = FulltextValue::class;

    const WORD_BOUNDARY_SEVERITY_LOW = 1;
    const WORD_BOUNDARY_SEVERITY_MEDIUM = 2;
    const WORD_BOUNDARY_SEVERITY_HIGH = 3;
    const WORD_BOUNDARY_SEVERITY_EXTRA_HIGH = 4;

    const WORD_BOUNDARY_EXTRA_STRONG = '(?=\s|$)|(?<=\s|^)';
    const WORD_BOUNDARY_STRONG = '(?=[\W_]|$)|(?<=[\W_]|^)';
    const WORD_BOUNDARY_WEAK = self::WORD_BOUNDARY_STRONG . '|(?<=\d)(?=\D)|(?<=\D)(?=\d)';
    const WORD_BOUNDARY_NONE = '';

    /**
     * @var int
     */
    protected $wordBoundarySeverity = self::WORD_BOUNDARY_SEVERITY_MEDIUM;

    /**
     * @var bool
     */
    protected $ignoreCase = true;


    /**
     * @var bool
     */
    protected $literalSpaces = false;

    /**
     * @return int
     */
    public function getWordBoundarySeverity(): int
    {
        return $this->wordBoundarySeverity;
    }

    /**
     * @param int $wordBoundarySeverity
     * @return self
     */
    public function setWordBoundarySeverity(int $wordBoundarySeverity): self
    {
        $this->wordBoundarySeverity = $wordBoundarySeverity;

        return $this;
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

    /**
     * @return bool
     */
    public function isLiteralSpaces(): bool
    {
        return $this->literalSpaces;
    }

    /**
     * @param bool $literalSpaces
     * @return self
     */
    public function setLiteralSpaces(bool $literalSpaces): self
    {
        $this->literalSpaces = $literalSpaces;

        return $this;
    }

    /**
     * @param TermCollection $termCollection
     * @return TermCollection
     */
    public function processTermCollection(TermCollection $termCollection): TermCollection
    {
        $terms = [];

        foreach ($termCollection as $term) {
            if (!is_string($term)) {
                continue;
            }

            if ($this->isIgnoreCase()) {
                $term = mb_strtolower($term);
            }
            if (!$this->isLiteralSpaces()) {
                $term = preg_replace('/\s+/', ' ', $term);
            }

            if (!in_array($term, $terms)) {
                $terms[] = $term;
            }
        }

        $termCollection->exchangeArray($terms);

        return $termCollection;
    }

    /**
     * @param mixed $value
     * @param TermCollection $terms
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue($value, $terms, HighlighterInterface $highlighter, &$highlighted, int &$score): void
    {
        $highlightAreas = [];
        $termScores = [];

        foreach ($terms as $term) {
            $matches = [];

            preg_match_all(
                $this->getRegex($this->getPattern($term)),
                $value,
                $matches,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE
            );

            $matches = array_filter(
                $matches,
                function ($match) {
                    return $match[0][0] != '';
                }
            );

            if (count($matches) > 0) {
                $matchesNumber = count($matches);
                $termScores[$term] = $matchesNumber;

                /*
                 * Express the complexity of the search term by adding the count of occurrences of the search term in
                 * the value for each token in the search term.
                 *
                 * Be aware to not just add the score for that token since it may have already been counted by another
                 * matched search term and the same occurrence in the value must not be counted multiple times.
                 * When searching the whole value for a single token, the count may likely be higher than the count
                 * of it in a context of the search term it is in.
                 *
                 * If the search term contains only 1 token, don't count that token since it has already been counted by
                 * the whole search term.
                 */
                $termTokens = $this->getTokens($term);

                if (count($termTokens) > 1) {
                    foreach (array_count_values($termTokens) as $termToken => $termTokenNumber) {
                        $termScores[$termToken] = max($termScores[$termToken] ?? 0, $termTokenNumber * $matchesNumber);
                    }
                }

                foreach ($matches as $match) {
                    $opening = mb_strlen(substr($value, 0, $match[0][1]));

                    $highlightAreas[] = $opening;
                    $highlightAreas[] = -($opening + mb_strlen($match[0][0]));
                }
            }
        }

        $score += array_sum($termScores);

        if (count($highlightAreas) > 0) {
            $highlighted = $highlighter->highlightAreas($value, $highlightAreas);
        }
    }

    /**
     * @param string $pattern
     * @return string
     */
    protected function getRegex(string $pattern): string
    {
        switch ($this->getWordBoundarySeverity()) {
            case self::WORD_BOUNDARY_SEVERITY_EXTRA_HIGH:
                $wordBoundary = self::WORD_BOUNDARY_EXTRA_STRONG;
                $modifiers = '';
                break;

            case self::WORD_BOUNDARY_SEVERITY_HIGH:
                $wordBoundary = self::WORD_BOUNDARY_STRONG;
                $modifiers = 'u';
                break;

            case self::WORD_BOUNDARY_SEVERITY_MEDIUM:
                $wordBoundary = self::WORD_BOUNDARY_WEAK;
                $modifiers = 'u';
                break;

            case self::WORD_BOUNDARY_SEVERITY_LOW:
            default:
                $wordBoundary = self::WORD_BOUNDARY_NONE;
                $modifiers = '';
                break;
        }

        if ($this->isIgnoreCase()) {
            $modifiers .= 'i';
        }

        return '/(?:' . $wordBoundary . ')' . $pattern . '(?:' . $wordBoundary . ')/' . $modifiers;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function getPattern(string $value): string
    {
        if ($this->isLiteralSpaces()) {
            return preg_quote($value, '/');
        }

        return implode('\s+', array_map(
            function ($val) {
                return preg_quote($val, '/');
            },
            preg_split('/\s+/', $value)
        ));
    }

    /**
     * @param string $value
     * @return array
     */
    protected function getTokens(string $value): array
    {
        $value = trim($value);

        if ($value == '') {
            return [];
        }

        return preg_split('/\s+/', $value);
    }
}
