<?php


namespace Futape\Search\Matcher\Exception;


use Futape\Search\Matcher\AbstractValue;
use InvalidArgumentException;
use Throwable;

class UnsupportedValueException extends InvalidArgumentException
{
    public function __construct(AbstractValue $value, string $expected, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            get_class($value) . ' value is not supported by matcher, expected ' . $expected,
            $code,
            $previous
        );
    }
}
