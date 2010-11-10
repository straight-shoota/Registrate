<?php
switch($op) {
	default:
		$op = 'list';
	case 'list':
	case 'export':
		/*
		 * $params = array(
		 * 		'page'	=> 'registrate_item',
		 * 		'op' 	=> 'list',
		 * 		'p'		=> (int) // page number						default:1
		 * 		'event'	=> (int) // event id
		 * 		'n'		=> (int) // number of items per page		default:30
		 * );
		 * 
		 */
		$params = $_REQUEST;
		$errors = array();
		$event = registrate_event(@$params['event']);
		if(! $event){
			return registrate_admin_error('Event %event does not exist.', array('%event' => @$params['event'])); 
		}else {
			$form = registrate_form_load($event['form']);
			if(! $form){
				return registrate_admin_error('Event %event has invalid form %form attached.',
					array('%event' => $event['name'], '%form' => $event['form']));
			}
		}
		
		require_once dirname(__FILE__) . '/Query/List.php';
		
		$query = new Registrate_Admin_Query_List($form, $event);
		$query->parseParams($params);
    	
    	// update registrations field on event table
		registrate_db()->updateRegistrationCounter(&$event);
		if($op == 'export'){
			$query->setItemCountPerPage(false);
		}
		
			
		$paginator = $query->paginator();
		$rows = $paginator->getCurrentItems();
		
    	foreach($rows as $i => $row){
    		foreach($form['fields'] as $field){
    			 $field->prepareDisplay(&$row, $event);
    			 $rows[$i] = $row;
    		}
    	}
    	
		return array(
			'rows'		 	=> $rows,
			'cols'			=> $query->getDisplayCols(),
			'query'			=> $query,
			'form'			=> $form,
			'event'			=> $event,
			'paginator'		=> $paginator,
			'checkin'		=> registrate_checkin_enabled()
		);
	break;
	case 'recent':
		$errors = array();
		$event = registrate_event($params['event']);
		
		if(! $event){
			return registrate_admin_error('Event %event does not exist.', array('%event', $params['event'])); 
		}else {
			$form = registrate_form_load($event['form']);
			if(! $form){
				return registrate_admin_error('Event %event has invalid form %form attached.',
					array('%event' => $event['name'], '%form' => $event['form']));
			}
		}
		
		require_once dirname(__FILE__) . '/Query/Recent.php';
		$query = new Registrate_Admin_Query_Recent($form, $event);
		
    	$rows = $query->paginator();
    	
    	$days = array();
    	foreach($rows as &$row){
    		foreach($form['fields'] as $field){
    			 $field->prepareDisplay(&$row, $event);
    		}
			if(!array_key_exists(date('Ymd', $row['regdate']), $days)) {
				$days[date('Ymd', $row['regdate'])] = array();
			}
			$days[date('Ymd', $row['regdate'])][] = $row;
    	}
    	
		return array(
			'rows'		 	=> $rows,
			'form'			=> $form,
			'event'			=> $event,
			'days'			=> $days,
			'rows'			=> $rows
		);
		
	break;
	case 'edit':
		$params = $_REQUEST;
		$errors = array();
		$event = registrate_event($params['event']);
		if(! $event){
			return registrate_admin_error('Event %event does not exist.',
					array('%event', $params['event'])); 
		}else {
			$form = registrate_form_load($event['form']);
			if(! $form){
				return registrate_admin_error('Event %event has invalid form %form attached.',
						array('%event' => $event['name'], '%form' => $event['form']));
			}
		}
		
		require_once dirname(__FILE__) . '/Query/Edit.php';
		$query = new Registrate_Admin_Query_Edit($form, $event, $params['id']);
		
		$item = $query->getItem();
		//$item = registrate_db()->getItem($form, $params['id']);
		if($item == null){
			return registrate_admin_error('Could not load item with id %id', array('%id' => $params['id']));
		}
		$raw = $item;
		
		$dbCols = array();
    	foreach($form['fields'] as $field){
    		 $field->prepareDisplay(&$item, $event);
    		 foreach($field->getDatabaseColumns() as $name => $col){
    		 	$col['field'] = $field;
    		 	$dbCols[$name] = $col;
    		 }
    	}
    	//var_dump($raw);
    	if(isset($params['submit'])){
    		registrate_check_hash('registrate_item', 'edit', $item);
    		
    		$updatedRaw = array();
    		foreach($params as $name => $value){
    			if(substr($name, 0, strlen('registrate-field-')) == 'registrate-field-'){
    				$field = substr($name, strlen('registrate-field-'));
    				if(isset($raw[$field]) && $raw[$field] != $params[$name]){
    					$updatedRaw[$field] = $params[$name];
    					//$col = $dbCols[$field];
    				}
    			}
    		}
    		
    		if(! count($updatedRaw) || registrate_db()->updateItem($form, $params['id'], $updatedRaw) === 1){
    			do_action('registrate_item_updated', $form, $event, $params['id'], $updatedRaw, $raw);
    			return array(
    				'op'		=> 'list',
    				'o' 		=> $event,
    				'messages'	=> array(registrate_message('Item has been updated.'))
    			);
    		}else{
    			return array(
    				'op'		=> 'list',
    				'o' 		=> $event,
    				'errors'	=> array(registrate_message('Database error: item could not be saved.'))
    			);
    		}
    	}
    	
		return array(
			'form'		=> $form,
			'event'		=> $event,
			'item'		=> $item,
			'raw'		=> $raw,
			'dbCols' 	=> $dbCols,
			'cols'		=> $query->getDisplayCols(),
			'errors'	=> $errors
		);
	break;
	case 'delete':
		$params = $_REQUEST;
		
		$errors = array();
		
		$event = registrate_event($params['event']);
		if(! $event){
			return registrate_admin_error('Event %event does not exist.', array('%event', $params['event'])); 
		}else {
			$form = registrate_form_load($event['form']);
			if(! $form){
				return registrate_admin_error('Event %event has invalid form %form attached.',
					array('%event' => $event['name'], '%form' => $event['form']));
			}
		}

		if(!isset($params['nonce']) || ! wp_verify_nonce($params['nonce'], 'registrate_delete')){
			return registrate_admin_error('Security check failed. Operation not permitted.');
		}
		
		if(! count($errors)){
    		
			$item = registrate_db()->getItem($form, $params['id']);
			$raw = $item;
	    	foreach($form['fields'] as $field){
	    		 $field->prepareDisplay(&$item, $event);
	    	}
			
    		//registrate_check_hash('registrate_item', 'delete', $item);
    		
    		$updatedRaw = array('status' => Registrate_Field_Status::DELETED);
			if(registrate_db()->updateItem($form, $params['id'], $updatedRaw) === 1){
    			do_action('registrate_item_updated', $form, $event, $params['id'], $updatedRaw, $raw);
    			return array(
    				'op'		=> 'list',
    				'object'	=> $event,
    				'messages'	=> array(registrate_message('Registration of %name has been deleted.', array('%name' => $item['name'])))
    			);
    		}else{
    			return array(
    				'op'		=> 'list',
    				'object'	=> $event,
    				'errors'	=> array(registrate_message('Database error: Registration of %name could not be deleted.', array('%name' => $item['name'])))
    			);
    		}
		}else{
			$item = array();
		}
		
    	
		return array(
			'errors' 	=> $errors,
			'op'		=> 'list',
			'object'	=> $event,
		);
		
	break;
	case 'checkin':
		$params = $_REQUEST;

		if(! registrate_checkin_enabled()){
			return array(
				'op'	=> 'list',
    			'o' 	=> $params['event'],
			);
		}
		
		$event = registrate_event($params['event']);
		if(! $event){
			return registrate_admin_error('Event %event does not exist.', array('%event', $params['event'])); 
		}else {
			$form = registrate_form_load($event['form']);
			if(! $form){
				return registrate_admin_error('Event %event has invalid form %form attached.',
					array('%event' => $event['name'], '%form' => $event['form']));
			}
		}

		/*if(!isset($params['nonce']) || ! wp_verify_nonce($params['nonce'], 'registrate_delete')){
			return registrate_admin_error('Security check failed. Operation not permitted.');
		}*/
		
		$item = registrate_db()->getItem($form, $params['id']);
		$raw = $item;
    	foreach($form['fields'] as $field){
    		 $field->prepareDisplay(&$item, $event);
    	}
		
    	//registrate_check_hash('registrate_item', 'delete', $item);
    	
    	$updatedRaw = array('status' => Registrate_Field_Status::CHECKED_IN);
		if(registrate_db()->updateItem($form, $params['id'], $updatedRaw) === 1){
    		do_action('registrate_item_updated', $form, $event, $params['id'], $updatedRaw, $raw);
    		return array(
    			'op'		=> 'list',
    			'object'	=> $event,
    			'messages'	=> array(registrate_message('%name has been checked in.', array('%name' => $item['name'])))
    		);
    	}else{
    		return array(
    			'op'		=> 'list',
    			'object'	=> $event,
    			'errors'	=> array(registrate_message('Database error: %name could not be checked in.', array('%name' => $item['name'])))
    		);
    	}
		
		return array(
			'errors' 	=> $errors,
			'op'		=> 'list',
			'object'	=> $event,
		);
	break;
}
