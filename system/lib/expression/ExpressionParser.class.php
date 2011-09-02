<?php

class ExpressionParser
{
	
	const WAIT_OPERAND = 0;
	const WAIT_OPERATION = 1;
	const OPERAND = 2;
	const OPERATION = 3;
	const DOUBLE_QUOTE = 4;
	const PARAM = 5;
	
	protected $string;
	protected $chars;
	protected $chars_count;
	protected $index = 0;
	protected $state = 0;
	
	public function __construct($string)
	{
		$this->string = $string;
		$this->chars = preg_split("//u", $this->string, -1, PREG_SPLIT_NO_EMPTY);
		$this->chars[] = ' ';
		$this->chars_count = count($this->chars);
		$this->index = 0;
	}
	
	/**
	 * Returns tree of the expression
	 * 
	 * @param bool $braket
	 * @return Expression
	 */
	public function parse($braket = 0)
	{
		/**
		 * @var Expression
		 */
		$expression = false;
		$prev_char = $char = "";
		$buffer = "";
		while ($this->index < $this->chars_count) {
		   $prev_char = $char;
		   $char = $this->chars[$this->index];
	       switch ($this->state) {
	           case self::WAIT_OPERAND:
	               if ($char !== ' ') {
	               	   if ($char === '"') {
	               	       $this->state = self::DOUBLE_QUOTE;
	               	       $this->index++;   
	               	   } else {
	                       $this->state = self::OPERAND;
	               	   }
	               }
	               break;
	           case self::OPERAND:
	           	   $is_op = $this->isOperation($char);
	           	   if ($char === '(') {
	           	   	   $this->index++;	
	           	       $operand = $this->parse(true);
	           	       $operand->block(true);
	           	   	   if ($expression) {
	           	   	       $expression->addExpression($operand);
	           	   	   } else {
	           	   	       $expression = $operand;
	           	   	   }
         	   	   	   $this->state = self::WAIT_OPERATION;
           	   	   	   $buffer = '';
	           	   } elseif ($braket && $char == ')') {
	           	   	   $this->index++;
	           	   	   return $expression;
	           	   } elseif ($char === ' ' || $is_op) {
	           	   	   if (is_numeric($buffer)) {
	           	   	       $operand = new ExpressionValue($buffer);
	           	   	   } else {
	           	   	   	   $operand = new ExpressionField($buffer);
	           	   	   }
	           	   	   if ($expression) {
	           	   	       $expression->addExpression($operand);
	           	   	   } else {
	           	   	       $expression = $operand;
	           	   	   }
	           	   	   if ($is_op) {
	           	   	   		if (!$buffer) {
	           	   	   			throw new Exception("Parse error on '".$op."'");
	           	   	   		}
	           	   	   		$this->state = self::OPERATION;
	           	   	   		$buffer = $char;
	           	   	   } else {
	           	   	   		$this->state = self::WAIT_OPERATION;
	           	   	   		$buffer = '';
	           	   	   }
	           	   } else {
	           	       $buffer .= $char;
	           	   }
	           	   $this->index++;
	           	   break;
	           case self::WAIT_OPERATION:
	           	   if ($char !== ' ') {
	           	       $this->state = self::OPERATION;
	           	   } else {
	           	       $this->index++;
	           	   }
	           	   break;
	           case self::PARAM:
	           		
	           		break;
	           case self::OPERATION: 
	           	   if ($char === ' ') {
	           	       $expression = $expression->addOp($buffer);
	           	       $this->state = self::WAIT_OPERAND;
	           	       $buffer = "";
	           	   } else {
	           	   	   $buffer .= $char;
	           	   }
	           	   $this->index++;
	               break;
	           case self::DOUBLE_QUOTE:
	           	   if ($char == '"' && $prev_char !== "\\") {
	           	   	   $operand = new ExpressionValue($buffer, '"');
	           	   	   if ($expression) {
	           	   	       $expression->addExpression($operand);
	           	   	   } else {
	           	   	   	   $expression = $operand;
	           	   	   }
	           	   	   $this->state = self::WAIT_OPERATION;
	           	   	   $buffer = "";
	           	   } else {
	           	       $buffer .= $char;
	           	   }
	           	   $this->index++;
	           	   break;
	       }		
		}
		return $expression;
	}
	
	public function isOperation($char) 
	{
		return isset(Expression::$operations[$char]);
	}
		
}