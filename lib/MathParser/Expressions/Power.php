<?php

namespace MathParser\Expressions;

use MathParser\Stack;

class Power extends Operator
{
    protected $precedence = 6;

    public function operate(array &$stack)
    {
        $right = array_pop($stack)->operate($stack);
        $left = array_pop($stack)->operate($stack);
        
        if($left === null || $right === null){
            return null;
        }
        return pow($left, $right);
    }
}
