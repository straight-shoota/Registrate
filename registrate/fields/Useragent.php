<?php
class Registrate_Field_Useragent
extends Registrate_Field {
	function view(array &$data) {
		return false;
	}
	function validate(array &$data, $form = null, $event = null){
		$data['useragent'] = $_SERVER['HTTP_USER_AGENT'];
		
		return true;
	}
	function displayCols(Registrate_Admin_Query $query){
		return array();
		return array(
			'useragent' => array(
				'label' => 'Useragent'
			)
		);
	}
	function getDatabaseColumns() {
		return array(
			'useragent' => array('type' => 'VARCHAR', 'length' => 128, 'not null' => false, 'sortable' => false),
		);
	}
	function showSettings(){
		return false;
	}
	function isFixed(){
		return true;
	}
}
