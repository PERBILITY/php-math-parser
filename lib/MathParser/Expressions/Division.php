<?php

namespace MathParser\Expressions;

use MathParser\Stack;

class Division extends Operator
{
    protected $precedence = 5;

    public function operate(Stack $stack)
    {
        $left = $stack->pop()->operate($stack);
        $right = $stack->pop()->operate($stack);
    
        if($left === null || $right === null){
            return null;
        }
        
        if($left === 0){
            return null;
        }
        
        return $right / $left;
    }
}
