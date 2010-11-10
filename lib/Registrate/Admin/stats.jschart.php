<?php
class JSChart {
	const LINE	= 'line';
	const BAR	= 'bar';
	const PIE	= 'pie';
	
	protected $calls = array();
	protected $type;
	protected $name;
	
	function __construct($name, $type){
		$this->name = $name;
		$this->type = $type;
	}
	
	function __call($method, $args){
		$this->calls[] = array($method, $args);
		return $this;
	}
	function draw(){
		$var = 'jsc_' . $this->name;
		?>
		<div id="<?php print $this->name; ?>"><?php print registrate_message('Loading graph...'); ?></div>
		<script type="text/javascript">
		var <?php print $var; ?> = new JSChart('<?php print $this->name; ?>', '<?php print $this->type; ?>');
		<?php
		foreach($this->calls as $call){
			list($method, $args) = $call;
			foreach($args as $i => $a){
				$args[$i] = $this->_format($a);
			}
			print sprintf("%s.%s(%s);\n", $var, $method, join($args, ', '));
		}
		?>
		<?php print $var; ?>.draw();
		</script>
		<?php
	}
	protected function _format($a){
		if(is_string($a)){
			return sprintf('"%s"', $a);
		}else if(is_array($a)){
			foreach($a as $i => $b){
				$a[$i] = $this->_format($b);
			}
			return '[' . join($a, ", ") . ']';
		}
		return $a;
	}
}

function registrate_stats_age(array $form, array $event){
	$minAge	= 14;
	$maxAge	= 30;
	
	$outerLow	= 0;
	$outerHigh	= 0;
	
	$numMax = 0;
	
	$data = array();
	for($a = $minAge; $a <= $maxAge; $a++){
		$data[$a] = 0;
	}
	
	$stats = registrate_db()->getStats('age', $form, $event);
	foreach($stats as $stat){
		$a = $stat['age'];
		if($a >= $minAge){
			if($a <= $maxAge){
				$data[$a] = $stat['num'] + 0;
			}else{
				$outerHigh += $stat['num'];
			}
		}else{
			$outerLow += $stat['num'];
		}
	}
	$numMax = max($data + array($outerHigh, $outerLow)) + 1;
	
	$array = array(array('<' . $minAge, $outerLow));
	foreach($data as $age => $num) {
		$array[] = array(strval($age), $num);
	}
	$array[] = array('>' . $maxAge, $outerHigh);
	
	$chart = new JSChart('registrateStats_age', JSChart::BAR);
	$chart->setDataArray($array)
			->setSize(800, 300)
			->setTitle(registrate_message('Age pattern'))
			->setTitleFontSize(11);
	$chart->setBarSpacingRatio(50)
			->setAxisNameFontSize(16);
	
	$chart->setAxisNameX(registrate_message('Age'))
			->setAxisValuesDecimals(0)
			->setAxisPaddingBottom(40);
	
	$chart->setAxisNameY('')
			->setAxisValuesNumberY($numMax);
	
	$chart->draw();
	/*?>
	<script type="text/javascript">
		var myChart = new JSChart('registrate-stats-age', 'bar');
		myChart.setDataArray("[['<"" $minAge; ?>', <?php print $outerLow; ?>], <?php foreach($data as $age => $num) { printf("['%s', %d], ", $age, $num); }?>['><?php print $maxAge; ?>', <?php print $outerHigh; ?>]]);
		myChart.setSize(800, 300);
		myChart.setTitle('Age');
		myChart.setTitleColor('#8E8E8E');
		myChart.;
		myChart.setAxisNameY('');
		myChart.setAxisColor('#C4C4C4');
		myChart.setAxisNameFontSize(16);
		myChart.setAxisNameColor('#999');
		myChart.setAxisValuesDecimals(0);
		myChart.setAxisValuesColor('#7E7E7E');
		myChart.setBarValuesColor('#7E7E7E');
		myChart.setAxisValuesNumberY(<?php print $numMax;?>);
		myChart.setAxisPaddingTop(60);
		myChart.setAxisPaddingRight(140);
		myChart.setAxisPaddingLeft(150);
		myChart.setAxisPaddingBottom(40);
		myChart.setTextPaddingLeft(105);
		myChart.setTitleFontSize(11);
		myChart.setBarBorderWidth(1);
		myChart.setBarBorderColor('#C4C4C4');
		myChart.setBarSpacingRatio(50);
		//myChart.setGrid(false);
		//myChart.setBackgroundImage('chart_bg.jpg');
		myChart.draw();
	</script>
	<?php*/
}
function registrate_stats_days(array $form, array $event){
	$numTotal = 0;
	$days = array();
	
	
	$secsPerDay = 86400;
	$startDate	= $event['begin'];
	$endDate	= $event['end'];
	
	$stats = registrate_db()->getStats('days', $form, $event);
	foreach($stats as $set){
		$date = strtotime($set['day']);
		$index = ($date - $startDate) / $secsPerDay;
		$numTotal += $set['num'];
		$days[$index] = array($set['num']+0, $numTotal);
	}
	
	$total = 0;
	$totalValues	= array();
	$dayValues		= array();
	for($i = 0, $d = $startDate; $d < $endDate; $i++, $d += $secsPerDay){
		$s = $i % 7 == 0 ? date('j. n.', $d) : '';
		$dayValues[$i] 		= array($s, isset($days[$i]) ? $days[$i][0] : 0);
		if(isset($days[$i])){
			$total = $days[$i][1];
		}
		$totalValues[$i]	= array($s, $total);
	}
	/*?><table><tr>
	<td><?php var_dump($totalValues);?></td>
	<td><?php var_dump($dayValues);?></td>
	</tr></table>
	<?php*/
	$chart = new JSChart('registrateStats_days', JSChart::LINE);
	$chart->setDataArray($totalValues)
			->setDataArray($dayValues)
			->setSize(800, 300)
			->setTitle(registrate_message('Registrations time line'))
			->setTitleFontSize(11);
	$chart->setBarSpacingRatio(50)
			->setAxisNameFontSize(16);
	
	$chart->setAxisNameX(registrate_message('Day'))
			->setAxisValuesDecimals(0)
			->setAxisPaddingBottom(40);
	
	$chart->setAxisNameY(registrate_message('total'))
			->setAxisValuesNumberY($numTotal + 1);
	
	$chart->draw();
}

$params = $_REQUEST;
$errors = array();
$event = registrate_event($params['event']);
if(! $event){
	$errors[] = registrate_message('Event %event does not exist.', array('%event', $params['event'])); 
}else {
	$form = registrate_form_load($event['form']);
	if(! $form){
		$errors[] = registrate_message('Event %event has invalid form %form attached.',
			array('%event' => $event['name'], '%form' => $event['form']));
	}
}
if(count($errors)){
	return array('errors' => $errors);
}

?>
<div class="wrap">
	<h2><?php print registrate_message('Stats'); ?></h2>
	<script type="text/javascript" src="<?php print WP_PLUGIN_URL; ?>/registrate/jscharts.js"></script>
	<?php 
	registrate_stats_age($form, $event);
	registrate_stats_days($form, $event);
	?>
</div>