<?php


namespace Futape\Search\Tests\Unit\Matcher\Fulltext;


use Futape\Search\Highlighter\HtmlHighlighter;
use Futape\Search\Highlighter\PlainHighlighter;
use Futape\Search\Matcher\Filename\FilenameMatcher;
use Futape\Search\Matcher\Filename\FilenameValue;
use Futape\Search\Matcher\Fulltext\FulltextMatcher;
use Futape\Search\Matcher\Fulltext\FulltextValue;
use Futape\Utility\Filesystem\Files;
use Futape\Utility\Filesystem\Paths;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\Fulltext\FulltextMatcher
 */
class FulltextMatcherTest extends TestCase
{
    public function testSupportedValue()
    {
        $this->assertEquals(FulltextValue::class, FulltextMatcher::SUPPORTED_VALUE);
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testIgnoreCase()
    {
        $matcher = new FulltextMatcher();
        $value = (new FulltextValue('foo BAR baz'))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, 'bar');
        $this->assertEquals('foo **BAR** baz', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->setIgnoreCase(false);

        $matcher->match($value, 'bar');
        $this->assertEquals('foo BAR baz', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testLiteralSpaces()
    {
        $matcher = new FulltextMatcher();
        $value = (new FulltextValue("foo\tbar  baz"))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, "foo\nbar");
        $this->assertEquals("**foo\tbar**  baz", $value->getHighlighted());
        $this->assertEquals(3, $value->getScore());

        $matcher->match($value, "bar\nbaz");
        $this->assertEquals("foo\t**bar  baz**", $value->getHighlighted());
        $this->assertEquals(3, $value->getScore());

        $matcher->setLiteralSpaces(true);

        $matcher->match($value, "bar\nbaz");
        $this->assertEquals("foo\tbar  baz", $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @param string $value
     * @param string $term
     * @param int $expected
     *
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     *
     * @dataProvider scoreCalculationDataProvider
     */
    public function testScoreCalculation(string $value, string $term, int $expected)
    {
        $matcher = new FulltextMatcher();
        $fulltextValue = new FulltextValue($value);

        $matcher->match($fulltextValue, $term);
        $this->assertEquals($expected, $fulltextValue->getScore());
    }

    public function scoreCalculationDataProvider(): array
    {
        return [
            'Single word, one match' => ['foo bar baz', 'bar', 1],
            'Two words, one match' => ['foo bar baz', 'bar baz', 3],
            'Two words, full match' => ['foo bar', 'foo bar', 3],
            'Three words, one match' => ['foo bar baz bam', 'foo bar baz', 4],
            'Three words, two matches' => ['foo bar baz bam foo bar baz', 'foo bar baz', 8],
            'Three words, one match, two instances of one word in term' => [
                'foo bar bar baz',
                'foo bar bar',
                4
            ],
            'Three words, two matches, two instances of one word in term' => [
                'foo bar bar baz foo bar bar',
                'foo bar bar',
                8
            ]
        ];
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testMatchExtraHighWordBoundarySeverity()
    {
        $matcher = (new FulltextMatcher())
            ->setWordBoundarySeverity(FulltextMatcher::WORD_BOUNDARY_SEVERITY_EXTRA_HIGH);
        $value = (new FulltextValue('Foo Bar,Baz'))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, 'Foo');
        $this->assertEquals('**Foo** Bar,Baz', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'Baz');
        $this->assertEquals('Foo Bar,Baz', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testMatchHighWordBoundarySeverity()
    {
        $matcher = (new FulltextMatcher())
            ->setWordBoundarySeverity(FulltextMatcher::WORD_BOUNDARY_SEVERITY_HIGH);
        $value = (new FulltextValue('Foo_Bar0Baz-Bam,42'))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, '42');
        $this->assertEquals('Foo_Bar0Baz-Bam,**42**', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'Foo');
        $this->assertEquals('**Foo**_Bar0Baz-Bam,42', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'Bar');
        $this->assertEquals('Foo_Bar0Baz-Bam,42', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());

        $matcher->match($value, 'Bam');
        $this->assertEquals('Foo_Bar0Baz-**Bam**,42', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testMatchMediumWordBoundarySeverity()
    {
        $matcher = new FulltextMatcher();
        $value = (new FulltextValue('Foo012Bar_Baz-Bam'))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, 'Foo');
        $this->assertEquals('**Foo**012Bar_Baz-Bam', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, '01');
        $this->assertEquals('Foo012Bar_Baz-Bam', $value->getHighlighted());
        $this->assertEquals(0, $value->getScore());

        $matcher->match($value, '012');
        $this->assertEquals('Foo**012**Bar_Baz-Bam', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'Baz');
        $this->assertEquals('Foo012Bar_**Baz**-Bam', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'Bam');
        $this->assertEquals('Foo012Bar_Baz-**Bam**', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testMatchLowWordBoundarySeverity()
    {
        $matcher = (new FulltextMatcher())
            ->setWordBoundarySeverity(FulltextMatcher::WORD_BOUNDARY_SEVERITY_LOW);
        $value = (new FulltextValue('Foobarbaz012bam'))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, 'Foo');
        $this->assertEquals('**Foo**barbaz012bam', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, 'Bar');
        $this->assertEquals('Foo**bar**baz012bam', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->match($value, '01');
        $this->assertEquals('Foobarbaz**01**2bam', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());
    }

    /**
     * @uses \Futape\Search\Highlighter\PlainHighlighter
     * @uses \Futape\Search\Matcher\Fulltext\FulltextValue
     */
    public function testMatchWhitespacePaddedTerm()
    {
        $matcher = (new FulltextMatcher())
            ->setWordBoundarySeverity(FulltextMatcher::WORD_BOUNDARY_SEVERITY_EXTRA_HIGH);
        $value = (new FulltextValue('Foo Bar Baz'))
            ->setHighlighter(new PlainHighlighter());

        $matcher->match($value, ' Bar ');
        $this->assertEquals('Foo** Bar **Baz', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());

        $matcher->setLiteralSpaces(true);

        $matcher->match($value, ' Bar ');
        $this->assertEquals('Foo** Bar **Baz', $value->getHighlighted());
        $this->assertEquals(1, $value->getScore());
    }
}
