<?php


namespace Futape\Search\Tests\Unit\Matcher\Filename;


use Futape\Search\Matcher\Filename\FilenameValue;
use Futape\Search\Matcher\Filename\InvalidPathException;
use Futape\Utility\Filesystem\Files;
use Futape\Utility\Filesystem\Paths;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\Filename\FilenameValue
 */
class FilenameValueTest extends TestCase
{
    /**
     * Always ends with a slash
     *
     * @var string
     */
    protected static $documentRoot;

    public static function setUpBeforeClass(): void
    {
        $documentRoot = tempnam(sys_get_temp_dir(), 'filenamevaluetest');
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

    public function testValueIsNormalized()
    {
        $value = new FilenameValue(self::$documentRoot . 'foo//bar/../baz/./bam');

        $this->assertEquals(self::$documentRoot . 'foo/baz/bam', $value->getValue());
    }

    public function testPreventPathsOutsideDocumentRoot()
    {
        $this->expectException(InvalidPathException::class);

        new FilenameValue('/foo');
    }

    public function testHighlighted()
    {
        $value = new FilenameValue(self::$documentRoot . 'fo?o/bar/');

        // Assert document root is removed
        // Assert not URL-encoded
        // Assert no trailing slashes
        $this->assertEquals('/fo?o/bar', $value->getHighlighted());
    }
}
