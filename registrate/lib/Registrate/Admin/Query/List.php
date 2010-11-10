<?php
require_once dirname(__FILE__) . '/../Query.php';

class Registrate_Admin_Query_List
extends Registrate_Admin_Query
implements Zend_Paginator_Adapter_Interface {
	protected $where;
	protected $type    = "list";
	
	protected $params		= array();
	protected $order		= array("regdate" => "DESC");
	protected $pageNumber	= 1;
	protected $itemsPerPage	= 30;
	protected $status		= 'registered';
	
	protected $paginator;
	
	protected static $paramSeparator = ";";
	
	public function __construct(array $form, array $event) {
		parent::__construct($form, $event);
		$this->where = new Condition_Junction();
	}
	
	public function parseParams(array $params){
		if(isset($params['q']) && strlen($params['q'])){
			$this->where = Condition::parseSearchString($params['q'], $this->getDatabaseFields());
		}
		if(isset($params['sort']) && strlen($params['sort'])){
			$this->parseSort($params['sort']);
		}
		if(isset($params['p']) && strlen($params['p'])){
			$this->setCurrentPageNumber($params['p']);
		}
		if(isset($params['status']) && strlen($params['status'])){
			$this->status = $params['status'];
		}
		$this->params = $params;
	}
	protected function parseSort($sortString) {
		$sort = array();
		if(! strlen($sortString)) {
			return;
		}
		foreach(explode(self::$paramSeparator, $sortString) as $word) {
			switch($word{0}) {
				case "-":
					$word  = substr($word,1);
					$order = "DESC";
				break;
				case "+":
					$word  = substr($word,1);
				default:
					$order = "ASC";
			}
			$sort[$word] = $order;
		}
		if(count($sort)){
			$this->setSortOrder($sort);
		}
	}
	public function getWhere() {
		$where = Registrate_Db::getDefaultCondition($this->event['id']);
		switch($this->status) {
			case 'registered': 
				$where->add(new Condition_Comparation(Condition::EQUAL, 'status', Registrate_Field_Status::REGISTERED));
			break;
			case 'registered+':
			case 'registered ':
				$where->add(new Condition_Comparation(Condition::GREATER_OR_EQUAL, 'status', Registrate_Field_Status::REGISTERED));
			break;
			case 'checked_in':
				$where->add(new Condition_Comparation(Condition::EQUAL, 'status', Registrate_Field_Status::CHECKED_IN));
			break;
			case 'deleted':
				$where->add(new Condition_Comparation(Condition::EQUAL, 'status', Registrate_Field_Status::DELETED));
			break;
			case 'all':
			default:
				$this->status = 'all';
		}
		if(isset($this->where)){
			$where->add($this->where);
		}
		return $where;
	}
	
	/*public function getHaving() {
		return $this->having;
	}
	public function setHaving($having) {
		$this->having = $having;
	}*/
	public function getStatus(){
		return $this->status;
	}
	public function getSortOrder() {
		return $this->order;
	}
	public function setSortOrder(array $order) {
		$this->order = $order;
	}
	
	public function getItemCountPerPage() {
		return $this->itemsPerPage;
	}
	public function setItemCountPerPage($count) {
		if($count === false){
			$this->itemsPerPage = false;
		}else{
			$this->itemsPerPage = intval($count);
		}
	}
	
	public function getCurrentPageNumber() {
		return $this->pageNumber;
	}
	public function setCurrentPageNumber($number) {
		$this->pageNumber = intval($number);
	}
	public function isSearch() {
		return isset($this->params['q']);
	}
	public function getSearchString(){
		return $this->isSearch() ? $this->params['q'] : null;
	}
	
	public function getParams(){
		return $this->params;
	}
	
	public function paginator(){
		if(!isset($this->paginator)) {
			require_once 'Zend/Paginator.php';
			
	    	$this->paginator = new Zend_Paginator($this);
			$this->paginator->setCurrentPageNumber($this->getCurrentPageNumber());
			$this->paginator->setItemCountPerPage($this->getItemCountPerPage());
		}
		
		return $this->paginator;
	}
	

	/**
	 * Implementation of Zend_Paginator_Adapter_Interface::getItems
	 * @param int $offset
	 * @param int $itemCountPerPage
	 * @return array
	 */
    public function getItems($offset, $itemCountPerPage) {
    	return registrate_db()->getItems($this);
    }

    /**
     * Implementation of Countable::count
     * @return int
     */
    public function count() {
		if(! $this->isSearch()){
			return $this->event['registrations'];
		}
    	if(! isset($this->count)) {
    		$this->count = registrate_db()->countItems($this);
    	}
    	return $this->count;
    }
}