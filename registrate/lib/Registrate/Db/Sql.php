<?php
require_once dirname(__FILE__) . '/../Db.php';
abstract class Registrate_Db_Sql
extends Registrate_Db {
	protected $sql = array(
		'list'	 	=> 'SELECT %s
						FROM `%s`
						%s
						%s',
		'get'		=> 'SELECT %s FROM `%s` WHERE %s LIMIT 1',
		'update'	=> 'UPDATE `%s` SET %s WHERE %s LIMIT 1',
		'count'		=> 'SELECT COUNT(*) FROM `%s` WHERE %s',
		'get_log'	=> 'SELECT `log`.`id`, `log`.`form`, `log`.`event`, `log`.`item`, `log`.`message`, `log`.`data`, `log`.`timestamp`, `e`.`name` AS `event_name`, `e`.`description` AS `event_description`, `u`.`user_nicename` AS `username`
				FROM `%s` AS `log`
				LEFT JOIN `%s` AS `e` ON `e`.`id` = `event`
				LEFT JOIN `%s` AS `u` ON `u`.`ID` = `user`
				WHERE %s ORDER BY `timestamp` DESC LIMIT 30',
		'log_messages'	=> 'SELECT `message` FROM `%s` GROUP BY `message`',
	);
	
	protected $stats = array(
		'age'	=> 'SELECT (YEAR(CURDATE()) - YEAR(`birthdate`)) - (RIGHT(CURDATE(), 5) < RIGHT(`birthdate`, 5)) AS `age`, COUNT(*) AS `num` FROM `%s` WHERE %s GROUP BY `age` HAVING `age` > 0 AND `age` < 100 ORDER BY `age`',
		'timeline'	=> 'SELECT DATE(`regdate`) AS `day`, COUNT(*) AS `num` FROM `%s` WHERE %s GROUP BY `day` ORDER BY `day`',
		'hours'	=> 'SELECT HOUR(`regdate`) AS `hour`, COUNT(`id`) AS `count` FROM `%s` WHERE %s GROUP BY `hour`',
		'days'	=> 'SELECT DAYOFWEEK(`regdate`) AS `day`, COUNT(`id`) AS `count` FROM `%s` WHERE %s GROUP BY `day`',
		'towns'	=> 'SELECT `town`, `zipcode`, COUNT(`id`) AS `count` FROM `%s` WHERE %s GROUP BY `town` ORDER BY `town`',
	);

	protected function sql($sql){
		$name = 'sql_' . $sql;
		if(method_exists($this, $name)) {
			$args = func_get_args();
			$sql = array_shift($args);
			array_unshift($args, $this->sql[$sql]);
			return call_user_func_array(array($this, $name), $args);
		}
		throw new Exception("SQL Query for $sql operation should be handled by a method <tt>" . get_class($this) . "::sql_$sql</tt>");
	}
	
	/* Query Implementations */
	protected function _getItem($table, array $cols, Condition $where) {
		return $this->sql('get',
				$this->_table($table),
				$this->_cols($cols),
				$where
			);
	}
	
	protected function _updateItem($table, array $values, Condition $where) {
		return $this->sql('update',
				$this->_table($table), $this->_values($values), $where);
	}
	
	protected function _listItems(array $form, Condition $where, array $order, $limit) {
		$conditions = join(array(
				$where,
				$this->_sort($order)
			));
		return $this->sql('list',
					$this->_cols(registrate_form_cols($form)),
					$this->_table($form['name']),
					$conditions,
					$this->_limit($limit)
		);
	}
	protected function _countItems($table, Condition $where) {
		return $this->sql('count',
					$this->_table($table),
					$where
		);
	}
	
	protected function _getLog(Condition $where){
		$where = $where->getSql();
		if($where == ''){
			$where = 1;
		}
		return $this->sql('get_log', $this->log, $this->events, $this->users, $where);
	}
	public function getLogMessages() {
		return $this->sql('log_messages', $this->log);
	}
}