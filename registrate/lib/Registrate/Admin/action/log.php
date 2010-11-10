<?php
switch($op){
	default:
		$params = $_REQUEST;
		
		$filters = array();
		foreach(array('event', 'message') as $type){
			if(isset($params[$type]) && is_array($params[$type]) && count($params[$type])){
				$filters[$type] = $params[$type];
				if(in_array('null', $filters[$type])){
					$filters[$type] = null;
				}
			}
		}
		foreach(array('from', 'to') as $type){
			if(isset($params[$type]) && strlen($params[$type])){
				$filters[$type] = date('Y-m-d H:i:s', strtotime($params[$type]));
			}
		}
		if(isset($params['item'])){
			$filters['item'] = explode(',', trim($params['item']));
			foreach($filters['item'] as $i => $f){
				if(! strlen(trim($f)) || $f == 'null'){
					unset($filters['item'][$i]);
				}else{
					$filters['item'][$i] = intval($f);
				}
			}
		}
		
		$rows = registrate_db()->getLog($filters);
		foreach($rows as $i => $row){
			$row['data'] = unserialize($row['data']);
			$row['timestamp'] = strtotime($row['timestamp']);
			$rows[$i] = $row;
		}
		
		$events = registrate_event();
		$events['null'] = array('name' => '<null>');
		
		$message = registrate_db()->getLogMessages();
		var_dump($message);
		$filters = $filters + array(
			'message'	=> array(),
			'from'		=> '',
			'to'		=> '',
			'item'		=> array(),
			'event'		=> array()
		);
		$controls = array(
			'message'	=> array(
				'values'	=> $message,
				'selected'	=> $filters['message']
			),
			'item'		=> join($filters['item'], ","),
			'event'		=> array(
				'values'	=> $events,
				'selected'	=> $filters['event']
			),
			'time'		=> array(
				'from'		=> $filters['from'],
				'to'		=> $filters['to'],
			),
		);
		
		return array(
			'rows' => $rows,
			'controls' => $controls
		);
	break;
}