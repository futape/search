<?php


namespace Futape\Search\Matcher\Filename;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Utility\Filesystem\Paths;
use Futape\Utility\String\Strings;

class FilenameMatcher extends AbstractMatcher
{
    const SUPPORTED_VALUE = FilenameValue::class;

    /**
     * @param mixed $value
     * @param mixed $term
     * @param HighlighterInterface $highlighter
     * @param mixed $highlighted
     * @param int $score
     */
    protected function matchValue($value, $term, HighlighterInterface $highlighter, &$highlighted, int &$score): void
    {
        $pathinfo = pathinfo($value);

        if ($pathinfo['basename'] === $term) {
            // Match against last path segment (directory or file)
            $highlighted = $highlighter->highlight($pathinfo['basename']);
            $score++;
        } elseif (
            $pathinfo['filename'] != '' &&
            !Strings::endsWith($value, '/') &&
            !is_dir($value) &&
            $pathinfo['filename'] === $term
        ) {
            // Match against filename (ignored if value ends with a slash, indicating a directory)
            $highlighted = $highlighter->highlight($pathinfo['filename']);
            if ($pathinfo['extension'] !== null) {
                $highlighted .= $highlighter->lowlight('.' . $pathinfo['extension']);
            }
            $score++;
        } else {
            return;
        }

        $highlighted = $highlighter->lowlight(rtrim(Paths::toUrlPath($pathinfo['dirname'], false, false), '/') . '/') .
            $highlighted;
    }
}
