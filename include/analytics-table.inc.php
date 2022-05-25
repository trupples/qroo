<?php

function analyticsTable($days) {

	$max_scans = 7;
	foreach($days as $day => $hours) {
		foreach($hours as $hour => $stats) {
			$max_scans = max($max_scans, $stats['total_scans']);
		}
	}

$hour_strings = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
	?>
<div id='analytics-viz'>
<table>
<thead>
	<tr><th></th><th colspan=24>Total scans</th><th></th><th colspan=24>Unique scans</th></tr>
	<th></th>
	<th>00</th><th></th><th></th><th>03</th><th></th><th></th><th>06</th><th></th><th></th><th>09</th><th></th><th></th><th>12</th><th></th><th></th><th>15</th><th></th><th></th><th>18</th><th></th><th></th><th>21</th><th></th><th></th>
	<th class='analytics-middle-space'></th>
	<th>00</th><th></th><th></th><th>03</th><th></th><th></th><th>06</th><th></th><th></th><th>09</th><th></th><th></th><th>12</th><th></th><th></th><th>15</th><th></th><th></th><th>18</th><th></th><th></th><th>21</th><th></th><th></th>
	<th class='analytics-middle-space'></th>
</thead>
<tbody>
<?php
$expected_next_day = (new DateTime(array_key_first($days)))->format('Y-m-d');

foreach ($days as $day => $hours) {
	if ($day !== $expected_next_day) {
		echo '<tr><td>&vellip;</td></tr>';
	}

	$expected_next_day = new DateTime($day);
	$expected_next_day = $expected_next_day->add(date_interval_create_from_date_string('1 day'))->format('Y-m-d');

	echo "<tr><td>$day</td>";

	for ($i = 0; $i < 24; $i++) {
		if (array_key_exists($hour_strings[$i], $hours)) {
			$x = $hours[$hour_strings[$i]]['total_scans'];
			$scale_color_index = ceil(($x * 7) / $max_scans);
			echo "<td><div class='hour-square scale-green-$scale_color_index'><span class='tooltip'>$day&nbsp;$hour_strings[$i]:00 $x&nbsp;total&nbsp;scan" . ($x != 1 ? 's' : '') . '</span></div></td>';
		} else {
			echo "<td><div class='hour-square scale-green-0'></div></td>";
		}
	}

	echo '<td></td>'; // spacing column

	for ($i = 0; $i < 24; $i++) {
		if (array_key_exists($hour_strings[$i], $hours)) {
			$x = $hours[$hour_strings[$i]]['unique_scans'];
			$scale_color_index = ceil(($x * 7) / $max_scans);
			echo "<td><div class='hour-square scale-blue-$scale_color_index'><span class='tooltip'>$day&nbsp;$hour_strings[$i]:00 $x&nbsp;unique&nbsp;scan" . ($x != 1 ? 's' : '') . '</span></div></td>';
		} else {
			echo "<td><div class='hour-square scale-blue-0'></div></td>";
		}
	}

	echo "</tr>\n";
} ?>
</tbody>
</table>
</div>

<?php
}
?>
