<?php
define('AA', "LOL");
function l($a = array("lol", "we", null, 123, AA)){
var_dump($a);
};
l();
l(array("tet"));
include_once '../lib/Registrate/registrate.php'; 
include_once '../lib/Registrate/Field.php';

session_start();

Registrate_Field::loadClasses('../fields');

$form = registrate_form_load("event");
extract(registrate_form_handle($form));

if($status == REGISTRATE_STATUS_SUBMITTED) {
	?>
	<h3>Thx for registering</h3>
	<?php
}

foreach($errors as $error => $fields) {
	?>
	<div class="message error">
		Error <?php print $error; ?>: <?php print join(array_keys($fields), ", "); ?>
	</div>
	<?php
}
?>
<form method="post">
<?php 
foreach($form['fields'] as $name => $field) {
	?>
	<div class="form-item">
	<label><?php print $field->getConfig('label'); ?></label>
	<?php $field->view($items); ?>
	</div>
	<?php
}
?>
</form>
<?php 