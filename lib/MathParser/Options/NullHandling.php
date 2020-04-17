<?php

declare(strict_types=1);

namespace MathParser\Options;

class NullHandling
{
    public static function withNullHandling(&$stack, $options, $callback)
    {
        $right = array_pop($stack)->operate($stack, $options);
        $left = array_pop($stack)->operate($stack, $options);
    
        if ($options['null_handling'] === 'strict') {
            if ($left === null || $right === null) {
                return null;
            }
        } elseif ($options['null_handling'] === 'fallback') {
            if ($left === null) {
                $left = $options['fallback'];
            }
            if ($right === null) {
                $right = $options['fallback'];
            }
        } elseif ($options['null_handling'] === 'loose') {
            if ($left === null) {
                $left = 0;
            }
            if ($right === null) {
                $right = 0;
            }
        } elseif ($options['null_handling'] === 'skip') {
            if ($right === null) {
                return $left;
            }
            if ($left === null) {
                return $right;
            }
        }
        
        return $callback($left, $right);
    }
}