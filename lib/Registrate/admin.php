<?php


function registrate_hash($page, $op, $object){
	/*if(!is_string($object)){
		$object = $object['name'];
	}*/
	if(is_array($object)){
		$object = serialize($object);
	}
	//$object = (array) $object;
	//$object = serialize($object);
	//return $page . $op . md5($object);
	return md5($page . $op . $object);
}
function registrate_hash_field($page, $op, $object){
	?><input type="hidden" name="_hash" value="<?php print registrate_hash($page, $op, $object); ?>" /><?php
}
function registrate_check_hash($page, $op, $object){
	$hash = registrate_hash($page, $op, $object);
	if(!isset($_REQUEST['_hash']) || $_REQUEST['_hash'] != $hash){
		throw new Registrate_InvalidHashException();
		return false;
	}
	return true;
}
class Registrate_InvalidHashException 
extends Exception {
	//function __construct()
}