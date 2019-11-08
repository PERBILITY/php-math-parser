<?php

namespace MathParser\Expressions;

use MathParser\Stack;

class Power extends Operator
{
    protected $precedence = 6;

    public function operate(Stack $stack)
    {
        $right = $stack->pop()->operate($stack);
        $left = $stack->pop()->operate($stack);
        
        if($left === null || $right === null){
            return null;
        }
        return pow($left, $right);
    }
}
