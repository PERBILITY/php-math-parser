<?php

namespace MathParser\Expressions;

use MathParser\Stack;

class Addition extends Operator
{
    protected $precedence = 4;

    public function operate(Stack $stack)
    {
        $left = $stack->pop()->operate($stack);
        $right = $stack->pop()->operate($stack);
    
        if($left === null || $right === null){
            return null;
        }
        
        return $left + $right;
    }
}
