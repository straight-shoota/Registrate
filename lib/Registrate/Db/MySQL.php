<?php
require_once dirname(__FILE__) . '/Sql.php';
class Registrate_Db_MySql
extends Registrate_Db_Sql {
	protected $settings;
	public $handler;
	function __construct(){
		global $registrate_settings;
		$this->settings = $registrate_settings;
	}
	function connect(){
		$this->handler = @new mysqli($this->settings['db.host'],
				$this->settings['db.username'],
				$this->settings['db.password'],
				$this->settings['db.database']);
			
		return $this->handler->connect_error == false;
	}
	
	/*
	 * Query Implementations
	 * @see Storage
	 */
	protected function _storeItem(array $form, array $values) {
		/*global $wpdb;
		if($wpdb->insert($this->_table($form['name']), $values)){
			return $wpdb->insert_id;
		}
		return false;*/
	}
	protected function _countItems($table, Condition $where) {
		/*global $wpdb;
		return $wpdb->get_var(sprintf($this->sql['count'],
					$this->_table($table),
					$where
			));*/
	}
	protected function _getStats($sql, $type, array $form, array $event) {
		/*global $wpdb;
		return $wpdb->get_results($sql, ARRAY_A);*/
	}
	
	protected function _table($name) {
		/*global $wpdb;
		return $wpdb->prefix . 'registrate_form-' . $name;*/
	}

	/*
	 * form storage
	 */
	public function loadForms() {
		//return get_option('registrate_forms', array());
	}
	protected function storeForms(array $forms){
		/*if(get_option('registrate_forms') == $forms){
			// this is necessary because update_options returns false if new and old value are the same ("no need to update")
			return true;
		}
		return update_option('registrate_forms', $forms);*/
	}
	public function getForm($name){
		/*$forms = $this->loadForms();
		if(isset($forms[$name])) {
			return $forms[$name];
		}else{
			return false;*
		}*/
	}
	public function setForm($name, $form){
		/*$forms = $this->loadForms();
		if($form != null){
			$forms[$name] = $form;
		}else{
			unset($forms[$name]);
		}
		return $this->storeForms($forms);*/
	}
	
	public function createFormTable($name, array $data) {
		/*global $wpdb;
		$cols = array(
			'`id` int(8) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY',
			//'`status` int(3) NOT NULL DEFAULT "1"'
		);
		$indices = array(
			'status'
		);
		foreach($data as $n => $col){
			$col['type'] = strtolower($col['type']);
			$sql = '`' . $n . '` ' . $col['type'];
			if (in_array($col['type'], array('varchar', 'char', 'text')) && isset($col['length'])) {
				$sql .= '('. $col['length'] .')';
			}elseif (isset($col['precision']) && isset($col['scale'])) {
				$sql .= '('. $col['precision'] .', '. $col['scale'] .')';
			}
			if (isset($col['not null']) && $col['not null']) {
				$sql .= ' NOT NULL';
			}
			if (isset($col['default'])) {
				$default = is_string($col['default']) ? "'". $col['default'] ."'" : $col['default'];
				$sql .= " default $default";
			}

			$cols[] = $sql;
			
			if(isset($col['sortable']) && $col['sortable']){
				$indices[] = $n;
			}
		}
		//$cols[] = 'PRIMARY KEY (`id`)';
		//$cols[] = ''
		foreach($indices as $index){
			$cols[] = 'KEY `' . $index . '_idx` (`' . $index . '`)';
		}
		$table = $this->_createTable($name);
		$sql = "CREATE TABLE `$table`(\n\t" . join($cols, ",\n\t") . "\n) DEFAULT CHARSET=utf8;";
		
		print "<pre>$sql</pre>";
		$wpdb->show_errors();
		$result = $wpdb->query($sql);
		$wpdb->hide_errors();
		return $result !== false;*/
	}
	public function deleteFormTable($name) {
		/*global $wpdb;
		$table = $this->_createTable($name);
		$sql = "DROP TABLE `$table`;";
		print "<pre>$sql</pre>";
		$wpdb->show_errors();
		$result = $wpdb->query($sql);
		$wpdb->hide_errors();
		return $result !== false;*/
	}

	/*
	 * event storage
	 */
	public function getEvent($name){
		/*global $wpdb;
		
		$cond = (intval($name) == $name) ? 'id = %d' : 'name = %s';
		
		return $wpdb->get_row($wpdb->prepare("SELECT id, name, form, description, registrations, status, begin, end, settings FROM $this->events WHERE " . $cond, $name), ARRAY_A);*/
	}
	public function getEvents(){
		/*global $wpdb;
		return $wpdb->get_results("SELECT * FROM $this->events", ARRAY_A);*/
	}
	public function addEvent(array $event) {
		/*global $wpdb;
		return $wpdb->insert($this->events, $event);*/
	}
	public function updateEvent(array $event) {
		/*global $wpdb;
		$id = $event['id'];
		unset($event['id']);
		$return = $wpdb->update($this->events, $event, array('id' => $id));
		return $return !== false;*/
	}
	public function updateRegistrationCounter(&$event) {
		/*global $wpdb;
		$table = $this->_table($event['form']);
		$num = $this->_countItems($event['form'], self::getDefaultCondition($event['id']));
		
		if($num != $event['registrations']){
			$event['registrations'] = $num;
			$wpdb->update($this->events, array('registrations' => $num), array('id' => $event['id']), array('%d'), array('%d'));
		}
		return $num;*/
	}
	
	public function _log($values){
		/*global $wpdb;
		$values['user'] = wp_get_current_user()->ID;
		$wpdb->insert($this->log, $values);*/
	}
	protected function sql_get_log($sql, $table, $events, $users, $where) {
		/*global $wpdb;
		return $wpdb->get_results(sprintf($sql, $this->log, $this->events, $this->users, $where), ARRAY_A);*/
	}
	protected function sql_log_messages($sql, $table) {
		/*global $wpdb;
		return $wpdb->get_col(sprintf($sql, $this->log));*/
	}
	
}
