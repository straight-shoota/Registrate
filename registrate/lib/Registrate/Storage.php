<?php
interface Registrate_Storage {
	public function storeItem(array $form, array $values);
	public function getItem(array $form, $id);
	public function updateItem(array $form, $id, array $values);	
	public function deleteItem(array $form, $id);
	public function getItems(Registrate_Admin_Query_List $query);
	public function countItems(Registrate_Admin_Query_List $query);
	
	
	public function loadForms();
	//public function storeForms(array $forms);
	public function getForm($name);
	public function setForm($name, $form);
	
	public function createFormTable($fname, array $cols);
	public function deleteFormTable($fname);
	
	public function getEvent($name);
	public function addEvent(array $event);
	public function updateEvent(array $event);
	public function getEvents();
	
	public function updateRegistrationCounter(&$event);
	
	public function log($message, $data, $form = null, $event = null, $item = null);
	public function getLog($filter = array());
	public function getLogMessages();
	
	public function supportsStatType($type);
	public function getStats($type, array $form, array $event);
}