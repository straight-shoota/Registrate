<?php
require_once dirname(__FILE__) . '/../Query.php';

class Registrate_Admin_Query_Edit 
extends Registrate_Admin_Query {
	protected $itemId;
	protected $type = 'edit';
	
	protected $item;
	
	function __construct(array $form, array $event, $id){
		parent::__construct($form, $event);
		$this->itemId = $id;
	}
	function getItemId(){
		return $this->itemId;
	}
	
	function getItem(){
		if(! isset($this->item)){
			$this->item = registrate_db()->getItem($this->getForm(), $this->getItemId());
		}
		return $this->item;
	}
}