<?php

namespace MathParser\Expressions;

use MathParser\Stack;

class Modulo extends Operator
{
    protected $precedence = 5;

    public function operate(Stack $stack)
    {
        $left = $stack->pop()->operate($stack);
        $right = $stack->pop()->operate($stack);
    
        if($left === null || $right === null){
            return null;
        }
        
        if($left===0){
            return null;
        }else{
            return $right % $left;
        }
    }
}
