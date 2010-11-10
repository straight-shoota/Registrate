<?php
require_once 'List.php';
		
class Registrate_Admin_Query_Recent
extends Registrate_Admin_Query_List{
	protected $startTime;
	
	public function __construct(array $form, array $event, $startTime = null) {
		parent::__construct($form, $event);
		if($startTime === null){
			$startTime = strtotime('-14 days');
		}
		$this->setStartTime($startTime);
	}
	public function setStartTime($time){
		$this->startTime = $time;
	}
	public function getStartTime(){
		return $this->startTime;
	}
	public function getWhere() {
		$where = Registrate_Db::getDefaultCondition($this->event['id']);
		/*if(isset($this->where)){
			$where->add($this->where);
		}*/
		//$where->add(new Condition_Comparation('regdate', '>', date('Y-m-d', $this->startTime)));
		return $where;
	}
}