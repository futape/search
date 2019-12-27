<?php


namespace Futape\Search\Tests\Unit\Matcher\Token;


use Futape\Search\Matcher\Token\TokenValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\Token\TokenValue
 */
class TokenValueTest extends TestCase
{
    public function testValueIsStringArray()
    {
        $value = new TokenValue(['foo', 42, null]);

        $this->assertSame(['foo', '42', ''], $value->getValue());
    }
}
