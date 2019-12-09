<?php

namespace MathParser\Expressions;

class Subtraction extends Operator
{
    protected $precedence = 4;

    public function operate(array &$stack)
    {
        $left = array_pop($stack)->operate($stack);
        $right = array_pop($stack)->operate($stack);
    
        if ($left === null || $right === null) {
            return null;
        }
        
        return $right - $left;
    }
}
