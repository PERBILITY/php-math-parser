<?php

namespace MathParser\Expressions;

class Multiplication extends Operator
{
    protected $precedence = 5;

    public function operate(array &$stack)
    {
        $left = array_pop($stack)->operate($stack);
        $right = array_pop($stack)->operate($stack);
        if ($left === null || $right === null) {
            return null;
        }
        return $left * $right;
    }
}
