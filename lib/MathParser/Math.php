<?php

declare(strict_types=1);

namespace MathParser;

use MathParser\Exceptions\InvalidSyntaxException;
use MathParser\Expressions\Number;
use MathParser\Expressions\Unary;

class Math
{
    
    /**
     * @param string $string
     * @param int[]|float[] $variables
     * @return string
     */
    public static function evaluate(string $string, array $variables = [], array $options = [])
    {
        $stack = self::parse($string);
        self::substituteVariables($stack, $variables);
        return self::run($stack, $options);
    }
    
    /**
     * @param string $string
     * @return Expression[] $output
     */
    public static function parse(string $string): array
    {
        $tokens = self::tokenize($string);
        $output = [];
        $operators = [];
        
        $expectOperator = false;
        
        foreach ($tokens as $token) {
            $expression = Expression::factory($token);
            if ($expression->isOperator()) {
                if ($expectOperator) {
                    self::parseOperator($expression, $output, $operators);
                    $expectOperator = false;
                } elseif (!$expectOperator && $token === '-') {
                    self::parseOperator(new Unary('u'), $output, $operators);
                } else {
                    throw new InvalidSyntaxException('expected number or variable but found: ' . get_class($expression));
                }
            } elseif ($expression->isParenthesis()) {
                self::parseParenthesis($expression, $output, $operators);
                if ($expression->isOpen()) {
                    if ($expectOperator) {
                        throw new InvalidSyntaxException('expected operator but found: ' . get_class($expression));
                    }
                    $expectOperator = false;
                } else {
                    $expectOperator = true;
                }
            } else {
                if ($expectOperator) {
                    throw new InvalidSyntaxException('expected operator but found: ' . get_class($expression));
                }
                $output[] = $expression;
                $expectOperator = true;
            }
        }
        while (($op = array_pop($operators))) {
            if ($op->isParenthesis()) {
                throw new InvalidSyntaxException('Mismatched Parenthesis');
            }
            $output[] = $op;
        }
        
        return $output;
    }
    
    /**
     * @param Expression[] $stack
     * @param array $options
     * @return string|null
     */
    public static function run(array $stack, array $options = [])
    {
        $defaultOptions = [
            'null_handling' => 'strict',
            'fallback' => 0
        ];
        $options = $options + $defaultOptions;
        
        assert(in_array($options['null_handling'], ['strict', 'skip', 'loose', 'fallback']));
        assert(is_int($options['fallback']) || is_float($options['fallback']));
        
        while (($operator = array_pop($stack)) && $operator->isOperator()) {
            $value = $operator->operate($stack, $options);
            if ($value !== null) {
                $stack[] = Expression::factory($value);
            }
        }
        
        return $operator ? $operator->render() : self::render($stack);
    }
    
    public static function getDistinctVariables(array $stack)
    {
        $variables = [];
    
        foreach ($stack as $expression) {
            if ($expression instanceof Variable) {
                $variableName = $expression->getName();
                if (!in_array($variableName, $variables, true)) {
                    $variables[] = $variableName;
                }
            }
        }
        
        return $variables;
    }
    
    private static function render(array &$stack)
    {
        $output = '';
        while (($el = array_pop($stack))) {
            $output .= $el->render();
        }
        
        if ($output) {
            return $output;
        }
        return null;
    }
    
    private static function parseParenthesis(Expression $expression, array &$output, array &$operators)
    {
        if ($expression->isOpen()) {
            $operators[] = $expression;
        } else {
            $clean = false;
            while (($end = array_pop($operators))) {
                if ($end->isParenthesis()) {
                    $clean = true;
                    break;
                } else {
                    $output[] = $end;
                }
            }
            if (!$clean) {
                throw new \RuntimeException('Mismatched Parenthesis');
            }
        }
    }
    
    private static function parseOperator(Expression $expression, array &$output, array &$operators)
    {
        $end = end($operators);
        if (!$end) {
            $operators[] = $expression;
        } elseif ($end->isOperator()) {
            do {
                if ($expression->isLeftAssoc() && $expression->getPrecedence() <= $end->getPrecedence()) {
                    $output[] = array_pop($operators);
                } elseif (!$expression->isLeftAssoc() && $expression->getPrecedence() < $end->getPrecedence()) {
                    $output[] = array_pop($operators);
                } else {
                    break;
                }
            } while (($end = end($operators)) && $end->isOperator());
            $operators[] = $expression;
        } else {
            $operators[] = $expression;
        }
    }
    
    private static function tokenize(string $string)
    {
        $match = preg_match('#^(\d+(\.\d+)?|\$\d+|\$\w+|\+|-|\(|\)|\*|/|%|\^|\s+)+$#', $string);
        
        // check to see obvious syntax mistakes (e.g. unallowed characters...)
        if (!$match) {
            throw new InvalidSyntaxException('invalid syntax!');
        }
        $parts = preg_split('((\d+(?:\.\d+)?|\$\d+|\$\w+|\+|-|\(|\)|\*|/|%|\^|\s+))', $string, -1, PREG_SPLIT_NO_EMPTY |
            PREG_SPLIT_DELIM_CAPTURE);
        $parts = array_filter(array_map('trim', $parts), static function ($val) {
            return $val !== '';
        });
        
        return $parts;
    }
    
    public static function substituteVariables(array &$stack, array $variables): void
    {
        self::assertVariablesAreNumbersOrNull($variables);
        foreach ($stack as &$expression) {
            if ($expression instanceof Variable) {
                $expression = new Number($expression->render($variables));
            }
        }
    }
    
    private static function assertVariablesAreNumbersOrNull(array $variables)
    {
        foreach ($variables as $variable) {
            self::assertVariableIsNumberOrNull($variable);
        }
    }
    
    private static function assertVariableIsNumberOrNull($variable)
    {
        if (!is_int($variable) && !is_float($variable) && !($variable === null)) {
            throw new \InvalidArgumentException('provided variable is not a number or null. found: ' .
                gettype($variable));
        }
    }
}
