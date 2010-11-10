<?php
class Registrate_Field_Status
extends Registrate_Field {
	const REGISTERED = 1;
	const DELETED	 = 0;
	const CHECKED_IN = 2;
	
	static $status_labels = array(
		self::REGISTERED	=> 'registered',
		self::DELETED		=> 'deleted',
		self::CHECKED_IN	=> 'checked_in'
	);
	
	public static function getStatus($i){
		return self::$status_labels[$i];
	}
	
	function view(array &$data) {
		$data += array(
			'status' => self::REGISTERED,
		);
		return false;
	}
	function displayCols(Registrate_Admin_Query $query){
		return array();
			/*'status' => array(
				'label'		=>  'Status'
			)
		);*/
	}

	function display(array $row, $col = null, $context = null) {
		return self::$status_labels[$row['status']];
	}
	function validate(array &$data, $form = null, $event = null) {
		$data['status'] = self::REGISTERED;
		return true;
	}
	function getDatabaseColumns() {
		return array(
			'status' => array('type' => 'int', 'length' => 2, 'not null' => true)
		);
	}
	function showSettings(){
		return false;
	}
	/*function isFixed(){
		return true;
	}
	function isHidden(){
		return true;
	}*/
}