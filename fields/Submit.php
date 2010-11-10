<?php
class Registrate_Field_Submit
extends Registrate_Field {
	function view(array &$items) {
		?>
		<input type="submit" name="<?php print REGISTRATE_FIELD_PREFIX; ?>" value="<?php print $this->getConfig('text'); ?>" />
		<?php
	}
	function config(){
		return array(
			'label' 	=> false,
			'weight'	=> 1001,
			'text'		=> 'Ok'
		);
	}
	function parseSettings($params) {
		if(isset($params['text'])){
			$this->setConfig('text', $params['text']);
		}
	}
	function showSettings(){
		?><div class="form-item">
			<label for="<?php print $this->settingName('text'); ?>">text:</label>
			<input type="text" name="<?php print $this->settingName('text'); ?>" value="<?php print $this->getConfig('text');?>" />
		</div>
		<?php
	}
	/*function showSettings(){
		return false;
	}*/
	function isFixed(){
		return true;
	}
}