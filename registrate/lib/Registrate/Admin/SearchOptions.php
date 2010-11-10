<?php
class Registrate_Admin_SearchOptions {
	protected $searchParams   = array();
	protected $sortParams     = array(); 
	protected $paramSeparator = ";";
	protected $paramIndicator = ":";
	protected $valueSeparator = ",";
	
	
	public function parseSearch($searchString) {
			//if(strpos($s, ":")) {
			// string of format param1:value1a[,value1b[,value1c]][;param2:value2]
		$this->searchParams = array();
		if(! strlen($searchString)) {
			return;
		}
		foreach(explode($this->paramSeparator, $searchString) as $paramString) {
			$pos = strpos($paramString, $this->paramIndicator);
			$paramName	= substr($paramString, 0, $pos);
			if($pos > 0) {
				$pos++;
			}
			$paramValue = substr($paramString, $pos);
			$paramWords = explode($this->valueSeparator, $paramValue);
			
			$this->searchParams[$paramName] = $paramWords;
		}
		//var_dump($this->searchParams);
	}
	public function getSearchConditions(Registrate_Admin_Query $query) {
		$cols = $query->getCols();
		$fullSearchCols = array();
		foreach($cols as $name => $col) {
			if(isset($col['fullSearch']) && $col['fullSearch']){
				$fullSearchCols[$name] = $col;
			}
		}
		
		
		$conditions = array();
		$heaving = array();
		
		foreach($this->searchParams as $name => $param) {
			if($name == "") {
				//match all fields
				$subConditions = array("OR");
				foreach($fullSearchCols as $name => $col) {
					if(! isset($col['alias'])) {
						$subConditions[] = array("LIKE", $name, $param);
					}
				}
				$conditions[] = $subConditions;
			}else{
				if(isset($cols[$name])) {
					$col = $cols[$name];
					if(isset($col['alias'])) {
						$heaving[] = array("LIKE", $name, $param);
					}else{
						$conditions[] = array("LIKE", $name, $param);
					}
				}
			}
		}
		if(count($conditions)) {
			array_unshift($conditions, "AND");
		}
		if(count($heaving)) {
			array_unshift($heaving, "AND");
		}
		//print_r($conditions);
		return array($conditions, $heaving);
	}
	public function getSortOrder(Registrate_Admin_Query $query) {
		$cols = $query->getCols();
		$form = $query->getForm();
		
		$sortOrder = array();
		foreach($this->sortParams as $name => $sort) {
			if(isset($cols[$name])){
				//
			}elseif(isset($form['fields'][$name])){
				$field = $form['fields'][$name];
				
				
				$name = $field->getSortColumn();
				if($name == null){
					continue;
				} 
			}else{
				continue;
			}
			$sortOrder[$name] = $sort;
		}
		return $sortOrder;
	}
	
	public function parseSort($sortString) {
		$this->sortParams = array();
		if(! strlen($sortString)) {
			return;
		}
		foreach(explode($this->paramSeparator, $sortString) as $word) {
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
			$this->sortParams[$word] = $order;
		}
	}
	
	public function build(&$params) {
		if(! isset($params["s"]) && $this->isSearch()) {
			$params["s"] = $this->buildSearch();
		}
		if(! isset($params["sort"]) && $this->isSort()) {
			$params["sort"] = $this->buildSort();
		}
	}
	public function buildSearch() {
		$string = array();
		foreach($this->searchParams as $name => $values){
			if($name != ""){
				$string[] = $name . $this->paramIndicator . join($values, $this->valueSeparator);
			}else{
				$string[] = join($values, $this->valueSeparator);
			}
		}
		return join($string, $this->paramSeparator);
	}
	public function buildSort($additional = array()) {
		$string = array();
		$sort = array_merge($this->sortParams, $additional);
		foreach($sort as $name => $order){
			if($order == "DESC") {
				$name = "-" . $name;
			}
			$string[] = $name;
		}
		return join($string, $this->paramSeparator);
	}
	
	public function isSearch() {
		return count($this->searchParams) > 0;
	}
	public function isSort() {
		return count($this->sortParams) > 0;
	}
	
	public static function create($data) {
		$o = new self();
		if(isset($data["s"])) {
			$o->parseSearch($data["s"]);
		}
		if(isset($data["sort"])) {
			$o->parseSort($data["sort"]);
		}
		return $o;
	}
}