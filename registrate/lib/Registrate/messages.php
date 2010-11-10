<?php
$registrate_messages = array();
function registrate_message_get($msg){
	global $registrate_messages;
	if(isset($registrate_messages[$msg])){
		return $registrate_messages[$msg];
	}
	$registrate_messages[$msg] = $msg;
	return $msg;
}
function registrate_messages_init($lang = 'en'){
	global $registrate_messages, $registrate_localization;
	list($localization, $messages) = registrate_messages_load($lang);
	$registrate_localization	= $localization;
	$registrate_messages 		= $messages;
}
function registrate_messages_load($lang){
	$file = REGISTRATE_LANG_DIR . $lang . '.php';
	if(! file_exists($file)){
		registrate_messages_create_lang($lang);
	}
	$messages = include($file);
	$localization = $localization + array(
			'source'				=> 'en',
			'destination'			=> $lang,
			'source_version'		=> 1,
			'source_date'			=> date('Y-m-d H:i:s'),
			'destination_version'	=> 1,
			'destination_date'		=> date('Y-m-d H:i:s'),
			'author'				=> '<unknown>',
		);
	return array($localization, $messages);
}
function registrate_messages_create_lang($lang){
	$localization = array(
		'source'				=> 'en',
		'destination'			=> $lang,
		'source_version'		=> 1,
		'source_date'			=> date('Y-m-d H:i:s'),
		'destination_version'	=> 1,
		'destination_date'		=> date('Y-m-d H:i:s'),
		'author'				=> '<unknown>',
	);
	registrate_messages_save($localization);
}
function registrate_messages_save($localization = false, $messages = array()){
	if($localization === false){
		global $registrate_messages, $registrate_localization;
		$localization	= $registrate_localization;
		$messages 		= $registrate_messages;
	}
	
	if(count($messages)){
		list($l, $saved) = registrate_messages_load($localization['destination']);
		if($saved === $messages){
			// no need to save when there are no changes
			return;
		}
	}
	
	$localization['source_version']++;
	$localization['source_date'] = date('Y-m-d H:i:s');
	if(file_put_contents(REGISTRATE_LANG_DIR . $localization['destination'] . '.php', '<?php
/*
 * localization for Registrate
 */
$localization = ' . var_export($localization, true). ';
return ' . var_export($messages, true) . ';')){
	}
}