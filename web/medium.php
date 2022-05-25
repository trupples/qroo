<?php

require '../include/bestqr.inc.php';
require '../include/analytics-table.inc.php';

$medium = $_GET['medium'];

if ($medium === null)
	redirect("/home.php");

$stmt = $db->prepare('select * from Media where name=:name');
$stmt->bindValue(':name', $medium, SQLITE3_TEXT);
$info = $stmt->execute()->fetchArray();

if($info === false)
	redirect("/home.php?error=" . urlencode(htmlspecialchars("Medium $medium does not exist")));

// ANALYTICS

$total_scans = 0;
$unique_scans = 0;

$days = [
   (new DateTime())->format('Y-m-d') => [],
];

$stmt = $db->prepare('select day, hour, total_scans, unique_scans from ScansGraphByMedium where medium=:medium');
$stmt->bindValue(':medium', $medium, SQLITE3_TEXT);
$hourly_analytics = $stmt->execute();
while ($hourinfo = $hourly_analytics->fetchArray()) {
	$day = $hourinfo['day'];
	$hour = $hourinfo['hour'];

	if (!array_key_exists($day, $days)) {
		$days[$day] = [];
	}
	$days[$day][$hour] = ['total_scans' => $hourinfo['total_scans'], 'unique_scans' => $hourinfo['unique_scans']];

	$total_scans += $hourinfo['total_scans'];
	$unique_scans += $hourinfo['unique_scans'];
}

ksort($days); // sort by date, ascending

head($medium, [
	"<link rel='stylesheet' href='/static/folder.css'>",
	"<link rel='stylesheet' href='/static/analytics.css'>",
	"<script src='https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js' integrity='sha512-ZDSPMa/JM1D+7kdg2x3BsruQ6T/JpJo3jWDWkCZsP+5yVyp1KfESqLI+7RqB5k24F7p2cV7i2YHh/890y6P6Sw==' crossorigin='anonymous' referrerpolicy='no-referrer' defer></script>",
]);

?>

<h1><?= htmlspecialchars($medium) ?></h1>

<section id='info'>
	<form action='#' method="POST">
		<label for="name-edit-field" class="form-label">Name:</label>
		<input id="name-edit-field" type="text" name="name" value="<?= htmlspecialchars($info['name']) ?>" class="form-control" required>
		
		<label for="description" class="form-label">Description:</label>
		<textarea id="description" name="description" class=" form-control col"><?= htmlspecialchars($info['description']) ?></textarea>
	</form>
</section>

<section>
	<h2>Analytics</h2>

	Total scans: <?= $total_scans ?><br/>
	Unique scans: <?= $unique_scans ?>

	<?php analyticsTable($days); ?>
</section>

<?php
foot();
?>
