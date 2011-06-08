<?php
function read_dir($path = './') {
	$dir = dir($path);
	$files = array();
	while(($file = $dir->read()) !== false){
		$filepath = $path . $file;
		if(substr($file, -4) === '.php'){
			$files[] = $filepath;
		}else if($file{0} !== '.' && is_dir($filepath)){
			$files = array_merge($files, read_dir($filepath . '/'));
		}
	}
	$dir->close();
	return $files;
}

$functions = array();

function inspect_file($file) {
	global $functions;
	$lines = file($file);
	$is_class = false;
	foreach($lines as $line_number => $line) {
		$line_number++;
		if(preg_match('/(\s|^)(class|interface)\s/', $line)) {
			$is_class = true;
		}
		preg_match_all('/(new\s+|function\s+|->)?([a-z_0-9]+)\s*\(/i', $line, &$hits);
		foreach($hits[2] as $i => $f) {
			if(in_array($f, array('if', 'for', 'while', 'array', 'date', 'trim', 'join', 'time', 'strtotime', 'preg_match', 'floor', 'foreach', 'is_dir', 'dir', 'isset', 'empty', 'sprintf', 'print', 'echo', 'printf', 'var_dump', 'explode', 'strpos', 'is_array', 'intval', 'floatval', 'array_search', 'in_array', 'dirname', 'strlen', 'explode', 'implode', 'is_int', 'is_long', 'is_double', 'array_merge', 'elseif', 'switch', 'is_numeric', 'is_string', 'print_r', 'is_object', 'uniquid', 'md5', 'unset', 'class_exists', 'count', 'substr', 'define', 'int', 'list', 'array_unshift', 'strtolower', 'serialize', 'strtr', 'var_export', 'preg_split', 'array_keys', 'uasort', 'max', 'extract', 'ob_start')) || strtoupper($f) === $f){
				continue;
			}
			switch($hits[1][$i]) {
				case '->':
					$type = 'method call';
				break;
				case '':
					$type = 'call';
				break;
				case 'function ':
					$type = $is_class ? 'method' : 'function';
				break;
				case 'new ':
					$type = 'instantiation';
				break;
			}
			$a = array(
				'file' => $file, 
				'line' => $line_number,
				'type' => $type
			);
			if($type === 'method' || $type === 'method call'){
				$f = '->' . $f;
			}
			if(!isset($functions[$f])){
				$functions[$f] = array();
			}
			if($type === 'call' || $type === 'method call') {
				$functions[$f]['calls'][] = $a;
			}else {
				$functions[$f] = array_merge($functions[$f], $a);
			}
		}
	}
}

$files = read_dir();
foreach($files as $file) {
	inspect_file($file);
}
/*
inspect_file('functions.test.php');
$unused_functions = array();
$undefined_functions = array();
foreach($function_calls as $f => $c) {
	if(! isset($function_defs[$f])){
		$undefined_functions[] = $f;
	}
}
foreach($function_defs as $f => $c) {
	if(! isset($function_calls[$f])){
		$unused_functions[] = $f;
	}
}

print "Unused Functions:<br/>\n";
var_dump($unused_functions);
print "Undefined Functions:<br/>\n";
var_dump($undefined_functions);
print "Function calls:<br/>\n";
var_dump($function_calls);
print "Function definitions:<br/>\n";
var_dump($function_defs);*/

function sort_by_calls($a, $b){
	$a = isset($a['calls']) ? count($a['calls']) : 0;
	$b = isset($b['calls']) ? count($b['calls']) : 0;
	return $a < $b;
}
uasort($functions, 'sort_by_calls');

print "Functions:<br/>\n";

?>
<table>
<tr>
	<th>function</th>
	<th>file</th>
	<th>calls</th>
	<th>type</th>
</tr>
<?php
foreach($functions as $f => $a){
	if(isset($a['file'])) continue; // show only those which are not defined in this domain
	if(isset($a['calls']) && is_array($a['calls'])){
		$c = count($a['calls']);
	}else{
		$c = 0;
	}
	?>
	<tr>
		<td><strong><?php print $f; ?></strong></td>
		<td><?php print isset($a['file']) ? ($a['file'] . ':' . $a['line']) : '<em>unknown</em>'; ?></td>
		<td><?php print $c; ?></td>
		<td><?php print @$a['type']; ?></td>
	</tr>
	<?php
}