<?php
include_once '../lib/Registrate/registrate.php'; 
include_once '../lib/Registrate/Field.php';

session_start();
Registrate_Field::loadClasses('../fields');

$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 'registrate_form';

$page = substr($page, 11);

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : null;
include 'admin/' . $page . '.php';

function registrate_admin_url($page, $o, $params = array(), $options = null) {
	switch($page) {
		case 'list':
		case 'stats':
			$params['event'] = $o->id;
			if($options !== null){
				$options->build($params);
			}
		break;
		case 'event':
			$params["id"] = $o->id;
		break;
		case 'form':
			if(! empty($o)){
				$params['form'] = $o['name'];
			}
		break;
	}
	$page = "registrate_" . $page;
	
	foreach($params as $k => &$p){
		$p = rawurlencode($p);
		$p = "$k=$p";
	}
	array_unshift($params, "page=$page");
	return 'admin.php?' . join($params, "&");
}