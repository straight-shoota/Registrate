<?php
require_once 'Operation.php';
class Condition_Comparation
extends Condition_Operation {
	
	protected $value;
	protected static $sql = array(
		'int'		=> '%s %s %d',
		'string'	=> '%s %s "%s"',
		'float'		=> '%s %s %f',
		'statement'	=> '%s %s %s',
	);
	
	public function __construct($operator = self::EQUAL, $field = null, $value = null) {
		parent::__construct($operator, $field);
		$this->value = $value;
	}
	public	function getValue(){
		return $this->value;
	}
	public	function setValue($value){
		$this->value = $value;
	}
	/*public	function get(){
		return $this->;
	}*/
	
	public function getSql() {
		$value = $this->getValue();
		if(is_array($value)){
			if(count($value) > 1){
				$sql = array();
				foreach($value as $v){
					$sql[] = $this->_getSql($v);
				}
				return '(' . implode(' ' . /*$this->getJunctor()*/ 'AND' . ' ', $sql) . ')';
			}elseif(count($value) == 1){
				return $this->_getSql($value[0]);
			}else{
				return "";
			}
		}
		return $this->_getSql($value);
	}
	public function _getSql($value, $field = null) {
		if($field == null){
			$field = $this->getField();
		}
		if(is_array($field)){
			if(count($field) > 1){
				$sql = array();
				foreach($field as $f){
					$sql[] = $this->_getSql($value, $f);
				}
				return '(' . implode(' ' . $this->getJunctor() . ' ', $sql) . ')';
			}elseif(count($field) == 1){
				return $this->_getSql($value, $field[0]);
			}else{
				return "";
			}
		}
		return sprintf($this->getSqlFormat($value), $field, $this->getOperator(), $value);
	}
	protected function getSqlFormat($value){
		if(is_int($value) || is_long($value)){
			$sql = 'int';
		}elseif(is_float($value) || is_double($value)){
			$sql = 'float';
		}elseif($value instanceof Condition){
			$sql = 'statement';
		}else{
			$sql = 'string';
		}
		return self::$sql[$sql];
	}
}