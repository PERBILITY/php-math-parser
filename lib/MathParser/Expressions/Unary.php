<?php

declare(strict_types=1);

namespace MathParser\Expressions;

class Unary extends Operator
{
    public function isUnary(): bool
    {
        return true;
    }
    
    public function getPrecedence(): int
    {
        return 7;
    }
    
    public function operate(array &$stack, array $options)
    {
        //the operate here should always be returning a value alone
        $next = array_pop($stack)->operate($stack, $options);
        
        if ($next === null) {
            return null;
        }
        
        return -$next;
    }
}
