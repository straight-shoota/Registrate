<?php
class Registrate_Field_Select
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'selected' => array()
		);
		?>
		<ol>
			<?php foreach($this->getConfig('options') as $option) : ?>
				<li><input type="<?php print $this->getConfig('mode'); ?>" /></li>
			<?php endforeach; ?>
		</ol>
		<?php
	}
	function showSettings(){
		parent::showSettings();
		
		?>
		<div class="form-item">
			<label for="<?php print $this->settingName('mode'); ?>">Mode:</label>
			<input type="radio" name="<?php print $this->settingName('mode'); ?>" value="radio" <?php if($this->getConfig('mode') == 'radio') { print ' checked="checked"'; }; ?>/> single select
			<input type="radio" name="<?php print $this->settingName('mode'); ?>" value="checkbox" <?php if($this->getConfig('mode') == 'checkbox') { print ' checked="checked"'; }; ?>/> multi select
		</div>
		<div class="form-item">
			<label for="<?php print $this->settingName('options'); ?>">Options:</label>
		</div>
		<div class="form-item">
			<ol>
				
				<?php 
				$options = $this->getConfig('options');
				$options[] = array('label' => '', 'default' => false); 
				foreach($options as $i => $option): ?>
					<li>
						<input type="text" name="<?php print $this->settingName('option_'.$i); ?>" value="<?php print $option['label']; ?>" />
						<input type="<?php print $this->getConfig('mode'); ?>" name="<?php print $this->settingName('option_'.$i.'_default'); ?>" title="Checked on default" />
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
		<?php
	}
	function config(){
		return array(
			'options' => array(),
			'mode' => 'checkbox'
		);
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
		);
	}
	function validate(array &$items, $form = null, $event = null) {
		$errors = array();
		
		return $errors;
	}
	function prepareStoreage(array $form, array $values){
	}
	function prepareDisplay(array &$row, array $event) {
	}
	function getDatabaseColumns() {
		return array(
			//'birthdate' => array('type' => 'date', 'not null' => true, 'sortable' => true)
		);
	}
}