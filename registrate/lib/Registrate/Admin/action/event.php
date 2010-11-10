<?php
switch($op) {
	case 'create':
	case 'edit':
		if($op == 'edit'){
			$event = registrate_event($_GET['event']);
			if(! $event){
				return array(
					'op'		=> 'list',
					'messages' => array(
						registrate_message('Event does not exist.')
					)
				);
			}
			if($event['settings'] == ""){
				unset($event['settings']);
			}
		}else{
			$event = array();
		}
		$forms = registrate_form();
		if(! count($forms)){
			return array(
				'op'		=> 'error',
				'errors'	=> array(
					registrate_message('Cannot create event: You must <a href="!url">set up</a> a form first.', array('!url' => registrate_admin_url('form', 'create')))
				)
			);
		}
		$event = $event + array(
			'name' 			=> "",
			'form'			=> null,
			'description'	=> "",
			'status'		=> 1,
			'begin'			=> time(),
			'end'			=> time(),
			'settings'		=> array()
		);
	
		$event['settings'] = $event['settings'] + array(
			'thx-message' => registrate_message("<h3>Thank you</h3>\nThank you for registering for !event."),
			'mail'			=> array(
				'enabled'	=> true,
				'from'		=> 'Registrate <admin@domain.com>',
				'subject'	=> 'Your registration for !event',
				'message'	=> ''
			));
					
		$errors = array();
		$success = null;
		if(isset($_POST['submit'])) {
			if($op == "edit"){
				registrate_check_hash('event', 'edit', $event);
			}
			$event['name'] = $_POST['name'];
			if(empty($event['name']) || ! preg_match('/^([a-z0-9_-])+$/i', $event['name'])){
				$errors[] = array('Name %name is invalid', array('%name' => $event['name']));
			}else{
				//$event['name'] = strtolower($event['name']);
			}
			
			if(!empty($_POST['description'])){
				$event['description'] = $_POST['description'];
			}else{
				$event['description'] = $event['name'];
			}
			
			if($op == 'create') {
				$event['form'] = $_POST['form'];
				if(! registrate_form($event['form'])){
					$errors[] = array('Form %form does not exist.', array('form' => $event["form"]));
				}
			}
			
			$event['begin'] = $_POST['begin'];
			$begin = strtotime($event['begin']); 
			if(strtotime(date('Y-m-d H:i', $begin)) != $begin){
				$errors[] = array('Begin is not a valid date.');
			}else{
				$event['begin'] = $begin;
			}
			$event['end'] = $_POST['end'];
			$end = strtotime($event['end']); 
			if(strtotime(date('Y-m-d H:i', $end)) != $end){
				$errors[] = array('End is not a valid date.');
			}else{
				$event['end'] = $end;
			}
			
			if(isset($_POST['thx-message'])){
				$event['settings']['thx-message'] = $_POST['thx-message'];
			}
			
			if(isset($_POST['mail-enabled'])){
				$event['settings']['mail']['enabled'] = $_POST['mail-enabled'] == 'on';
			}else{
				$event['settings']['mail']['enabled'] = false;
			}
			
			if(isset($_POST['mail-subject'])){
				$event['settings']['mail']['subject'] = $_POST['mail-subject'];
			}
			if(isset($_POST['mail-from'])){
				$event['settings']['mail']['from'] = $_POST['mail-from'];
			}
			if(isset($_POST['mail-message'])){
				$event['settings']['mail']['message'] = stripslashes($_POST['mail-message']);
			}
			
			if(count($errors) == 0) {
				if ($op == 'edit' 
						? registrate_event_update($event)
						: registrate_event_create($event)
					){
					do_action('registrate_event_' . $op . 'ed', $event);
					return array(
						'op'		=> 'list',
						'messages'	=> array(
							registrate_message($op == 'edit' 
								? 'Event %event was succesfully updated.'
								: 'Event %event was successfully createds.', array('%event' => $event['name'])
							)
						)
					);
				}else{
					$errors[] = registrate_message('Event could not be saved: Database Error');
				}
			}
		}
		
		return array_merge($event, array(
			'errors'	=> registrate_messages($errors),
			'event'		=> $event,
			'forms'		=> $forms
		));
	break;
	case 'activate':
	case 'deactivate':
		$event = registrate_event($_REQUEST['event']);
		if(empty($event)){
			$update = false;
		}else{
			$event['status'] = $op == 'activate' ? 1 : 0;
			$update = registrate_event_update($event);
			/*$update = $wpdb->update($wpdb->prefix . 'onev_events',
				array('status' => $op == "activate" ? 1 : 0),
				array( 'id' => $event->id), array('%d'), array('%d'));*/
		}
		if($update){
			$message = "Event %event has been " . ($op == "activate" ? 'activated' : 'deactivated');
		}else{
			$message = "Event %event could not be " . ($op == "activate" ? 'activated' : 'deactivated');
		}
		$message = registrate_message($message, array('%event' => $event['name']));
		return array(
			'op' => 'list',
			'messages' => array($message)
		);
	break;
	case 'list':
	default:
		$events = registrate_event();
		return array('events' => $events);
	case 'view':
		$event = registrate_event($_REQUEST['event']);
		if(empty($event)){
			?>
			<div class="error fade"><p><strong>
				<?php _e("Can't find event"); ?>
			</strong></p></div>
			<?php
		}else{
			return $event;
		}
	break;
	case 'delete':
		$messages = array();
		$event = registrate_event($_REQUEST['event']);
		if($event){
			registrate_check_hash('event', $op, $event);
			
			if(registrate_event_delete($event)){
				$messages[] = registrate_message('Event %event has been successfully deleted.', array('%event' => $event['name']));
			}else{
				$messages[] = registrate_message('Could not delete event %event.', array('%event' => $event['name']));
			}
		}else{
			$messages[] = registrate_message('Cannot delete event %event: it does not exist.', array('%event' => $_REQUEST['event']));
		}
		
		return array('op' => 'list',
			'messages' => $messages
		);
	break;
}