<?php
switch($action) {
	case 'forms':
	default:
		$forms = registrate_forms();
		
		?>
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>Fields</th>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach($forms as $name => $form) {
				?>
				<tr><?php
				?>
					<th><?php print $name; ?></th>
					<td><?php print join(array_keys($form['fields']), ", "); ?></td>
					<td>
						<a href="<?php print registrate_admin_url('form', $form, array('action' => 'edit')); ?>">edit</a>
					</td>
				<?php
				?></tr>
				<?php
			}
			?>
			</tbody>
		</table>
		
		<a href="<?php print registrate_admin_url('form', null, array('action' => 'create')); ?>">create New &raquo;</a>
		<?php
	break;
	case 'edit':
		$form = registrate_form_load($_REQUEST['form']);
	case 'create':
		if($action == 'create'){
			$form = array(
				'name'		=> '',
				'fields'	=> array(
					'submit'=> array(),
					'hash'	=> array()
				)
			);
		}
		foreach($form as $k => $v){
			if(isset($_POST[$k])){
				$form[$k] =$_POST[$k];
			}
		}
		?>
		<h2><?php print $action == 'edit' ? "Edit" : "Create"; ?> Form</h2>
		<form method="post">
			<div class="form-item">
				<label for="name">Name:</label>
				<input type="text" name="name" value="<?php print $form['name']; ?>" />
			</div>
			
			<fieldset>
				<legend>Fields</legend>
				<?php foreach(registrate_field_types() as $type => $field) :
					$label = $field->getConfig("label");
					if(!$label) {
						$label = $type;
					}
					$checked = in_array($type, array_keys($form['fields']));
					?>
				<div class="form-item form-field-<?php print $type; ?>">
					<input type="checkbox" name="form-field-<?php print $type; ?>" <?php 
						if($checked) { print 'checked="true" '; }
						if($type == "submit" || $type == "hash") { print 'disabled="disabled" '; }
					?>/>
					<label for="form-field-<?php print $type; ?>"><?php print $label; ?></label>
				</div>
				<?php endforeach; ?>
			</fieldset>
			
			<div class="form-item">
				<input type="submit" value="<?php print $action == 'edit' ? "Save" : "Create"; ?>" />
			</div>
		</form>
		<?php
	break;
}