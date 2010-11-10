<?php
class Registrate_Field_IpAddress
extends Registrate_Field {
	function view(array &$data) {
		return false;
	}
	function validate(array &$data, $form = null, $event = null){
		$data['ipaddress']  = $_SERVER['REMOTE_ADDR'] != '::1' ? $_SERVER['REMOTE_ADDR'] : "localhost";
		
		return true;
	}
	
	function showSettings(){
		return false;
	}
	function getDatabaseColumns() {
		return array(
			'ipaddress' => array('type' => 'VARCHAR', 'length' => 12, 'not null' => true, 'sortable' => false),
		);
	}
	function isFixed(){
		return true;
	}
}