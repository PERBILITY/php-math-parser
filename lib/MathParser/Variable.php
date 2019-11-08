<?php
namespace MathParser;

use RuntimeException;

class Variable extends Expression
{
    public function render($variables = [])
    {
        if(isset($variables[$this->value])){
            return $variables[$this->value];
        }else{
            return null;
        }
    }
    
    public function isVariable()
    {
        return true;
    }
    
    public function operate(Stack $stack)
    {
        throw new RuntimeException('variable not instantiated');
    }
}