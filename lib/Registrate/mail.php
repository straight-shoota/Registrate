<?php

function registrate_mail(array $form, array $event, array $values){
	$vars = registrate_mail_vars($event, $values);
	
	$subject = registrate_mail_tpl($event['settings']['mail']['subject'], $vars);
	$message = registrate_mail_tpl($event['settings']['mail']['message'], $vars);
	
	$to = $values["mail"];
	if(isset($values['firstname']) && isset($values['lastname'])) {
		$to = sprintf('%s %s <%s>', $values['firstname'], $values['lastname'], $to);
	}
	
	$headers = array(
		'From' => $event['settings']['mail']['from'],
		'Content-type' => 'text/html; encoding=UTF-8',
		'bcc'	=> 'anmeldung@festdesglaubens.de'
	);
	
	$header = "";
	foreach($headers as $name => $value) {
		$header .= $name .':' . $value . "\r\n";
	}
	$return = wp_mail($to, $subject, $message, $header);
	return $return;
}

function registrate_mail_tpl($message, array $vars){
	return strtr($message, $vars);
}

function registrate_mail_vars(array $event, array $values){
	$vars = array();
	foreach($values as $k => $v){
		$vars['%' . $k] = $v;
	}
	$vars['!event'] = $event['description'];
	return $vars;
}