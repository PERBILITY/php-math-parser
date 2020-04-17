<?php

declare(strict_types=1);

namespace MathParser\Expressions;

use MathParser\Expression;

abstract class Operator extends Expression
{
    protected $leftAssoc = true;

    abstract public function getPrecedence(): int;

    public function isLeftAssoc(): bool
    {
        return $this->leftAssoc;
    }

    public function isOperator(): bool
    {
        return true;
    }
}
