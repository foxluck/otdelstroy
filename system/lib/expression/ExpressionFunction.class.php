<?php

class ExpressionFunction extends Expression 
{
    
    protected $block = true;
    protected $params;
    protected $op = 'function';
    
    public function __construct($params)
    {
        $this->params = $params;
    }
    
    public function getValue($data) 
    {
        return false;
    }
    
    public function getFields()
    {
    	$result = array();
        foreach ($this->params as $param) {
        	$fields = $param->getFields();
        	if ($fields) {
        		$result = array_merge($result, $fields);
        	}
        }
        return $result;
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