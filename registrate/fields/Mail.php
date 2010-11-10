<?php
class Registrate_Field_Mail
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'mail' => '',
		);
		?>
		<input type="text" name="<?php print $this->getIdentifier(); ?>" size="40" value="<?php print $data['mail']; ?>" />
		
		<?php
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			'mail' => array(
				'label' => 'E-Mail'
			)
		);
	}
	function validate(array &$data, $form = null, $event = null) {
		$errors = array();
		$data['mail'] = trim($data['mail']);
		
		$pattern = '/^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9]([-a-z0-9_]?[a-z0-9])*(\.[-a-z0-9_]+)*\.(aero|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z]{2})|([1]?\d{1,2}|2[0-4]{1}\d{1}|25[0-5]{1})(\.([1]?\d{1,2}|2[0-4]{1}\d{1}|25[0-5]{1})){3})(:[0-9]{1,5})?$/i'; 
		
		if(strlen($data['mail']) == 0){
			$errors[] = array(self::$messages['unset'], array('%field' => 'mail'));
		}elseif(! preg_match($pattern, $data['mail'])) {
			$errors[] = registrate_message('Du scheinst eine ungültige E-Mail-Adresse angegeben zu haben');
		}
		return $errors;
	}
	function getDatabaseColumns() {
		return array(
			'mail' => array('type' => 'varchar', 'length' => 128, 'not null' => FALSE, 'fullSearch' => true)
		);
	}
}