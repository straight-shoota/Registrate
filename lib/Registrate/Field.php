<?php
abstract class Registrate_Field {
	protected $config;
	protected $name;
	
	static $messages = array(
		'invalid'	=> 'Please enter a valid value on %field.',
		'unset' => 'You must enter a value on %field.',
	);
	
	function __construct($name) {
		$this->name = $name;
		$this->config = $this->config() + array(
			'label'		=> $name,
			'critical'	=> false,
			'required'	=> true,
			'hasError'  => false,
			'weight'	=> 0,
			'description' => false,
			'title'		=> false
		);
	}
	
	function setConfig($name, $value = null){
		if(is_array($name)){
			$this->config = array_merge($this->config, $name);
		}else{
			$this->config[$name] = $value;
		}
	}
	function getIdentifier($name = null){
		if($name == null){
			$name = $this->name;
		}
		return $name;
	}
	function getConfig($name = null, $default = null) {
		if($name != null){
			if(isset($this->config[$name])){
				return $this->config[$name];
			}
			return $default;
		}
		return $this->config;
	}
	function getLabel(){
		return $this->getConfig('label', $this->getName());
	}
	function getName(){
		return $this->name;
	}
	function getTitle() {
		$title = $this->getConfig('title', $this->getLabel());
		if($title == ''){
			return $this->getLabel();
		}
		return $title;
	}
	function getDescription() {
		return $this->getConfig('description', false);
	}
	function config(){
		return array();
	}
	function settingName($name = ''){
		return 'settings-' . $this->getName() . '-' . $name;
	}
	function parseSettings($params) {
		if($this->getLabel() && isset($params['label'])){
			$this->setConfig('label', $params['label']);
		}
		
		if(! $this->isFixed()){
			$this->setConfig('required', isset($params['required']) && $params['required'] == 'on');
		}
		
		if(isset($params['weight'])){
			$this->setConfig('weight', $params['weight']);
		}
		if(isset($params['title'])){
			$this->setConfig('title', $params['title']);
		}
		if(isset($params['description'])){
			$this->setConfig('description', $params['description']);
		}
	}
	function showSettings(){
		if($this->getLabel()) : ?>
		<div class="form-item">
			<label for="<?php print $this->settingName('label'); ?>">Label:</label>
			<input name="<?php print $this->settingName('label'); ?>" type="text" value="<?php print $this->getLabel(); ?>"/>
		</div>
		<?php endif;
		if(! $this->isFixed()): ?>
		<div class="form-item">
			<label for="<?php print $this->settingName('required'); ?>">Required:</label>
			<input name="<?php print $this->settingName('required'); ?>" type="checkbox" <?php if($this->getConfig('required')) { print ' checked="checked"'; } ?>/>
		</div>
		<?php endif;?>
		<div class="form-item">
			<label for="<?php print $this->settingName('title'); ?>">Title:</label>
			<input size="20" name="<?php print $this->settingName('title'); ?>" type="text" <?php if($this->getTitle()) { print $this->getConfig('title'); } ?>/>
		</div>
		<div class="form-item">
			<label for="<?php print $this->settingName('description'); ?>">Description:</label>
			<textarea name="<?php print $this->settingName('description'); ?>"><?php if($this->getDescription()) { print $this->getConfig('description'); } ?></textarea>
		</div>
		<?php
	}
	function validate(array &$items, $form = null, $event = null) {
		return true;
	}
	function prepareStorage(array $form, array $values){
		
	}
	function finalize(array &$items, $form = null, $event = null){
		
	}
	abstract function view(array &$items);
	function prepareDisplay(array &$row, array $event) {
		
	}
	function display(array $row, $col = null, $context = null) {
		if($col == null){
			$col = $this->getName();
		}
		return $row[$col];
	}
	function displayCols(Registrate_Admin_Query $query){
		return array();
	}
	
	function getDatabaseColumns(){
		return array();
	}
	function getSortColumn() {
		$cols = $this->getDatabaseColumns();
		if(count($cols)){
			return array_pop(array_keys($cols));
		}
		return null;
	}	
	function getErrorMessages(){
		return array();
	}
	
	function isFixed(){
		return false;
	}
	function isHidden(){
		return false;
	}
	
	function printEditCol($col, $raw, $event, $forceEditing = false){
		if(! $this->isFixed() || $forceEditing) {
			?>
			<input type="text" name="registrate-field-<?php print $col['name']; ?>" value="<?php print htmlspecialchars($raw[$col['name']]); ?>" />
			<?php
		}else{
			//$event = registrate_event($raw['event']);
			$this->prepareDisplay($raw, $event);
			print $this->display($raw, $col['name'], 'editing');
		}
	}
	
	/* ################################ 
	 * 			Static Factory
	 * ################################*/
	
	protected static $fieldRegistry = array();
	public static function getTypes() {
		return array_keys(self::$fieldRegistry);
	}
	public static function registerType($name, $class) {
		self::$fieldRegistry[$name] = $class;
	}
	public static function unregisterType($name) {
		unset(self::$fieldRegistry[$name]);
	}
	public static function create($type, $options = array()) {
		$class = self::getClass($type);
		if(! class_exists($class)) {
			$class = 'Registrate_Field_' . $class;
		}
		if(! class_exists($class)) {
			throw new Exception('Class not found: ' . $class . ' for type ' . $type);
			//$class = get_class(); 
		}
		$field = new $class($type);
		
		if(! empty($options) && is_array($options)) {
			$field->setConfig($options);
		}
		
		return $field;
	}
	public static function getClass($type) {
		if(isset(self::$fieldRegistry[$type]))
			return self::$fieldRegistry[$type];
		else
			return $type;
	}
	public static function loadClasses($dir = false) {
		if(! $dir) {
			$dir = dirname(__FILE__) . "../fields";
		}
		$d = dir($dir);
		while (false !== ($file = $d->read())) {
			if(substr($file, -4) == ".php") {
				$name = substr($file, 0, -4);
				$class = 'Registrate_Field_' . $name;
				include_once $d->path . '/' . $file;
				//if(class_exists($class))
				self::registerType(strtolower($name), $class);
			}
		}
		$d->close();
	}
}
function registrate_field_types() {
	$types = Registrate_Field::getTypes();
	$fields = array();
	foreach($types as $type) {
		$fields[$type] = Registrate_Field::create($type); 
	}
	return $fields;
}