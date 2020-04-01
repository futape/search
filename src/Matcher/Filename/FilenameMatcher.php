<?php


namespace Futape\Search\Matcher\Filename;


use Futape\Search\Highlighter\HighlighterInterface;
use Futape\Search\Matcher\AbstractMatcher;
use Futape\Utility\Filesystem\Paths;
use Futape\Utility\String\Strings;

/**
 * @todo Consider renaming to UriFilename/-PathMatcher (or similar) since it only supports paths below document root and
 *       already highlights as URI paths. Maybe remove URI behaviour and handle any paths and return them normalized or
 *       something.
 */
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
        if (!is_string($term)) {
            return;
        }

        $path = Paths::toUrlPath($value, false, false);

        if ($path == '/') {
            return;
        }

        $basenamePosition = mb_strrpos($path, '/') + 1;
        $pathinfo = pathinfo($path);

        if ($pathinfo['basename'] == $term) {
            // Match against last path segment (directory or file)
            $highlightArea = [$basenamePosition, -($basenamePosition + mb_strlen($pathinfo['basename']))];
            $score++;
        } elseif (
            $pathinfo['filename'] != '' &&
            !Strings::endsWith($value, '/') &&
            !is_dir($value) &&
            $pathinfo['filename'] == $term
        ) {
            // Match against filename (ignored if value ends with a slash, indicating a directory, or points to one)
            $highlightArea = [$basenamePosition, -($basenamePosition + mb_strlen($pathinfo['filename']))];
            $score++;
        } else {
            return;
        }

        $highlighted = $highlighter->highlightAreas($path, $highlightArea);
    }
}
