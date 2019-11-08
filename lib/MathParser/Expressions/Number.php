<?php

namespace MathParser\Expressions;

use MathParser\Stack;
use MathParser\Expression;

class Number extends Expression
{
    public function operate(Stack $stack)
    {
        return $this->value;
    }
}
