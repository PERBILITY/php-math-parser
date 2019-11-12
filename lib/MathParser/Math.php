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
        if(!is_string($string)){
            throw new \RuntimeException('not a string provided as formula');
        }
        $stack = $this->parse($string);
        
        return $this->run($stack);
    }
    
    /**
     * @param $string
     * @return Stack
     */
    public function parse($string)
    {
        $tokens = $this->tokenize($string);
        $output = new Stack();
        $operators = new Stack();
        
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
                $output->push($expression);
                $expectOperator = true;
            }
        }
        while (($op = $operators->pop())) {
            if ($op->isParenthesis()) {
                throw new \RuntimeException('Mismatched Parenthesis');
            }
            $output->push($op);
        }
        
        return $output;
    }
    
    public function registerVariable($name, $value)
    {
        $this->assertNumericVariable($value);
    
        $this->variables[$name] = $value;
    }
    
    public function run(Stack $stack)
    {
        $stack = $this->substituteVariables($stack, $this->variables);
        
        while (($operator = $stack->pop()) && $operator->isOperator()) {
            $value = $operator->operate($stack);
            if (!is_null($value)) {
                $stack->push(Expression::factory($value));
            }
        }
        
        return $operator ? $operator->render() : $this->render($stack);
    }
    
    protected function render(Stack $stack)
    {
        $output = '';
        while (($el = $stack->pop())) {
            $output .= $el->render();
        }
        
        if ($output) {
            return $output;
        }
        return null;
        
    }
    
    protected function parseParenthesis(Expression $expression, Stack $output, Stack $operators)
    {
        if ($expression->isOpen()) {
            $operators->push($expression);
        } else {
            $clean = false;
            while (($end = $operators->pop())) {
                if ($end->isParenthesis()) {
                    $clean = true;
                    break;
                } else {
                    $output->push($end);
                }
            }
            if (!$clean) {
                throw new \RuntimeException('Mismatched Parenthesis');
            }
        }
    }
    
    protected function parseOperator(Expression $expression, Stack $output, Stack $operators)
    {
        $end = $operators->poke();
        if (!$end) {
            $operators->push($expression);
        } elseif ($end->isOperator()) {
            do {
                if ($expression->isLeftAssoc() && $expression->getPrecedence() <= $end->getPrecedence()) {
                    $output->push($operators->pop());
                } elseif (!$expression->isLeftAssoc() && $expression->getPrecedence() < $end->getPrecedence()) {
                    $output->push($operators->pop());
                } else {
                    break;
                }
            } while (($end = $operators->poke()) && $end->isOperator());
            $operators->push($expression);
        } else {
            $operators->push($expression);
        }
    }
    
    protected function tokenize($string)
    {
        $match = preg_match('#^(\d+(\.\d+)?|\$\d+|\+|-|\(|\)|\*|/|%|\^|\s+)+$#', $string);
        
        // check to see obvious syntax mistakes (e.g. unallowed characters...)
        if (!$match) {
            throw new \RuntimeException('invalid syntax!');
        }
        $parts = preg_split('((\d+(?:\.\d+)?|\$\d+|\+|-|\(|\)|\*|/|%|\^|\s+))', $string, null, PREG_SPLIT_NO_EMPTY |
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
        $this->assertNumericVariables($variables);
        $this->variables = $variables;
    }
    
    private function substituteVariables(Stack $stack, array $variables)
    {
        $substitutedStack = new Stack();
        while ($expression = $stack->shift()) {
            if ($expression instanceof Variable) {
                $substitutedStack->push(new Number($expression->render($variables)));
            } else {
                $substitutedStack->push($expression);
            }
        }
        return $substitutedStack;
    }
    
    private function assertNumericVariables(array $variables)
    {
        foreach ($variables as $variable) {
            $this->assertNumericVariable($variable);
        }
    }
    
    private function assertNumericVariable($variable)
    {
        if (!is_numeric($variable)) {
            throw new \InvalidArgumentException('provided variable is not a number');
        }
    }
}
