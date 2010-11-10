<?php

switch($op) {
	case 'view':
		return array(
			'forms' => registrate_form()
		);
		break;
	case 'edit':
	case 'create':
		if($op == 'edit'){
			$form = registrate_form_load($_REQUEST['form']);
			if(!$form){
				return array('op' => 'list',
						'messages' => array(
							registrate_message('Cannot edit form %form: it does not exist.', array('%form' => $_REQUEST['form']))
						)
					);
			}
		}else{
			$form = array(
				'name'		=> '',
				'fields'	=> array()
			);
		}
		foreach($form as $k => $v){
			if(isset($_POST[$k])){
				$form[$k] =$_POST[$k];
			}
		}
		
		$fields = registrate_field_types();
		$errors = array();
		$success = null;
		if(isset($_POST['submit'])){
			if($op == "edit"){
				registrate_check_hash('form', 'edit', $form);
			}
			
			if($op == 'create') {
				if(empty($_POST['name'])){
					$errors[] = array('Name is empty');
				}elseif(! preg_match('/^[a-z0-9_-]{3,16}$/i', $_POST['name'])){
					$errors[] = array('Name %name is invalid. It may only contain alphanumerical characters, underscore and hyphen and must be 3 to 16 charachters long.', array('%name' => $form['name']));
				}elseif($op == 'create' && registrate_form($form['name'])) {
					/*print '<pre>';
					var_dump(registrate_form($form['name']));
					print '</pre>';*/
					$errors[] = array('Name %name is already in use', array('%name' => $form['name']));
				}
				$form['name'] = strtolower($form['name']);
				$form['fields'] = array();
				//$position = 0;
				foreach($_POST as $key => $value){
					$type = substr($key, strlen('form-field-'));
					if(isset($fields[$type])){
						//$field['weight'] = $position;
						$form['fields'][$type] = $fields[$type];
						//$position++;
					}
				}
			}
			$settings = array();
			$weight = 0;
			foreach($_POST as $k => $v){
				@list($prefix, $field, $setting) = preg_split('/-/', $k);
				if($prefix == 'settings'){
					$settings[$field][$setting] = $v;
					$settings[$field]['weight'] = $weight++;
				}
			}
			foreach($settings as $field => $s){
				if(isset($form['fields'][$field])){
					$form['fields'][$field]->parseSettings($s);
				}
			}
			var_dump($settings);
			if(count($errors) == 0){
				$result = $op == "edit"
					? registrate_form_save($form)
					: registrate_form_create($form);
				if($result === true){
					return array('op' => 'list',
						'messages' => array(
							registrate_message($op == 'edit' 
								? 'Form %form was succesfully updated.'
								: 'Form %form was successfully created.', array('%form' => $form['name'])
							)
						)
					);
				}else{
					$errors = array_merge($errors, $result);
				}
			}
		}
		
		if($op == 'create') {
			foreach($fields as $key => $field){
				if($field->isFixed()){
					unset($fields[$key]);
					$form['fields'][$key] = $field;
				}elseif(isset($form['fields'][$key])){
					unset($fields[$key]);
				}
			}
		}
		
		return array(
			'form'		=> $form,
			'errors'	=> registrate_messages($errors),
			'fields'	=> $fields,
			'succes'	=> $success
		);
	break;
	case 'list':
	default:
		return array(
			'forms' => registrate_form()
		);
	break;
	case 'delete':
		$messages = array();
		$form = registrate_form_load($_REQUEST['form']);
		if($form){
			registrate_check_hash('form', $op, $form);
			
			if(registrate_form_delete($form)){
				$messages[] = registrate_message('Form %form was successfully deleted.', array('%form' => $form['name']));
			}else{
				$messages[] = registrate_message('Could not delete %form.', array('%form' => $form['name']));
			}
		}else{
			$messages[] = registrate_message('Cannot delete form %form: it does not exist.', array('%form' => $_REQUEST['form']));
		}
		
		return array('op' => 'list',
			'messages' => $messages
		);
	break;
}