<?php

declare(strict_types=1);

namespace MathParser;

use RuntimeException;

class Variable extends Expression
{
    public function render(array $variables = [])
    {
        if (isset($variables[$this->value])) {
            return $variables[$this->value];
        } else {
            return null;
        }
    }
    
    public function getName(): string
    {
        return $this->value;
    }
    
    public function isVariable(): bool
    {
        return true;
    }
    
    public function operate(array &$stack, array $options)
    {
        throw new RuntimeException('variable not instantiated');
    }
}