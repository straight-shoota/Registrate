<?php 

/**
 * @author Johannes Müller <dev@straight-shoota.de>
 * @version 0.1
 */
class Condition_Junction
extends Condition {
	/**
	 * @var array
	 */
	protected $conditions;
	/**
	 * @var string
	 */
	protected $junctor;
	
	/**
	 * @param string $junctor
	 * @param array $conditions
	 */
	public function __construct($junctor = self::CONJUNCTION, array $conditions = array()) {
		$this->conditions = $conditions;
		$this->junctor = $junctor;
	}
	
	public function getConditions(){
		return $this->conditions;
	}
	/**
	 * @param array $conditions
	 */
	public function setConditions(array $conditions){
		$this->conditions = $conditions;
	}
	public function add(Condition $condition){
		$this->conditions[] = $condition;
	}
	/*public	function removeCondition(Condition $condition){
		$this->conditions[] = array_ $condition;
	}*/
	
	
	public function getJunctor(){
		return $this->junctor;
	}
	
	public function getSql() {
		$conds = array();
		foreach($this->getConditions() as $condition){
			$sql = $condition->getSql();
			if($sql == ''){
				continue;
			}
			if($condition instanceof Condition_Junction){
				$sql = "(" . $sql . ")";
			}
			$conds[] = $sql;
		}
		return join($conds, " " . $this->getJunctor() . " ");
	}
}