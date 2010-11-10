<?php
class Registrate_Field_Phone
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'phone' => '',
		);
		?>
		<input type="text" name="<?php print $this->getIdentifier(); ?>" size="16" maxlength="16" value="<?php print $data['phone']; ?>" />
		
		<?php
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			'phone' => array(
				'label'		=>  'Phone'
			)
		);
	}
	function validate(array &$data, $form = null, $event = null) {
		$errors = array();
		$data['phone'] = trim($data['phone']);
		if(strlen($data['phone']) == 0){
			if($this->getConfig('required')){
				$errors[] = array(self::$messages['unset'], array('%field' => 'phone'));;
			}
		}elseif(! preg_match('/^\(?0\d{2,5}\)?\s*[\/-]?\s*\d{3,9}$/', $data['phone'])){
			$errors[] = array(self::$messages['invalid'], array('%field' => 'phone'));
		}else{
			//$data['birthdate'] = 
		}
		return $errors;
	}
	function getDatabaseColumns() {
		return array(
			'phone' => array('type' => 'varchar', 'length' => 16, 'not null' => false)
		);
	}
}