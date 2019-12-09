<?php

namespace MathParser\Expressions;

use MathParser\Stack;
use MathParser\Expression;

class Number extends Expression
{
    public function __construct($value)
    {
        parent::__construct(+$value);
    }
    
    public function operate(array &$stack)
    {
        return $this->value;
    }
}
