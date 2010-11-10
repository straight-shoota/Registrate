<?php
class Registrate_Field_Birthdate
extends Registrate_Field {
	function view(array &$data) {
		$data += array(
			'birthdate' => '',
		);
		$date = $data['birthdate'];
		if(is_int($date)) {
			$date = date('j. F Y', $data['birthdate']);
		}
		?>
		<input type="text" name="<?php print $this->getIdentifier(); ?>" size="15" value="<?php print $date; ?>" />
		
		<?php
	}
	function displayCols(Registrate_Admin_Query $query){
		return array(
			/*'birthdate' => array(
				'label'		=> 'Birthdate'
			),*/
			'age' => array(
				'label'		=> 'Age'
			),
		);
	}
	function validate(array &$data, $form = null, $event = null) {
		$errors = array();
		$data['birthdate'] = trim($data['birthdate']);
		if(strlen($data['birthdate']) == 0){
			$errors[] = array(self::$messages['unset'], array('%field' => 'birthdate'));
		}elseif(false === strtotime($data['birthdate'])){
			$month_names = array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
			$pattern = '/^(\d{1,2})\.\s*(' . join($month_names, '|') . ')\s+(\d{4})$/';
			//var_dump($pattern);
			if(preg_match($pattern, $data['birthdate'], $hits)){
				$month_names = array_flip($month_names);
				$data['birthdate'] = strtotime($hits[3] . '-' . ($month_names[$hits[2]]+1) . '-' . $hits[1]);
			}elseif(preg_match('#(\d{1,2})\.\s*(\d{1,2}).\s*(\d{4})#', $data['birthdate'], $hits)){
				$data['birthdate'] = strtotime($hits[3] . '-' . $hits[2] . '-' . $hits[1]);
			}elseif(preg_match('#(\d{1,2})/\s*(\d{1,2})/s*(\d{4})#', $data['birthdate'], $hits)){
				$data['birthdate'] = strtotime($hits[3] . '-' . $hits[2] . '-' . $hits[1]);
			}else{
				$errors[] = array(self::$messages['invalid'], array('%field' => 'birthdate'));
			}
		}else{
			$data['birthdate'] =  strtotime($data['birthdate']);
		}
		return $errors;
	}
	function prepareStorage(array $form, array $data){
		$data['birthdate'] = date('Y-m-d', $data['birthdate']);
	}
	function display(array $row, $col = null, $context = null){
		$d = parent::display($row, $col);
		if($col == 'birthdate') {
			$d = date("j. F Y", $d);
		}
		return $d;
	}
	function prepareDisplay(array &$row, array $event) {
		$row['birthdate'] = strtotime($row['birthdate']);
		$row['age'] = floor((time() - $row['birthdate']) / (60 * 60 * 24 * 365));
	}
	function getDatabaseColumns() {
		return array(
			'birthdate' => array('type' => 'date', 'not null' => true, 'sortable' => true,
				'additional' => array(
					'age' => new Birthdate_Condition('birthdate')
				)
			)
		);
	}
}
if(class_exists('Condition_Comparation')){
	class Birthdate_Condition
	extends Condition_Comparation {
		public function __construct($field) {
			parent::__construct($field, self::GREATER);
		}
		public function setValue($value){
			parent::setValue(date('Y-m-d', strtotime(sprintf('now - %dyears', $value))));
		}
		public function setOperator($operator){
			parent::setOperator($this->flipOperator($operator));
		}
	}
}else{
	class Birthdate_Condition{
	}
}