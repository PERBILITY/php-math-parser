<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Expression;

class Parenthesis extends Expression
{

    public function operate(array &$stack, array $options = [])
    {
    }

    public function getPrecedence(): int
    {
        return 6;
    }

    public function isNoOp(): bool
    {
        return true;
    }

    public function isParenthesis(): bool
    {
        return true;
    }

    public function isOpen(): bool
    {
        return $this->value === '(';
    }
}
