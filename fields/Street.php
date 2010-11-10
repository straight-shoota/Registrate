<?php
class Registrate_Field_Street
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'street' => '',
		);
		?>
		<input type="text" name="<?php print $this->getIdentifier(); ?>" size="40" value="<?php print $data['street']; ?>" />
		
		<?php
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			'street' => array(
				'label'		=> 'Street'
			)
		);
	}
	function display(array $row, $col = null, $context = null) {
		$street = parent::display($row, $col, $context);
		//$street .= sprintf(' <a href="http://maps.google.com?q=%s %s %s"><small>[map]</small></a>', $street, $row['zipcode'], $row['town']);
		return $street;
	}
	function validate(array &$data, $form = null, $event = null) {
		$errors = array();
		$data['street'] = trim($data['street']);
		if(strlen($data['street']) == 0){
			$errors[] = array(self::$messages['unset'], array('%field' => 'street'));;
		}
		return $errors;
	}
	function getDatabaseColumns() {
		return array(
			'street' => array('type' => 'varchar', 'length' => 40, 'not null' => false, 'fullSearch' => true)
		);
	}
}