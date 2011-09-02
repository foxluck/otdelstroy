<?php

class ExpressionIn extends Expression 
{
    
    protected $block = true;
    protected $left;
    protected $right;
    protected $op = 'in';
    
    public function __construct($left, $right = false)
    {
        $this->left = $left;
        if (is_array($right)) {
            $this->right = $right;
        }
    }
    
    public function getValue($values) 
    {
        $left = $this->left->getValue($values);
        if ($this->right) {
            foreach ($this->right as $ex) {
                if ($left == $ex->getValue($values)) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function getFields()
    {
        return $this->left->getFields();
    }        
    
    public function addExpression($expression)
    {
        if (!$this->left) {
            $this->left = $expression;
        }
        elseif ($this->op) {
            $this->right[] = $expression;
        }
    }    
    
}

?>