<?php


namespace Futape\Search\Matcher\Fulltext;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Search\Matcher\TermCollectionAware;
use Futape\Search\TermCollection;

class FulltextMatcher extends AbstractMatcher implements TermCollectionAware
{
    const SUPPORTED_VALUE = FulltextValue::class;
    const SUPPORTS_TERM_COLLECTION = true;

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

//            'Three words with two instances of one word in term (word boundary)' => ['foo_bar bar bam', 'foo_bar bar', 4],
//            // 1 + 1*1 + 2*1
//
//            // Compare to above! Is this correct?
//            // How to express priority/power/weight of term?
//            /*
//             * 1. Count of tokens (space-separated) or words (impossible if word boundary = none) in term
//             * 2. Count of occurrences per token in term
//             *    + Beware to count a token only once for all passed terms when implementing TermCollection
//             * 3. Size of portion the term has in relation to value to search in (= size of term)
//             *    + may result in a float, but integers are required
//             * >>> 4. Add number of found occurrences of term in search string for each token in term (similar to 1.)
//             *    + Beware to count a token only once for all passed terms when implementing TermCollection
//             *      + When searching for the single token in whole search string, the count may likely be higher
//             *    + ~Only required for unique tokens in term~ Do for *every* token in term - even duplicated
//             *    + Value added per token must not be greater than possible when searching for that single token the
//             *      search string => not possible if implemented as described above
//             *
//             * Whenever adding per token, do so only if more than 1 token exists in term
//             */
//            'Three words (word boundary)' => ['foo_bar baz bam', 'foo_bar baz', 3],
//            // 1 + 1*1 + 1*1
                $termTokens = $this->getTokens($term);

                if (count($termTokens) > 1) {
                    foreach (array_count_values($termTokens) as $termToken => $termTokenNumber) {
                        $termScores[$termToken] = max($termScores[$termToken] ?? 0, $termTokenNumber * $matchesNumber);
                    }
                }

                foreach ($matches as $match) {
                    $highlightAreas[] = $match[0][1];
                    $highlightAreas[] = -($match[0][1] + strlen($match[0][0]));
                }
            }
        }

        $score += array_sum($termScores);

        if (count($highlightAreas) > 0) {
            usort(
                $highlightAreas,
                function ($a, $b) {
                    if (abs($a) == abs($b)) {
                        return $a < 0 ? -1 : 1;
                    } else {
                        return abs($a) - abs($b);
                    }
                }
            );

            $highlighted = '';
            $pointer = 0;

            foreach ($highlightAreas as $position) {
                if ($position >= 0) {
                    $highlighted .= $highlighter->lowlight(substr($value, $pointer, $position - $pointer));
                } else {
                    $highlighted .= $highlighter->highlight(substr($value, $pointer, abs($position) - $pointer));
                }

                $pointer = abs($position);
            }

            $highlighted .= $highlighter->lowlight(substr($value, $pointer));
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
     *
     * @todo Consider trimming the term if literal spaces is false
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
        if ($value == '') {
            return [];
        }

        return preg_split('/\s+/', $value);
    }
}
