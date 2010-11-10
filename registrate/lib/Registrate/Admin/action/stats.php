<?php
function registrate_stats_jsformat($a){
	if(is_string($a)){
		return sprintf('"%s"', $a);
	}else if(is_array($a)){
		foreach($a as $i => $b){
			$a[$i] = registrate_stats_jsformat($b);
		}
		return '[' . join($a, ", ") . ']';
	}
	return $a;
}
function registrate_stats_age(array $form, array $event){
	$minAge	= 14;
	$maxAge	= 30;
	$avgAge = 0;
	$avgAgeTA = 0;

	$outerLow	= 0;
	$outerHigh	= 0;

	$numMax		= 0;
	$numTotal	= 0;
	$numTotalTA = 0;

	$data = array();
	for($a = $minAge; $a <= $maxAge; $a++){
		$data[$a] = false;
	}

	$stats = registrate_db()->getStats('age', $form, $event);
	foreach($stats as $i => $stat){
		$a = $stat['age'];
		if($a >= $minAge){
			if($a <= $maxAge){
				$data[$a] = $stat['num'] + 0;
				$avgAgeTA += $a * $stat['num'];
				$numTotalTA += $stat['num'];
			}else{
				$outerHigh += $stat['num'];
			}
		}else{
			$outerLow += $stat['num'];
		}
		$avgAge += $a * $stat['num'];
		$numTotal += $stat['num'];
	}
	$avgAge /= $numTotal;
	$avgAgeTA /= $numTotalTA;
	$numMax = max($data + array($outerHigh, $outerLow)) + 1;

	$array = array(array($minAge - 1.25, $outerLow > 0 ? $outerLow : false));
	foreach($data as $age => $num) {
		$array[] = array(strval($age), $num);
	}
	$array[] = array($maxAge + 1.25, $outerHigh > 0 ? $outerHigh : false);

	$name = 'flot_age';
	?>
	<p><?php print registrate_message('Average age: <strong>!age</strong>', array('!age' => sprintf('%.1f', $avgAge))); ?></p>
	<p><?php print registrate_message('Average age (target audience): <strong>!age</strong>', array('!age' => sprintf('%.1f', $avgAgeTA))); ?></p>
<div id="<?php print $name; ?>" style="height: 300px; width: 600px;"><?php print registrate_message('Loading graph...'); ?></div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$.plot($('#<?php print $name; ?>'), [{
			data: <?php print registrate_stats_jsformat($array); ?>,
			bars: { show: true },
			
		}], {
			xaxis: {
				ticks: [[<?php print $minAge - 1.25;?>, "< <?php print $minAge; ?>"], <?php foreach($array as $b) {
					if($b[0] == $minAge - 1.25 || $b[0] == $maxAge + 1.25) continue;
					printf('[%d, "%s"], ', $b[0], $b[0]);
				}	?> [<?php print $maxAge + 1.25; ?>, "> <?php print $maxAge; ?>"]],
				min: <?php print $minAge - 2; ?>,
				max: <?php print $maxAge + 2; ?>,
			},
			series: {
				bars: {
					align: 'center',
					barWidth: 0.6,
					lineWidth: 1.2
				}
			},
			yaxis: {
				tickDecimals: false
			},
			grid: {
	            backgroundColor: { colors: ["#fff", "#eee"] },
	            hoverable: true,
	            clickable: true,
	            markings: [{ color: '#ddd', xaxis: { to: <?php print $minAge - .5; ?> } },
	       	            { color: '#ddd', xaxis: { from: <?php print $maxAge + .5; ?> } },
	       	            { color: 'red', xaxis: { from: <?php printf('%.1f', $avgAge);?>, to: <?php printf('%.1f', $avgAge);?>}},
	       	            { color: 'blue', xaxis: { from: <?php printf('%.1f', $avgAgeTA);?>, to: <?php printf('%.1f', $avgAgeTA);?>}}]
	        }
		});
		var previousPoint = null;
		$("#<?php print $name; ?>").bind("plothover", function (event, pos, item) {
            if (item) {
                if (previousPoint != item.datapoint) {
                    previousPoint = item.datapoint;
                    
                    $("#tooltip").remove();
                    var x = item.datapoint[0],
                        y = item.datapoint[1];
					
                    showTooltip(item.pageX, item.pageY-3, y, {'background-color': '#ed8', padding: '0 5px', 'border-color': '#da8'}, "top center");
                }
            }
            else {
                $("#tooltip").remove();
                previousPoint = null;            
            }
    });
	});
	</script>
				<?php

}
function registrate_stats_timeline(array $form, array $event){
	$numTotal = 0;
	$days = array();


	$secsPerDay = 86400;
	$startDate	= $event['begin'];
	$endDate	= $event['end'];
	
	$total = 0;

	$stats = registrate_db()->getStats('timeline', $form, $event);
	foreach($stats as $set){
		$date = strtotime($set['day']);
		$numTotal += $set['num'];
		if($date < $startDate){
			$total += $set['num'];
		}else{
			$index = ($date - $startDate) / $secsPerDay;
			$days[$index] = array($set['num']+0, $numTotal);
		}
	}
	
	$totalValues	= array();
	$dayValues		= array();
	$today			= time();
	for($i = 0, $d = $startDate; $d < $endDate && $d < $today; $i++, $d += $secsPerDay){
		//$s = $i % 7 == 0 ? date('j. n.', $d) : '';
		$dayValues[$i] 		= array($d * 1000, isset($days[$i]) ? $days[$i][0] : (isset($days[$i+1]) || isset($days[$i-1]) ?  0 : false));
		if(isset($days[$i])){
			$total = $days[$i][1];
		}
		$totalValues[$i]	= array($d * 1000, $total);
	}
	$totalValues[] = array(time() * 1000, $total);
	$name = 'flot_timeline';
	
	$shortMonthNames = array("Jan", "Feb", "Mar", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
	foreach($shortMonthNames as $i => $month){
		$shortMonthNames[$i] = registrate_message('!month-short:' .$month, array('!month-short:' => ''));
	}
	$longMonthNames = array("January", "February", "March", "April", "Mai", "June", "Juli", "August", "September", "October", "November", "December");
	foreach($longMonthNames as $i => $month){
		$longMonthNames[$i] = registrate_message('!month:' .$month, array('!month:' => ''));
	}
	?>
	<p><?php print registrate_message('registration time range: from <strong>!date-from</strong> to <strong>!date-to</strong>', array('!date-from' => date_i18n('j. F Y H:i', $event['begin']), '!date-to' => date_i18n('j. F Y H:i', $event['end']))); ?></p>
<div id="<?php print $name; ?>" style="height: 300px;"><div class="loading"><?php print registrate_message('Loading graph...'); ?></div></div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$.plot($('#<?php print $name; ?>'), [{
			data: <?php print registrate_stats_jsformat($totalValues); ?>,
			lines: { show: true, fill: true },
			points: {},
			yaxis: 2,
			label: '<?php print registrate_message('total registrations'); ?>'
		}, {
			data: <?php print registrate_stats_jsformat($dayValues); ?>,
					lines: { show: true},
					points: { show: true},
					yaxis: 1,
					label: '<?php print registrate_message('registrations per day'); ?>',
		}], {
			xaxis: {
				mode: 'time',
				timeformat: "%d. %b",
				monthNames: <?php print registrate_stats_jsformat($shortMonthNames); ?>,
				tickSize: [7, "day"],
				min: <?php print $startDate * 1000; ?>,
				max: <?php print $endDate * 1000; ?>
			},
			yaxis: {
				tickDecimals: false
			},
			y2axis: {
				tickDecimals: false
			},
			series: {
			},
			legend: {
				position: 'nw',
			},
			grid: {
	            backgroundColor: { colors: ["#fff", "#eee"] },
	            hoverable: true,
	            clickable: true,
	            markings: [{ color: '#DDD', xaxis: { from: <?php print time() * 1000; ?> }},
	                 { color: '#888', lineWidth: 1, xaxis: { from: <?php print time() * 1000; ?>, to: <?php print time() * 1000; ?> }}]
	        }
		});
		
		var previousPoint = null;	
		$("#<?php print $name; ?>").bind("plothover", function (event, pos, item) {
	            if (item && previousPoint != item.datapoint) {
	                previousPoint = item.datapoint;
					
                    $("#tooltip").remove();
                    var x = item.datapoint[0],
                        y = item.datapoint[1];
                        
					x = $.plot.formatDate(new Date(x), "%d. %b %y", <?php print registrate_stats_jsformat($longMonthNames); ?>);
					/*for(i in item){
						x = i + " " + x;
					}*/
					css = { 'background-color': '#eef', 'border-color': '#ddf'};
					if(item.seriesIndex == 1) {
						switch(y){
							case 1: y = '<?php print registrate_message('1 new registration');?>';
							break;
							case 0: y = '<?php print registrate_message('no new registration');?>';
							break;
							default:
								y = strtr('<?php print registrate_message('%num new registrations'); ?>', '%num', y);
						}
					}else{
						y = strtr('<?php print registrate_message('total: %num registrations'); ?>', '%num', y);
						css['background-color'] = '#ed8';
						css['border-color'] = '#da8'
					}
                    showTooltip(item.pageX, item.pageY - 10,
    	                    "<strong>" + x + "</strong><br/> " + y,
    	                    css,
                    		'top center'
                    	);
	            }
	            else {
	                $("#tooltip").remove();
	                previousPoint = null;            
	            }
	    });
	});
	
	</script>
	<?php
}
function registrate_stats_hours(array $form, array $event){
	$hours = array();
	for($i = 0; $i < 24; $i++){
		$hours[$i] = array($i, 0);
	}
	
	$stats = registrate_db()->getStats('hours', $form, $event);
	//var_dump($stats);
	foreach($stats as $set){
		$h = $set['hour'] + 0;
		$hours[$h][1] = $set['count'] + 0;
	}
	$name = 'flot_hours';
	?>
	<div id="<?php print $name; ?>" style="height: 300px;"><div class="loading"><?php print registrate_message('Loading graph...'); ?></div></div>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$.plot($('#<?php print $name; ?>'), [{
				data: <?php print registrate_stats_jsformat($hours); ?>,
				bars: { show: true },
			}], {
				series: {
					bars: {
						align: 'center',
						barWidth: 0.6,
						lineWidth: 1.2
					}
				},
				xaxis: {
					ticks: [<?php
					foreach($hours as $h) {
						printf('[%d, "%d&ndash;%d"], ', $h[0], $h[0], $h[0]+1);
					}?>],
					tickDecimals: false
				},
				yaxis: {
					tickDecimals: false
				},
			}
		)
	});
	</script>
	<?php
}
function registrate_stats_days(array $form, array $event){
	$days = array();
	
	$stats = registrate_db()->getStats('days', $form, $event);
	//var_dump($stats);
	
	for($i = 1; $i <= 7; $i++){
		$days[$i] = array($i, 0);
	}
	
	foreach($stats as $set){
		$h = $set['day'] + 0;
		$days[$h] = array($h, $set['count'] + 0);
	}
	$name = 'flot_days';
	
	$day_names = array('so', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
	?>
	<div id="<?php print $name; ?>" style="height: 300px;width: 600px;"><div class="loading"><?php print registrate_message('Loading graph...'); ?></div></div>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		$.plot($('#<?php print $name; ?>'), [{
				data: <?php print registrate_stats_jsformat($days); ?>,
				bars: { show: true },
			}], {
				series: {
					bars: {
						align: 'center',
						barWidth: 0.6,
						lineWidth: 1.2
					}
				},
				xaxis: {
					ticks: [<?php
					foreach($days as $h) {
						printf('[%d, "%s"], ', $h[0], $day_names[$h[0]-1]);
					}?>],
					tickDecimals: false
				},
				yaxis: {
					tickDecimals: false
				},
			}
		)
	});
	</script>
	<?php
}
function registrate_stats_towns(array $form, array $event){
	$towns = array();
	
	$stats = registrate_db()->getStats('towns', $form, $event);
	$max = 0;
	foreach($stats as $set) {
		$max = max($set['count'], $max);
	}
	
	?>
	<table class="widefat" style="width: 600px;">
		<colgroup>
			<col style="width: 6em;" />
			<col style="width: 16em;" />
			<col />
		</colgroup>
		<thead>
			<tr>
				<th><?php print registrate_message('zipcode'); ?></th>
				<th><?php print registrate_message('town'); ?></th>
				<th><?php print registrate_message('registrations'); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($stats as $set) :?>
			<tr>
				<td><?php print $set['zipcode']; ?></td>
				<td><a href="<?php print registrate_admin_url('item', 'list', $event, array('q' => $set['town'])); ?>"><?php print $set['town']; ?></a></td>
				<td style="padding: 2px;">
				<div style="background-color: #FFEF9C; font-weight: bold; width: <?php print $set['count']/$max * 100; ?>%;"><div style="padding: 2px; "><?php print $set['count']; ?></div></div></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php
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
	<h2><?php print registrate_message('Stats for %event', array('%event' => $event['description'])); ?></h2>
	<script type="text/javascript" src="<?php print WP_PLUGIN_URL; ?>/registrate/flot/jquery.flot.js"></script>
	<p><?php print registrate_message('Total registrations: <strong>!registrations</strong>', array('!registrations' => $event['registrations'])); ?>
	<small>(<?php print registrate_message('as of <strong>!date</strong>', array('!date' => date_i18n('j. F Y H:i'))); ?>)</small></p>
	<h3><?php print registrate_message('Registrations time line'); ?></h3>
	<?php registrate_stats_timeline($form, $event); ?>
	<h3><?php print registrate_message('Age pattern'); ?></h3>
	<?php registrate_stats_age($form, $event); ?>
	<h3><?php print registrate_message('Registration hours'); ?></h3>
	<?php registrate_stats_hours($form, $event); ?>
	<h3><?php print registrate_message('Registration days'); ?></h3>
	<?php registrate_stats_days($form, $event); ?>
	<h3><?php print registrate_message('Registration towns'); ?></h3>
	<?php registrate_stats_towns($form, $event); ?>
</div>
