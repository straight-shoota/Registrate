<?php
/**
 * @author Johannes Müller <dev@straight-shoota.de>
 * @version 0.1
 */
class Condition_Field
implements Condition_Statement {
	protected $table;
	protected $field;
	
	public function __construct($field, $table = null){
		$this->table = $table;
		$this->field = $field;
	}
	/**
	 * Renders this condition as a SQL string.
	 * If it contains more than one sub statement, the whole string should be in parenthesis.
	 * @return string
	 */
	public function getSql() {
		return $this->table !== null ? sprintf('`%s`.`%s`', $this->table, $this->field) : sprintf('`%s`', $this->field);
	}
	
	public function __toString(){
		return $this->getSql();
	}
}