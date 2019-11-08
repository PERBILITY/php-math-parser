<?php

namespace MathParser;

use MathParser\Expressions\Addition;
use MathParser\Expressions\Division;
use MathParser\Expressions\Modulo;
use MathParser\Expressions\Multiplication;
use MathParser\Expressions\Number;
use MathParser\Expressions\Parenthesis;
use MathParser\Expressions\Power;
use MathParser\Expressions\Subtraction;
use MathParser\Expressions\Unary;

abstract class Expression
{
    protected $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public static function factory($value)
    {
        if (is_object($value) && $value instanceof self) {
            return $value;
        } elseif (is_numeric($value)) {
            // +0 => to number conversion (int or float)
            return new Number($value + 0);
        } elseif ($value == 'u') {
            return new Unary($value);
        } elseif ($value == '+') {
            return new Addition($value);
        } elseif ($value == '-') {
            return new Subtraction($value);
        } elseif ($value == '*') {
            return new Multiplication($value);
        } elseif ($value == '/') {
            return new Division($value);
        } elseif (in_array($value, array('(', ')'))) {
            return new Parenthesis($value);
        } elseif ($value == '^') {
            return new Power($value);
        }elseif ($value == '%') {
            return new Modulo($value);
        }elseif (strlen($value) >= 2 && $value[0] == '$') {
            return new Variable(substr($value, 1));
        }
        throw new \RuntimeException('Undefined Value ' . $value);
    }

    abstract public function operate(Stack $stack);

    public function isOperator()
    {
        return false;
    }

    public function isUnary()
    {
        return false;
    }

    public function isParenthesis()
    {
        return false;
    }

    public function isNoOp()
    {
        return false;
    }
    
    public function isVariable()
    {
        return false;
    }

    public function render()
    {
        return $this->value;
    }
}
