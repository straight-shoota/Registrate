<?php
class Registrate_Field_Hash
extends Registrate_Field {
	function view(array &$items) {
		if(! isset($_SESSION['registrate_hash'])){
			$this->generateHash();
		}
		?>
		<input type="hidden" name="<?php print $this->getIdentifier(); ?>" value="<?php print $_SESSION['registrate_hash']; ?>" />
		<?php
	}
	function showSettings(){
		return false;
	}
	function validate(array &$data, $form = null, $event = null){
		if(! isset($_SESSION['registrate_hash'])){
			return array('Your submission was detected to be a possible CSRF attack. Please try again or contact the administrator if this does not solve permanently.');
		}elseif($data['hash'] != $_SESSION['registrate_hash']) {
			return array('Your session could not be validated. Please try again!');
		}else{
			unset($data['hash']);
			$this->generateHash();
			return true;
		}
		//
	}
	function config(){
		return array(
			'label' 	=> false,
			'critical'	=> true,
			'weight'	=> 1000
		);
	}
	function finalize(array &$items, $form = null, $event = null){
		unset($_SESSION['registrate_hash']);
	}
	function generateHash(){
		$_SESSION['registrate_hash'] = md5(uniqid(REGISTRATE_FIELD_PREFIX));
	}
	function isFixed(){
		return true;
	}
	function isHidden(){
		return true;
	}
}
