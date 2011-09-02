<?php

class ExpressionOr extends Expression 
{
    protected $left;
    protected $right;
    protected $op = 'or';
    
    public function __construct($left, $right)
    {
        $this->left = $left;
        $this->right = $right;
    }
    
    public function getValue($values)
    {
        return ($this->left->getValue($values) || $this->right->getValue($values));
    }
            
    public function getFields()
    {
        $left_fields = $this->left->getFields();
        $right_fields = $this->right->getFields();
        
        return array_merge($left_fields, $right_fields);
    }
    
    public function addExpression($expression)
    {
        if (!$this->left) {
            $this->left = $expression;
        }
        elseif (!$this->right) {
            $this->right = $expression;
        }
        elseif ($this->right) {
            $this->right->addExpression($expression);
        }
    }    
    
    public function addOp($op) 
    {
        if (!$this->block && self::getPriority($op) < self::getPriority($this->op)) {
            $this->right = $this->right->addOp($op);
            return $this;
        } else {
            return parent::addOp($op);
        }
    }
    
    
}

?>