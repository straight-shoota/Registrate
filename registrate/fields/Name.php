<?php
class Registrate_Field_Name
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'firstname' => '',
			'lastname' => ''
		);
		?>
		<input type="text" title="Vorname" name="<?php print $this->getIdentifier('firstname'); ?>" value="<?php print $data['firstname']; ?>" />
		
		<input type="text" title="Nachname" name="<?php print $this->getIdentifier('lastname'); ?>" value="<?php print $data['lastname']; ?>" />
		<?php
	}
	function prepareDisplay(array &$row, array $event){
		$row['name'] = '<strong>' . $row['firstname'] . ' ' . $row['lastname'] . '</strong>';
		if($row['status'] == Registrate_Field_Status::CHECKED_IN){
			$row['name'] .= ' <strong>°</strong>';
		}
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			'name' => array(
				'label'		=> 'Name',
				'weight'	=> -100
			)
		);
	}
	function validate(array &$data, $form = null, $event = null){
			$errors = array();
			$data['lastname'] = trim($data['lastname']);
			$data['firstname'] = trim($data['firstname']);
			if(strlen($data['firstname']) == 0){
				$errors[] = array(self::$messages['unset'], array('%field' => 'firstname'));
			}
			if(strlen($data['lastname']) == 0){
				$errors[] = array(self::$messages['unset'], array('%field' => 'lastname'));
			}
			return $errors;
	}
	function getDatabaseColumns() {
		return array(
			'firstname' => array('type' => 'varchar', 'length' => 64, 'not null' => FALSE, 'sortable' => TRUE, 'fullSearch' => true),
			'lastname' => array('type' => 'varchar', 'length' => 64, 'not null' => FALSE, 'sortable' => TRUE, 'fullSearch' => true),
		);
	}
}