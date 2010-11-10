<?php
class Registrate_Field_Event
extends Registrate_Field {
	function view(array &$data) {
		return false;
	}
	function validate(array &$data, $form = null, $event = null){
		$data['event'] = $event['id'];
		
		return true;
	}
	function prepareDisplay(array &$row, array $event) {
		$row['event'] = $event;
	}

	function display(array $row, $col = null, $context = null) {
		$event = parent::display($row, $col, $context);
		return $event['description'];
	}
	function showSettings(){
		return false;
	}
	function getDatabaseColumns() {
		return array(
			'event' => array('type' => 'int', 'length' => 8, 'not null' => true, 'sortable' => true),
		);
	}
	function isFixed(){
		return true;
	}
	function isHidden(){
		return true;
	}
}