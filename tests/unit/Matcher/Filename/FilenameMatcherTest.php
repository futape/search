<?php


namespace Futape\Search\Tests\Unit\Matcher\Filename;


use Futape\Search\Highlighter\HtmlHighlighter;
use Futape\Search\Matcher\Filename\FilenameMatcher;
use Futape\Search\Matcher\Filename\FilenameValue;
use Futape\Utility\Filesystem\Files;
use Futape\Utility\Filesystem\Paths;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\Filename\FilenameMatcher
 */
class FilenameMatcherTest extends TestCase
{
    /**
     * Always ends with a slash
     *
     * @var string
     */
    protected static $documentRoot;

    public static function setUpBeforeClass(): void
    {
        $documentRoot = tempnam(sys_get_temp_dir(), 'filenamematchertest');
        Files::remove($documentRoot);
        mkdir($documentRoot);
        Paths::setDocumentRoot($documentRoot);
        self::$documentRoot = rtrim(Paths::getDocumentRoot(), '/') . '/';
    }

    public static function tearDownAfterClass(): void
    {
        Files::remove(Paths::getDocumentRoot());
        Paths::setDocumentRoot(null);
        self::$documentRoot = null;
    }

    public function testSupportedValue()
    {
        $this->assertEquals(FilenameValue::class, FilenameMatcher::SUPPORTED_VALUE);
    }

    /**
     * @uses \Futape\Search\Highlighter\HtmlHighlighter
     * @uses \Futape\Search\Matcher\Filename\FilenameValue
     */
    public function testMatchBasename()
    {
        $matcher = new FilenameMatcher();
        $value = (new FilenameValue(self::$documentRoot . 'f&o/b&r.txt'))
            ->setHighlighter(new HtmlHighlighter());

        $matcher->match($value, 'b&r.txt');
        $this->assertEquals('/f&amp;o/<mark>b&amp;r.txt</mark>', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'f&o');
        $this->assertEquals('/f&amp;o/b&amp;r.txt', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\HtmlHighlighter
     * @uses \Futape\Search\Matcher\Filename\FilenameValue
     */
    public function testMatchFilename()
    {
        $matcher = new FilenameMatcher();
        $highlighter = new HtmlHighlighter();
        $value = (new FilenameValue(self::$documentRoot . 'f&o/b&r.txt'))
            ->setHighlighter($highlighter);

        $matcher->match($value, 'b&r');
        $this->assertEquals('/f&amp;o/<mark>b&amp;r</mark>.txt', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        // Assert filename doesn't match if path points to a directory
        mkdir(self::$documentRoot . 'f&o/b&r.txt', 0777, true);
        $matcher->match($value, 'b&r');
        $this->assertEquals('/f&amp;o/b&amp;r.txt', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
        Files::remove(self::$documentRoot . 'f&o/b&r.txt');

        // Assert filename doesn't match if path ends with a slash (indicating directory)
        $value = (new FilenameValue(self::$documentRoot . 'f&o/b&r.txt/'))
            ->setHighlighter($highlighter);
        $matcher->match($value, 'b&r');
        $this->assertEquals('/f&amp;o/b&amp;r.txt', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }
}
