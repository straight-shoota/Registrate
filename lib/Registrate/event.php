<?php
function registrate_event($name = false) {
	if($name === null){
		return false;
	}elseif($name){
		$event = registrate_db()->getEvent($name);
		if($event === null){
			return false;
		}
		
		return registrate_event_load($event);
	}else{
		$db_events = registrate_db()->getEvents();
		$events = array();
		foreach($db_events as $event){
			$events[$event['id']] = registrate_event_load($event);
		}
		return $events;
	}
}
function registrate_event_load($event){
	if(is_string($event['settings'])){
		$event['settings'] = unserialize($event['settings']);
	}
	$event['begin'] 		= strtotime($event['begin']);
	$event['end']			= strtotime($event['end']);
	$event['id']			= intval($event['id']);
	$event['registrations']	= intval($event['registrations']);
	return $event;
}
function registrate_event_save($event){
	if(is_array($event['settings'])){
		$event['settings'] = serialize($event['settings']);
	}
	$event['begin'] = date('Y-m-d H:i:s', $event['begin']);
	$event['end'] = date('Y-m-d H:i:s', $event['end']);
	
	return $event;
}

function registrate_event_delete($name) {
	return false;
}
function registrate_event_create($event){
	$event = registrate_event_save($event);
	return registrate_db()->addEvent($event);
}
function registrate_event_update($event){
	$event = registrate_event_save($event);
	//var_dump($event);
	return registrate_db()->updateEvent($event);
}