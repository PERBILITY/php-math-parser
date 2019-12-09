<?php
namespace MathParser;

use MathParser\Expressions\Number;
use MathParser\Expressions\Unary;

class Math
{
    protected $variables = [];
    
    /**
     * @param $string
     * @return string
     */
    public function evaluate($string)
    {
        if (!is_string($string)) {
            throw new \RuntimeException('not a string provided as formula');
        }
        $stack = $this->parse($string);
        
        return $this->run($stack);
    }
    
    /**
     * @param $string
     * @return Expression[] $output
     */
    public function parse($string)
    {
        $tokens = $this->tokenize($string);
        $output = [];
        $operators = [];
        
        $expectOperator = false;
        
        foreach ($tokens as $token) {
            $expression = Expression::factory($token);
            if ($expression->isOperator()) {
                if ($expectOperator) {
                    $this->parseOperator($expression, $output, $operators);
                    $expectOperator = false;
                } else {
                    if (!$expectOperator && $token == '-') {
                        $this->parseOperator(new Unary('u'), $output, $operators);
                    } else {
                        throw new \RuntimeException('expected number or variable but found: ' . get_class($expression));
                    }
                }
            } elseif ($expression->isParenthesis()) {
                $this->parseParenthesis($expression, $output, $operators);
                if ($expression->isOpen()) {
                    if ($expectOperator) {
                        throw new \RuntimeException('expected operator but found: ' . get_class($expression));
                    }
                    $expectOperator = false;
                } else {
                    $expectOperator = true;
                }
            } else {
                if ($expectOperator) {
                    throw new \RuntimeException('expected operator but found: ' . get_class($expression));
                }
                $output[] = $expression;
                $expectOperator = true;
            }
        }
        while (($op = array_pop($operators))) {
            if ($op->isParenthesis()) {
                throw new \RuntimeException('Mismatched Parenthesis');
            }
            $output[] = $op;
        }
        
        return $output;
    }
    
    public function registerVariable($name, $value)
    {
        $this->assertVariableIsNumber($value);
    
        $this->variables[$name] = $value;
    }
    
    public function run(array &$stack)
    {
        $this->substituteVariables($stack, $this->variables);
        
        while (($operator = array_pop($stack)) && $operator->isOperator()) {
            $value = $operator->operate($stack);
            if (!is_null($value)) {
                $stack[] = Expression::factory($value);
            }
        }
        
        return $operator ? $operator->render() : $this->render($stack);
    }
    
    private function render(array &$stack)
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
    
    private function parseParenthesis(Expression $expression, array &$output, array &$operators)
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
    
    private function parseOperator(Expression $expression, array &$output, array &$operators)
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
    
    private function tokenize($string)
    {
        $match = preg_match('#^(\d+(\.\d+)?|\$\d+|\$\w+|\+|-|\(|\)|\*|/|%|\^|\s+)+$#', $string);
        
        // check to see obvious syntax mistakes (e.g. unallowed characters...)
        if (!$match) {
            throw new \RuntimeException('invalid syntax!');
        }
        $parts = preg_split('((\d+(?:\.\d+)?|\$\d+|\$\w+|\+|-|\(|\)|\*|/|%|\^|\s+))', $string, null, PREG_SPLIT_NO_EMPTY |
            PREG_SPLIT_DELIM_CAPTURE);
        $parts = array_filter(array_map('trim', $parts), function ($val) {
            return $val !== '';
        });
        
        return $parts;
    }
    
    /**
     * removes any variables
     */
    public function clearVariables()
    {
        $this->variables = [];
    }
    
    /**
     * expects a one dimensional array of key-value pairs
     *
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->assertVariablesAreNumbers($variables);
        $this->variables = $variables;
    }
    
    private function substituteVariables(array &$stack, array $variables)
    {
        foreach ($stack as &$expression) {
            if ($expression instanceof Variable) {
                $expression = new Number($expression->render($variables));
            }
        }
    }
    
    private function assertVariablesAreNumbers(array $variables)
    {
        foreach ($variables as $variable) {
            $this->assertVariableIsNumber($variable);
        }
    }
    
    private function assertVariableIsNumber($variable)
    {
        if (!is_int($variable) && !is_float($variable)) {
            throw new \InvalidArgumentException('provided variable is not a number');
        }
    }
}
