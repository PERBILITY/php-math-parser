<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Options\NullHandling;

class Division extends Operator
{
    public function getPrecedence(): int
    {
        return 5;
    }

    public function operate(array &$stack, $options)
    {
        return NullHandling::withNullHandling(
            $stack,
            $options,
            static function ($left, $right) {
                // loose equality to catch 0, 0.0,... or any falsy value
                if ($right == 0) {
                    return null;
                }

                return $left / $right;
            }
        );
    }
}
