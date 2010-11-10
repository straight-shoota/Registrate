<?php
class Registrate_Field_Regdate
extends Registrate_Field {
	function view(array &$data) {
		return false;
	}
	function validate(array &$data, $form = null, $event = null){
		$data['regdate'] = date("Y-m-d H:i:s");
		return true;
	}
	function display(array $row, $col = null, $context = null){
		return date("j. F Y H:i:s", $row['regdate']);
	}
	function prepareDisplay(array &$row, array $event){
		$row['regdate'] = strtotime($row['regdate']);
	}
	function showSettings(){
		return false;
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			'regdate' => array(
				'label' => 'Regdate'
			)
		);
	}
	function getDatabaseColumns() {
		return array(
			'regdate' => array('type' => 'DATETIME', 'not null' => true, 'sortable' => TRUE),
		);
	}
	function isFixed(){
		return true;
	}
}