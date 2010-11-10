<?php
require_once '../lib/Registrate/Field.php';

Registrate_Field::loadClasses(dirname(__FILE__) . '/../fields');

$mail = Registrate_Field::create('mail');
test_mail($mail, array(
	'Maggi1-Magdalena@gmx.net' => true
));

$date = Registrate_Field::create('birthdate');
test_b($date, array(
	'21. November 1998' => '1998-11-21',
	'11. Dezember 1992' => '1992-12-11',
	'31.02.1992' => false,
	'2001-09-11' => '2001-09-11',
	'11.6.88' => '1988-06-11',
	'1. Januar 1992' => '1992-01-01'
));
function test_b($field, $data){
	$n = 'birthdate';
	printf('<h3>testing <tt>%s</tt></h3> ', $field->getName());
	foreach($data as $d => $result){
		$array = array($n => $d);
		$tested = $field->validate($array);
		printf('<tt>%s</tt>: ', $d);
		print @date('Y-m-d', $array[$n]) .' => ';
		$valid = $tested === true || ! count($tested);
		if($valid && date('Y-m-d', $array[$n]) == $result XOR $result === false){
			print '<span style="color: green">passed</span>';
		}else{
			print '<span style="color: red; font-weight: bold;">failed</span>';
		}
		print '<br/>';
	}
}
function test_mail($field, $data){
	$n = 'mail';
	printf('<h3>testing <tt>%s</tt></h3> ', $field->getName());
	foreach($data as $d => $result){
		$array = array($n => $d);
		$tested = $field->validate($array);
		printf('<tt>%s</tt>: ', $d);
		print $array[$n] .' => ';
		$valid = $tested === true || ! count($tested);
		if($valid XOR $result === false){
			print '<span style="color: green">passed</span>';
		}else{
			print '<span style="color: red; font-weight: bold;">failed</span>';
		}
		print '<br/>';
	}
}