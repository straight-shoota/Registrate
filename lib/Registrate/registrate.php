<?php
require_once REGISTRATE_DIR . '/lib/Condition/Condition.php';

require_once dirname(__FILE__) . '/Field.php';
require_once dirname(__FILE__) . '/event.php';
require_once dirname(__FILE__) . '/form.php';
require_once dirname(__FILE__) . '/mail.php';
require_once dirname(__FILE__) . '/messages.php';
	
/**
 * registrate_db($storage) sets global storage adapter for registrate
 * registrate_() return global storage adapter for registrate
 * @param Registrate_Storage $_storage
 * @return Registrate_Storage
 */
function registrate_db(Registrate_Storage $_storage = null){
	static $storage;
	if($_storage != null){
		$storage = $_storage;
	}
	return $storage;
}

/*$registrate_messages = array();
$registrate_message_function = 'registrate_message_function';
function registrate_message_function($msg){
	return $msg;
};

function registrate_message_get($msg){
	global $registrate_messages;
	
	if(isset($registrate_messages[$msg])){
		return $registrate_messages[$msg];
	}
	return $msg;
}s*/
/**
 * 
 * @param string $msg
 * @param array $vars
 * @return string
 */
function registrate_message($msg, array $vars = array()){
	if(function_exists('registrate_message_get')){
		$msg = registrate_message_get($msg);
	}
	//$msg = call_user_func($registrate_message_function, $msg);
	
	foreach($vars as $k => $v){
		if($k{0} == '%'){
			$vars[$k] = '<em>' . $v . '</em>';
		}
	}
	return strtr($msg, $vars);
}

function registrate_messages(array $data){
	$messages = array();
	foreach($data as $msg){
		if(!is_array($msg)){
			$msg = array($msg, array());
		}elseif(!isset($msg[1])){
			$msg[1] = array();
		}
		
		$messages[] = registrate_message($msg[0], $msg[1]);
	}
	return $messages;
}

/**
 * Inserts a new record in the registration database.
 * @param array $form
 * @param array $values
 * @return bool
 */
function registrate_register(array $form, array $event, array $values) {
	foreach($form['fields'] as $field){
		$field->prepareStorage($form, &$values);
	}
	do_action('registrate_registration_prepare', $form, $event, &$values);
	
	// delete any index which is no column of the database table
	// but leave $values array untouched
	$store = array();
	foreach(registrate_form_cols($form) as $name => $col) {
		$store[$name] = $values[$name];
	}
	
	$success = registrate_db()->storeItem($form, $store);
	
	if($success){
		if($event['settings']['mail']['enabled']){
			registrate_mail($form, $event, $values);
		}
		do_action('registrate_registration_success', $form, $event, $store, $success);
	}else{
		do_action('registrate_registration_failure', $form, $event, $store);
		global $wpdb;
		$wpdb->print_error();
		var_dump($wpdb->queries);
		var_dump($form);
		var_dump($event);
		var_dump($values);
		var_dump($store);
	}
	return $success;
}

/**
 * Increases the registration counter of the event record.
 * Called on action registrate_registration_success if @registrate_register was successfull.
 * @param array $form
 * @param array $event
 * @param array $values
 * @return unknown_type
 *
function registrate_registrate_increase(array $form, array $event, array $values) {
	registrate_db()->updateRegistrationCounter($event, 1);
}
/**
 * Decreases the registration counter of the event record.
 * Called on action registrate_registration_cancelled when a registration was successfully cancelled.
 * @param array $form
 * @param array $event
 * @param array $item
 * @return unknown_type
 *
function registrate_registrate_decrease(array $form, array $event, array $values) {
	registrate_db()->updateRegistrationCounter($event, -1);
}*/

//add_action('registrate_registration_success', 'registrate_registrate_increase', 10, 3);
//add_action('registrate_registration_cancelled', 'registrate_registrate_decrease', 10, 3);