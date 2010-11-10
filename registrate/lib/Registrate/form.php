<?php
define('REGISTRATE_FIELD_PREFIX', 'registrate_form');

/**
 * registrate_form() - Returns array of all available forms
 * registrate_form(string) - Returns the form with the specified a name or false if doesnt exists.
 * registrate_form(string, array) - Stores an altered form on the specified name.
 * registrate_form(string, null) - Deletes form.
 * @param string $event
 * @return string
 */
function registrate_form($name = false, array $form = null) {
	if($name === false){
		$forms = registrate_db()->loadForms();
		if(! is_array($forms))
			$forms = array();
		return $forms;
	}
	if($form === null) {
		return registrate_db()->getForm($name);
	}
	return registrate_db()->setForm($name, $form);
}

define('REGISTRATE_STATUS_SUBMITTED', "submitted");
define('REGISTRATE_STATUS_INVALID', "invalid");
define('REGISTRATE_STATUS_ERROR', "error");
define('REGISTRATE_STATUS_FORBIDDEN', "forbidden");
define('REGISTRATE_STATUS_NULL', 'null');
define('REGISTRATE_STATUS_FAILED_SUBMISSION', 'failedSubmission');

/**
 * Main hook for form processing.
 * @param array $form
 * @param array $event
 * @return array
 */
function registrate_form_handle(array $event) {
	//global $registrate_messages;
	$items = array();
	$errors = array();
	$form = registrate_form_load($event['form']);

	if($form){
		if(isset($_POST[REGISTRATE_FIELD_PREFIX])) {
			foreach($_POST as $k => $v) {
				$items[$k] = $v;
			}
			$critical_error = false;
			$groupedErrors = array();
			foreach($form['fields'] as $name => $field) {
				//$registrate_messages += $field->getErrorMessages();
				$validate = $field->validate(&$items, $form, $event);
				if($validate === true || $validate === null || count($validate) == 0) {
					//print $name.": all good <br/>";
				}else{
					foreach($validate as $error){
						if(is_array($error) && $k = array_search($error[0], Registrate_Field::$messages)){
							$groupedErrors[$k][] = registrate_message('!col:' . $error[1]['%field'], array('!col:' => ''));;
						}elseif($field->getConfig('required')){
							$errors[] = $error;
						}
					}
					
					$field->setConfig('hasError', true);
					if($field->getConfig('critical')){
						$critical_error = true;
					}
				}
			}
			foreach(Registrate_Field::$messages as $k => $msg){
				if(isset($groupedErrors[$k])){
					array_unshift($errors, array($msg, array('%field' => join($groupedErrors[$k], ', '))));
				}
			}
			//var_dump($errors);
			if(empty($errors)){
				if(registrate_register($form, $event, $items)){
					foreach($form['fields'] as $field){
						$field->finalize($items);
					}
					$status = REGISTRATE_STATUS_SUBMITTED;
				}else{
					$status = REGISTRATE_STATUS_FAILED_SUBMISSION;
				}
			}else{
				if($critical_error){
					// clear fields on csrf attack
					//$items = array();
				}
				$status = REGISTRATE_STATUS_INVALID;
				do_action('registrate_registration_invalid', $form, $event, array(
					'status'	=> $status,
					'errors'	=> registrate_messages($errors),
					'items'		=> $items
				));
			}
		}else{
			$status = REGISTRATE_STATUS_NULL;
		}
	}else{
		do_action('registrate_registration_error', $form, null, array('errors' => $errors));
		$status = REGISTRATE_STATUS_ERROR;
	}
	
	return array(
		'status'	=> $status,
		'errors'	=> registrate_messages($errors),
		'items'		=> $items,
		'form'		=> $form
	);
}

function registrate_form_view($vars){
	extract($vars);
	if(count($errors)){
		?>
		<div class="registrate-messages">
		<?php
		foreach($errors as $error){
			?>
			<div class="message error">
				<?php print $error; ?>
			</div>
			<?php
		}
		?>
		</div>
		<?php
	}
	?>
	<div class="" style="margin: 1em;">
		<form method="post" class="yform">
		<?php foreach($form['fields'] as $name => $field) :	?>
			<?php
			ob_start();
			$field->view($items);
			$view = ob_get_clean();
			if($view == false){
				continue;
			}
			?>
			<div class="form-item<?php if($field->getConfig('hasError')) { print ' error'; } ?>">
			<label for="<?php print $field->getIdentifier(); ?>"><span><?php print $field->getConfig('label'); ?></span></label>
			<?php 
			if($field->getTitle()){
				?>
				<span title="<?php print $field->getTitle(); ?>">
				<?php
			}
			print $view;
			if($field->getTitle()){
				?>
				</span>
				<?php
			}
			
			if($field->getDescription() !== false){
				?>
				<p class="description"><?php print $field->getDescription(); ?></p>
				<?php
			}
			?>
			</div>
		<?php endforeach; ?>
		</form>
	</div>
	<?php
}

/**
 * Loads form data from database
 * @param string|array $form
 * @return array
 */
function registrate_form_load($form) {
	if(! is_array($form)) {
		$form = registrate_form($form);
	}
	
	if($form == null){
		return false;
	}
	
	$name = $form['name'];
	registrate_form_add_default(&$form, $name);
	
	// adding default settings
	foreach($form['fields'] as $field_name => $config) {
		$field = Registrate_Field::create($field_name, $config);
		$form['fields'][$field_name] = $field;
	}
	
	uasort($form['fields'], 'registrate_sort_field');
	
	$form['name'] = $name;
	return $form;
}

function registrate_form_save(array $form){
	foreach($form['fields'] as $name => $field){
		$form['fields'][$name] = $field->getConfig();
	}
	
	if(registrate_db()->setForm($form['name'], $form)){
		do_action('registrate_form_edited', $form);
		return true;
	}else{
		return array(
			array('form could not be saved')
		);
	}
}

function registrate_form_delete(array $form){
	if(registrate_db()->deleteFormTable($form['name'])){
		do_action('registrate_form_deleted', $form);
		return registrate_db()->setForm($form['name'], null);
	}else{
		return array(
			array('Form deletion failed: Could not delete database table')
		);
	}
}
function registrate_form_create(array $form){
	$errors = array();
	
	$cols = registrate_form_cols($form);
	
	if(!registrate_db()->createFormTable($form['name'], $cols)){
		return array(
			array('Form creation failed: Could not create database table')
		);
	}
	//$cols = array_merge($cols, registrate_form_default_cols());
	$sql = var_export($cols, true);
	//print "<pre>$sql</pre>";
	
	/*$errors[] = array('form creation is not yet implemented');
	
	return $errors;*/
	
	$save = registrate_form_save($form);
	if(is_array($save)){
		$errors = array_merge($errors, $save);
	}else{
		do_action('registrate_form_created', $form);
		return true;
	}
}

/**
 * Returns an array of all database columns defined by a form's fields.
 * @param array $form
 * @return unknown_type
 */
function registrate_form_cols(array $form){
	$form = registrate_form_load($form);
	$cols = array();
	foreach($form['fields'] as $field){
		$cols = array_merge($cols, $field->getDatabaseColumns());
	}
	/*foreach(registrate_field_types() as $field){
		if($field->isFixed()){
			$cols = array_merge($cols, $field->getDatabaseColumns());
		}
	}*/
	return $cols;
}

function registrate_form_add_default(&$form, $event) {
	foreach(registrate_field_types() as $name => $field){
		if($field->isFixed()){
			if(!isset($form['fields'][$name])){
				$form['fields'][$name] = array();
			}
		}
	}
}


function registrate_sort_field($a, $b) {
	if($a->getConfig('weight') == $b->getConfig('weight')) {
		return 0;
	}
	return $a->getConfig('weight') > $b->getConfig('weight');
}