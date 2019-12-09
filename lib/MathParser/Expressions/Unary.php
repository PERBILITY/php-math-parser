<?php

namespace MathParser\Expressions;

use MathParser\Stack;

class Unary extends Operator
{
    protected $precedence = 7;

    public function isUnary()
    {
        return true;
    }

    public function operate(array &$stack)
    {
        //the operate here should always be returning a value alone
        $next = array_pop($stack)->operate($stack);
        
        if($next === null){
            return null;
        }

        return -$next;
    }
}
