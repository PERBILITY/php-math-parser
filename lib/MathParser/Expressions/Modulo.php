<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Options\NullHandling;

class Modulo extends Operator
{
    public function getPrecedence(): int
    {
        return 5;
    }
    
    public function operate(array &$stack, array $options)
    {
        return NullHandling::withNullHandling(
            $stack,
            $options,
            static function ($left, $right) {
                if ($right === 0) {
                    return null;
                } else {
                    return $left % $right;
                }
            }
        );
    }
}
