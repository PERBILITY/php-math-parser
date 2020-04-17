<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Options\NullHandling;

class Addition extends Operator
{
    public function getPrecedence(): int
    {
        return 4;
    }
    
    public function operate(array &$stack, array $options)
    {
        return NullHandling::withNullHandling(
            $stack,
            $options,
            static function ($left, $right) {
                return $left + $right;
            }
        );
    }
}
