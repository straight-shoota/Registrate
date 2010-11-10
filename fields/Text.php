<?php
class Registrate_Field_Text
extends Registrate_Field {
	function view(array &$data) {
		print $this->getConfig('text');
	}
	function config(){
		return array('text' => '');
	}
	function showSettings(){
		parent::showSettings();
		
		?><div class="form-item">
			<label for="<?php print $this->settingName('text'); ?>">Text:</label>
			<textarea name="<?php print $this->settingName('text'); ?>"><?php print $this->getConfig('text');?></textarea>
		</div>
		<?php
	}
}