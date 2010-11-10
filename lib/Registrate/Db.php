<?php
require_once 'Storage.php';
		
abstract class Registrate_Db
implements Registrate_Storage {
	protected $stats = array(
	);
	public $version = 0.0;
	/**
	 * Executes a query.
	 * @param Onev_Admin_Query $query
	 * @return array|bool|object
	 *
	public function executeQuery(Registrate_Admin_Query $query, $type = null) {
		if($type == null) {
			$type = $query->getType();
		}
		$query->sql = $this->getSQL($query, $type);
		
		$query->result = $this->_executeQuery($query, $type);
		
		return $query->result;
	}*/
	
	/* Query Implementations */
	public function storeItem(array $form, array $values) {
		return $this->_storeItem($form, $values);
	}
	protected abstract function _storeItem(array $form, array $values);
	
	public function getItem(array $form, $id) {
		return $this->_getItem(
			$form['name'],
			registrate_form_cols($form),
			new Condition_Comparation(Condition::EQUAL, 'id', $id));
	}
	protected abstract function _getItem($table, array $cols, Condition $where);
	
	public function getItems(Registrate_Admin_Query_List $query) {
		return $this->_listItems(
			$query->getForm(),
			$query->getWhere(),
			//$query->getHaving(),
			$query->getSortOrder(),
			$query->getLimit()
		);
	}
	protected abstract function _listItems(array $form, Condition $where, array $order, $limit);
	
	public function countItems(Registrate_Admin_Query_List $query) {
		$form = $query->getForm();
		return intval($this->_countItems(
			$form['name'],
			$query->getWhere()
		));
	}
	protected abstract function _countItems($table, Condition $where);
	
	
	public function updateItem(array $form, $id, array $values) {
		$table = $this->_table($form['name']);
    	unset($values['id']);
		$where = new Condition_Comparation(Condition::EQUAL, 'id', $id);
		return $this->_updateItem($table, $values, $where);
	}
	protected abstract function _updateItem($table, array $values, Condition $where);
	
	public function deleteItem(array $form, $id){
		return $this->updateItem($form, $id, array('status' => 0));
	}
	
	public function log($message, $data, $form = null, $event = null, $item = null){
		$values = array('message' => $message, 'data' => serialize($data), 'timestamp' => date('Y-m-d H:i:s'));
		if($form !== null){
			$values['form'] = is_array($form) ? $form['name'] : $form;
		}
		if($event !== null){
			$values['event'] = is_array($event) ? $event['id'] : $event;
		}
		if($item !== null){
			$values['item'] = is_array($item) ? $item['id'] : $item;
		}
		return $this->_log($values);
	}
	protected abstract function _log($values);
	
	public function getLog($filter = array()){
		$where = new Condition_Junction();
		
		if(isset($filter['form'])){
			$where->add(new Condition_Comparation(Condition::LIKE, new Condition_Field('form', 'log'), $filter['form']));
		}else if(array_key_exists('form', $filter) && $filter['form'] === null){
			$where->add(new Condition_Operation(Condition::IS_NULL, 'form'));
		}
		
		if(isset($filter['message'])){
			$where->add(new Condition_Comparation(Condition::LIKE, new Condition_Field('message', 'log'), $filter['message']));
		}
		
		if(isset($filter['event'])){
			$where->add(new Condition_Comparation(Condition::EQUAL, new Condition_Field('event', 'log'), $filter['event']));
		}else if(array_key_exists('event', $filter) && $filter['event'] === null){
			$where->add(new Condition_Operation(Condition::IS_NULL, new Condition_Field('event', 'log')));
		}
		
		if(isset($filter['item'])){
			$where->add(new Condition_Comparation(Condition::EQUAL, new Condition_Field('item', 'log'), $filter['item']));
		}elseif(array_key_exists('item', $filter) && $filter['item'] === null){
			$where->add(new Condition_Operation(Condition::IS_NULL, new Condition_Field('item', 'log')));
		}
		
		if(isset($filter['from'])){
			$where->add(new Condition_Comparation(Condition::GREATER, new Condition_Field('timestamp', 'log'), $filter['from']));
		}
		if(isset($filter['to'])){
			$where->add(new Condition_Comparation(Condition::LOWER, new Condition_Field('timestamp', 'log'), $filter['to']));
		}
		return $this->_getLog($where);
	}
	protected abstract function _getLog(Condition $where);
	
	
	public function supportsStatType($type) {
		return isset($this->stats[$type]);
	}
	public function getStats($type, array $form, array $event) {
		$where = self::getDefaultCondition($event['id']);
		$sql = sprintf($this->stats[$type], $this->_table($form['name']), $where);
		return $this->_getStats($sql, $type, $form, $event);
	}
	protected abstract function _getStats($sql, $type, array $form, array $event);
	

	public static function getDefaultCondition($eventId){
		$where = new Condition_Junction();
		$where->add(new Condition_Comparation(Condition::EQUAL, 'event', $eventId));
		//$where->add(new Condition_Comparation(Condition::EQUAL, 'status', 1));
		return $where;
	}
	
	public function getSQL($query, $type = null) {
		if($type == null) {
			$type = $query->getType();
		}
		unset($this->sql);
		$form = $query->getForm();
		$table = $this->_createTable($form['name']);
		switch($type) {
			case "hard-delete":
				$where = $this->_createConditions($query->getWhere());
				$this->sql = sprintf("DELETE FROM `%s` WHERE %s LIMIT 1", $table, $where);
			break;
			case 'delete'://
				$values = $this->_createValues(array('status' => 0));
				$where = $this->_createConditions($query->getWhere());
				$this->sql = sprintf("UPDATE `%s` SET %s WHERE %s LIMIT 1", $table, $values, $where);
				var_dump($this->sql);
			break;
			case "update"://
				$values = $this->_createValues($query);
				$where = $this->_createConditions($query->getWhere());
				$this->sql = sprintf("UPDATE `%s` SET %s WHERE %s LIMIT 1", $table, $values, $where);
			break;
			case 'list':
			case 'read':
				$order = $this->_createSortOrder($query);
				$cols = $this->_createCols($query);
				$where = array(
					'AND',
					array('=', 'status', 1),
					$query->getWhere()
				);
				if($query->getEvent()) {
					$event = $query->getEvent();
					$where[] = array('=', 'event', $event['id']);
				}
				$where = $this->_createConditions($where, $query);
				if($where) {
					$where = "WHERE " . $where;
				}
				$having = $this->_createConditions($query->getHaving(), $query);
				if($having) {
					$having = "HAVING " . $having;
				}
				$limit = $type == 'read' ? 'LIMIT 1' : $this->_createLimit($query);
				
				$this->sql = sprintf("SELECT %s\nFROM `%s`\n", $cols, $table) . join(array($where, $having, $order, $limit), "\n");
			break;
			case 'count':
				$cols = 'COUNT(*)';
				$event = $query->getEvent();
				$where = $this->_createConditions(array('AND', 
						$query->getWhere(),
						$query->getHaving(),
						array('=', 'event', $event['id']),
						array('=', 'status', 1)
					), $query, true);
				if($where) {
					$where = "WHERE " . $where . "\n"; 
				}
				$this->sql = sprintf("SELECT %s\nFROM `%s`\n", $cols, $table) . join(array($where), "\n");
			break;
		}
		return $this->sql;
	}

	protected abstract function _table($form);
	
	protected function _values($values) {
		$s = array();
		foreach($values as $name => $value) {
			$type = "%s";
			if(is_string($value)) {
				$type = '"%s"';
			}elseif(is_numeric($value)) {
				$type = '%d';
			}
			$s[] = sprintf("`%s` = " . $type, $name, $value);
		}
		return join($s, ", ");
	}
	
	protected function _cols($cols) {
		$s = array('`id`, `status`');
		foreach($cols as $name => $col) {
			if(isset($col['alias'])) {
				$s[] = sprintf('%s AS `%s`', $col['alias'], $name);
			}else {
				$s[] = sprintf('`%s`', $name);
			}
		}
		return join($s, ", ");
	}
	protected function _sort($order) {
		$o = array();
		foreach($order as $field => $dir) {
			$o[] = sprintf("`%s` %s", $field, $dir);
		}
		if(count($o)) {
			return "ORDER BY " . join($o, ", ");
		}
		return "";
	}
	
	protected function _limit($limit, $offset = 0) {
		if($limit) {
			return sprintf("LIMIT %d, %d", $offset, $limit); 
		}
		return "";
	}
	
	/*
	 * 
	 */
	protected function _where($data) {
		if(! count($data)){
			return "";
		}elseif(in_array($data[0], array("OR", "AND"))) {
			$op = array_shift($data);
			if(!count($data)) {
				return "";
			}
			$parts = array();
			foreach($data as $d) {
				if(! count($d)) {
					continue;
				}
				$parts[] = $this->_createConditions($d, $query, $useAlias);
			}
			if(! count($parts)) {
				return "";
			}
			return '(' . join($parts, " " .$op. " ") . ')';
		}elseif(in_array($data[0], array('=', '>', '<', '=>', '<=', '<>'))) {
			return $this->_createCondition($data[0], $data[1], $data[2], $query, $useAlias);
		}elseif(in_array($data[0], array('LIKE'))) {
			$values = is_array($data[2]) ? $data[2] : array($data[2]);
			$string = array();
			foreach($values as $value) {
				$string[] = $this->_createCondition($data[0], $data[1], $value, $query, $useAlias);
			}
			return join($string, " OR ");
		}elseif(in_array($data[0], array("IN_ARRAY"))) {
			return sprintf('`%s` %s ("%s")', $data[1], $data[0], join($data[2], '", "'));
		}elseif(is_string($data[0])) {
			return $data[0];
		}
		return "";
	}
	protected function _createCondition($operand, $op1, $op2, $query, $useAlias) {
		if($query == null) {
			$op1 = '`' . $op1 . '`';
		}else{
			if($query->hasCol($op1)) {
				$col = $query->getCol($op1);
				if($useAlias && isset($col['alias'])) {
					$op1 = $col['alias'];
				}else{
					$op1 = '`' . $col['name'] . '`';
				}
			}
		}
		if(strlen($op2) == 0 || $op2{0} != '`') {
			$op2 = '"' . $this->escape($op2) . '"';
		}
		return $op1 . ' ' . $operand . ' ' . $op2;
	}
	protected function escape($s) {
		return mysql_real_escape_string($s);
	}
	
	/*****/
}