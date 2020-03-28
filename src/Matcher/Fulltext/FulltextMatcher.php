<?php


namespace Futape\Search\Matcher\Fulltext;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;

class FulltextMatcher extends AbstractMatcher
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
     * @param mixed $value
     * @param mixed $term
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue($value, $term, HighlighterInterface $highlighter, &$highlighted, int &$score): void
    {
        if (!is_string($term)) {
            return;
        }

        $highlightAreas = [];
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
            $score += $matchesNumber;

            $termTokensNumber = count($this->getTokens($term));
            if ($termTokensNumber > 1) {
                $score += $termTokensNumber * $matchesNumber;
            }

            foreach ($matches as $match) {
                $highlightAreas[] = $match[0][1];
                $highlightAreas[] = -($match[0][1] + strlen($match[0][0]));
            }
        }

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
