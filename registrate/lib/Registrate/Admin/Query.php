<?php
require_once 'Zend/Paginator/Adapter/Interface.php';

abstract class Registrate_Admin_Query {
	//protected $name;
	protected $form;
	protected $event;
	
	protected $type    = "undefined";
	
	public $sql        = false;
	
	protected $count;
	protected $dbFields;
	protected $cols;
	
	
	public function __construct(array $form, array $event) {
		$this->form = $form;
		$this->event = $event;
	}
	
	public function getForm() {
		return $this->form;
	}
	public function getEvent() {
		return $this->event;
	}
	
	/*public function getName() {
		return $this->name;
	}*/
	public function getType() {
		return $this->type;
	}
	
	public function getDisplayCols(){
		if(! isset($this->cols)){
			$this->cols = array();
			$form = $this->getForm();
		    foreach($form['fields'] as $field){
		    	$new = $field->displayCols($this);
		    	foreach($new as $name => $col){
		    		if(! isset($col['weight'])){
		    			$col['weight'] = $field->getConfig('weight');
		    			if($field->isFixed()){
							$col['weight'] += 100;
		    			}
		    		}
		    		$col['field'] = $field;
		    		$this->cols[$name] = $col;
		    	}
		    }
		    
		    uasort($this->cols, array($this, 'helperSortColumns'));
		}
	    return $this->cols;
	}
	public function helperSortColumns($a, $b) {
		if($a['weight'] == $b['weight']) {
			return 0;
		}
		return $a['weight'] > $b['weight'];
	}
	
	public function getDatabaseFields() {
		if(! isset($this->dbFields)){
			$this->dbFields = array();
			$form = $this->getForm();
			foreach($form['fields'] as $field){
				if(!is_object($field)){
					var_dump($form['fields']);
				}
				$this->dbFields = array_merge($this->dbFields, $field->getDatabaseColumns());
			}
		}
		return $this->dbFields;
	}
	/*public function getFullTextSearchFields(){
		$cols = $this->getDatabaseFields();
		$fullSearchCols = array();
		foreach($cols as $name => $col) {
			if(isset($col['fullSearch']) && $col['fullSearch']){
				$fullSearchCols[] = $name;
			}
		}
		return $fullSearchCols;
	}*/
	public function getDatabaseField($name) {
		$cols = $this->getDatabaseFields();
		$col = $cols[$name];
		$col['name'] = $name;
		return $col;
	}
	public function hasDatabaseField($name) {
		$this->getDatabaseFields();	// make sure $this->cols is set
		return isset($this->dbFields[$name]);
	}
	
	public function isSearch() {
		return false;
	}
}