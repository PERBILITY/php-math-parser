<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Expression;

class Number extends Expression
{
    public function operate(array &$stack, $options)
    {
        return $this->value;
    }
}
