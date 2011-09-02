<?php

class ExpressionCompare extends Expression 
{
    protected $op;
    protected $left;
    protected $right;
    
    public function __construct($op, $left, $right)
    {
        $this->op = $op;
        if ($this->op == '=') {
            $this->op = '==';
        } elseif ($this->op == '<>') {
            $this->op = '!=';
        }
        $this->left = $left;
        $this->right = $right;        
    }
    
    public function getValue($data)
    {
        $left = $this->left->getValue($data);
        if (is_array($left)) {
        	return $this->getArrayValue($data);
        }
        $right = $this->right->getValue($data);
        switch ($this->op) {
            case '==': {
                return (int)($left == $right);
            }
            case '!=': {
                return (int)($left != $right);
            }
            case '>': {
                return (int)($left > $right);
            }
            case '>=': {
                return (int)($left >= $right);
            }
            case '<': {
                return (int)($left < $right);
            }
            case '<=': {
                return (int)($left <= $right);
            }
            case '^=': {
                return (int)(mb_substr($left, 0, strlen($right)) == $right);
            }
            case '!^': {
                return (int)(mb_substr($left, 0, strlen($right)) != $right);
            }
            case 'like': {
            	$pattern = str_replace("%", ".*?", $right);
            	return preg_match("/".$pattern."/ui", $left);
            }
            default: {
                throw new Exception('Unknown operation "' . $this->op . '"');
            }
                
        }
    }
    
    public function getArrayValue($data)
    {
    	/**
    	 * @var array
    	 */
        $left = $this->left->getValue($data);
        $right = $this->right->getValue($data);
        switch ($this->op) {
            case '==': {
                return (int)in_array($right, $left);
            }
            case '!=': {
                return (int)!in_array($right, $left);
            }
            case '>': {
                foreach ($left as $k => $v) {
                	if ($v > $right) {
                		return 1;
                	}
                }
                return 0;
            }
            case '>=': {
                foreach ($left as $k => $v) {
                	if ($v >= $right) {
                		return 1;
                	}
                }
                return 0;
            	
            }
            case '<': {
                foreach ($left as $k => $v) {
                	if ($v < $right) {
                		return 1;
                	}
                }
                return 0;
            }
            case '<=': {
                foreach ($left as $k => $v) {
                	if ($v <= $right) {
                		return 1;
                	}
                }
                return 0;
            	
            }
            case '^=': {
                foreach ($left as $k => $v) {
                	if (mb_substr($v, 0, strlen($right)) == $right) {
                		return 1;
                	}
                }
                return 0;
            }
            case '!^': {
                foreach ($left as $k => $v) {
                	if (mb_substr($v, 0, strlen($right)) != $right) {
                		return 1;
                	}
                }
                return 0;                
            }
            case 'like': {
            	$pattern = str_replace("%", ".*?", $right);
       			foreach ($left as $k => $v) {
                	if (preg_match("/".$pattern."/ui", $v)) {
                		return 1;
                	}
                }
                return 0;            	
            }
            default: {
                throw new Exception('Unknown operation "' . $this->op . '"');
            }
                
        }
    	
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
        elseif ($this->op && !$this->right) {
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