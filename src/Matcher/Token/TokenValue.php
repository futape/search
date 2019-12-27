<?php


namespace Futape\Search\Matcher\Token;


use Futape\Search\Matcher\AbstractArrayValue;
use Futape\Search\Matcher\AbstractValue;

class TokenValue extends AbstractArrayValue
{
    /**
     * @param mixed $value
     * @return AbstractValue
     */
    protected function setValue($value): AbstractValue
    {
        array_walk(
            $value,
            function (&$val) {
                $val = (string)$val;
            }
        );

        return parent::setValue($value);
    }
}
