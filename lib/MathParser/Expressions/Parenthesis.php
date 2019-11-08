<?php

namespace MathParser\Expressions;

use MathParser\Stack;
use MathParser\Expression;

class Parenthesis extends Expression
{
    protected $precedence = 6;

    public function operate(Stack $stack)
    {
    }

    public function getPrecedence()
    {
        return $this->precedence;
    }

    public function isNoOp()
    {
        return true;
    }

    public function isParenthesis()
    {
        return true;
    }

    public function isOpen()
    {
        return $this->value == '(';
    }
}
