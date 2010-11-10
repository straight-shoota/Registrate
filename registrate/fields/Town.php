<?php
class Registrate_Field_Town
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'town' => '',
			'zipcode' => ''
		);
		$value = trim($data['zipcode'] . ' ' . $data['town']);
		?>
		<input type="text" name="<?php print $this->getIdentifier(); ?>" size="40" value="<?php print $value; ?>" />
		
		<?php
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			'ziptown' => array(
				'label'		=> 'Town'
			)
		);
	}

	function prepareDisplay(array &$row, array $event) {
		$row['ziptown'] = $row['zipcode'] . '&nbsp;' . $row['town'];
	}
	
	function validate(array &$data, $form = null, $event = null) {
		$errors = array();
		$data['town'] = trim($data['town']);
		if(strlen($data['town']) == 0){
			$errors[] = array(self::$messages['unset'], array('%field' => 'town'));;
		}elseif(preg_match('/^(\d{5})\s+(.+)$/', $data['town'], $hits)){
			$data['zipcode'] = $hits[1];
			$data['town'] = $hits[2];
		}else{
			$errors[] = array(self::$messages['invalid'], array('%field' => 'town'));;
		}
		return $errors;
	}
	function getDatabaseColumns() {
		return array(
			'town' => array('type' => 'varchar', 'length' => 30, 'not null' => false, 'fullSearch' => true),
			'zipcode' => array('type' => 'varchar', 'length' => 5, 'not null' => false)
		);
	}
}