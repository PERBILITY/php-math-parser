<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Options\NullHandling;

class Power extends Operator
{
    
    public function getPrecedence(): int
    {
        return 6;
    }
    
    public function isLeftAssoc(): bool
    {
        return false;
    }
    
    public function operate(array &$stack, array $options)
    {
        return NullHandling::withNullHandling(
            $stack,
            $options,
            static function ($left, $right) {
                return $left ** $right;
            }
        );
    }
}
