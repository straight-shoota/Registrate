<?php

require_once( dirname(__FILE__) . '/../../../wp-load.php' );

registrate_init();

if(! current_user_can('view_registrations')){
	//header("Location: ../../../../wp-login.php?redirect_to=" . urlencode($_SERVER['REQUEST_URI']));
	die();
}

$op = 'export';
$params = $_REQUEST;
$format = isset($params['format']) ? $params['format'] : 'xls';

$vars = include REGISTRATE_DIR . '/lib/Registrate/Admin/item.php';

extract($vars);

$cols = array(
	'firstname' => 'firstname',
	'lastname' => 'lastname',
	'street' => 'street',
	'zipcode' => 'zipcode',
	'town' => 'town',
	'birthdate' => 'birthdate',
	'mail' => 'mail',
	'phone'	=> 'phone',
	'regdate' => 'regdate'
);
include 'admin/export.php';