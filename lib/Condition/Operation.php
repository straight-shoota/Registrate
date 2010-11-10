<?php

/**
 * @author Johannes
 *
 */
class Condition_Operation
extends Condition {
	/**
	 * @var string|array
	 */
	protected $field;
	/**
	 * @var string
	 */
	protected $operator;
	
	protected static $sql = '%s %s';
	
	/**
	 * @param string|array|Contion_Field $field
	 * @param string $operator
	 */
	public function __construct($operator = self::NOT_NULL, $field = null) {
		$this->setField($field);
		$this->operator = $operator;
	}
	public function getField(){
		return $this->field;
	}
	/**
	 * @param string|array|Condition_Field $field
	 */
	public function setField($field){
		if(is_array($field)){
			foreach($field as $i => $f){
				if(! $field instanceof Condition_Field){
					$field[$i] = new Condition_Field($f);
				}
			}
		}elseif(! $field instanceof Condition_Field){
			$field = new Condition_Field($field);
		}
		$this->field = $field;
	}
	
	public function getOperator(){
		return $this->operator;
	}
	/**
	 * @param string $operator
	 */
	public function setOperator($operator){
		$this->operator = $operator;
	}
	
	static $opposingOperators = array(
			self::EQUAL 			=> self::NOT_EQUAL,
			self::GREATER			=> self::LOWER_OR_EQUAL,
			self::GREATER_OR_EQUAL	=> self::LOWER,
			self::LIKE				=> self::NOT_LIKE,
			self::IS_NULL			=> self::IS_NOT_NULL,
			self::IS_TRUE			=> self::IS_FALSE
		);
	/**
	 * Return the opposite operator of its param.
	 * e.g. EQUAL (=) becomes NOT_EQUAL (!=) 
	 * GREATER (>) becomdes LOWER_OR_EQUAL (<=)
	 * @param string $operator
	 * @return string
	 * @static
	 */
	public static function flipOperator($operator){
		if(isset(self::$opposingOperators[$operator])){
			return self::$opposingOperators[$operator];
		}
		if($key = array_search(self::$opposingOperators, $operator)){
			return $key;
		}
		return $operator;
	}
	
	static $exclusiveOperators = array(self::NOT_EQUAL, self::NOT_LIKE, self::IS_NULL);
	/**
	 * Returns true if the operator is exclusive i.e. negating.
	 * This is true for NOT_EQUAL, NOT_LIKE
	 * @param string $operator
	 * @return boolean
	 */
	public static function isExclusive($operator){
		return in_array($operator, self::$exclusiveOperators);
	}
	
	protected function getJunctor($operator = null){
		if($operator == null){
			$operator = $this->getOperator();
		}
		return self::isExclusive($operator) ? self::CONJUNCTION : self::DISJUNCTION;
	}
	
	public function getSql() {
		$field = $this->getField();
		if(is_array($field)){
			if(count($field) > 1){
				$sql = array();
				foreach($field as $f){
					$sql[] = $this->_getSql($f);
				}
				return '(' . implode(' ' . /*$this->getJunctor()*/ 'AND' . ' ', $sql) . ')';
			}elseif(count($field) == 1){
				return $this->_getSql($field[0]);
			}else{
				return "";
			}
		}
		return $this->_getSql($field);
	}
	protected function _getSql($field){
		return sprintf(self::$sql, $field, $this->getOperator());
	}
}