<?php


namespace Futape\Search\Tests\Unit\Exception;


use Futape\Search\Matcher\Exception\UnsupportedValueException;
use Futape\Search\Matcher\Token\TokenValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Futape\Search\Matcher\Exception\UnsupportedValueException
 */
class UnsupportedValueExceptionTest extends TestCase
{
    /**
     * @uses \Futape\Search\Matcher\Token\TokenValue
     */
    public function testMessage()
    {
        $this->assertEquals(
            TokenValue::class . ' value is not supported by matcher, expected \Foo\Bar\MyValue',
            (new UnsupportedValueException(new TokenValue(['foo']), '\Foo\Bar\MyValue'))->getMessage()
        );
    }
}
