<?php

/**
 * Class for storing expression
 *
 */
abstract class Expression 
{
    /**
     * true if this expression is single block
     */
    protected $block = false;
    
    /**
     * Return priority for the operation
     * 
     * @var string $op - operation
     * 
     * @return int 
     */
    
    public static $operations = array(
    	'not' => 1,
    	'*' => 2,
    	'/' => 2,
    	'+' => 3,
    	'-' => 3,
    	'in' => 4,
    	'=' => 5,
    	'==' => 5,
    	'!=' => 5,
    	'<>' => 5,
    	'like' => 5,
    	'and' => 6,
    	'or' => 7
    );
    
    public static function getPriority($op) 
    {
    	$op = strtolower($op);
        return self::$operations[$op];
		
    }
    
    /**
     * Set this expression as block
     * 
     * @var bool $block
     */
    public function block($block = true)
    {
        $this->block = $block;
    }
    
    /**
     * Return true if expression is value
     * 
     * @return bool  
     */
    public function isValue()
    {
        return false;
    }
    
    
    public function getValue($data)
    {
    	return false;
    }
    
    public function getFields()
    {
    	return array();
    }
    
            
    /**
     * Добавляет операцию к выражению
     * 
     * @var string $op
     * 
     * @return Expression 
     */
    public function addOp($op)
    {
        $op = strtolower($op);
        switch ($op) {
            // Arithmetic operations
            case '+':
            case '-':
            case '*': 
            case '/': {
                $expression = new ExpressionsArithmetic($op, $this, false);
                break;
            }
            
            // Comparing operations
            case '=':
            case '==': 
            case '!=': 
            case '>': 
            case '>=': 
            case '<': 
            case '<=':
            case '!^':
            case '^=': 
            case 'like':   {
                $expression = new ExpressionCompare($op, $this, false);
                break;
            }
            
            // Operation boolean AND
            case 'and': {
                $expression = new ExpressionAnd($this, false);
                break;
            }
            
            // Operarion boolean OR
            case 'or': {
                $expression = new ExpressionOr($this, false);
                break;
            }       

            // Operation IN
            case 'in': {
                $expression = new ExpressionIn($this, false);
                break;
            }      
                
            default: {
                throw new Exception('Unknown operation "' . $op . '"');
            }
        }
        
        return $expression;
    }    
}

?>