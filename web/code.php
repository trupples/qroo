<?php

require '../include/bestqr.inc.php';
require '../include/analytics-table.inc.php';

if (!isset($_GET['code']))
	redirect("/home.php");

$code = $_GET['code'];
$stmt = $db->prepare('select * from Codes where code=:code');
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$codeinfo = $stmt->execute()->fetchArray();

if($codeinfo === false)
	redirect("/home.php?error=" . urlencode(htmlspecialchars("Code $code does not exist")));

if(!empty($_POST)) {
	$redirect_url_modified	= modify_form_param($_POST['redirect'], $codeinfo['redirect_url']);
	$medium_modified			= modify_form_param($_POST['medium'], $codeinfo['medium']);
	$folder_modified			= modify_form_param($_POST['parent'], $codeinfo['folder']);
	$description_modified   = modify_form_param($_POST['description'], $codeinfo['description']);

	$db->exec('begin');

	$logid = log_new($_SESSION['user'], false, 'C');

	$stmt = $db->prepare('insert into CodeLogs(id, code, folder, medium, redirect_url, description) values (:id, :code, :folder, :medium, :redirect_url, :description)');
	$stmt->bindValue(':id', $logid, SQLITE3_INTEGER);
	$stmt->bindValue(':code', $code, SQLITE3_TEXT);
	$stmt->bindValue(':folder', $folder_modified, $folder_modified === null ? SQLITE3_NULL : SQLITE3_INTEGER);
	$stmt->bindValue(':medium', $medium_modified, $medium_modified === null ? SQLITE3_NULL : SQLITE3_TEXT);
	$stmt->bindValue(':redirect_url', $redirect_url_modified, $redirect_url_modified === null ? SQLITE3_NULL : SQLITE3_TEXT);
	$stmt->bindValue(':description', $description_modified, $description_modified === null ? SQLITE3_NULL : SQLITE3_TEXT);
	$stmt->execute();

	$stmt = $db->prepare('update Codes set redirect_url=:redirect_url, medium=:medium, folder=:folder, description=:description where code=:code');
	$stmt->bindValue(':redirect_url', $codeinfo['redirect_url'], SQLITE3_TEXT);
	$stmt->bindValue(':medium', $codeinfo['medium'], SQLITE3_TEXT);
	$stmt->bindValue(':folder', $codeinfo['folder'], SQLITE3_INTEGER);
	$stmt->bindValue(':description', $codeinfo['description'], SQLITE3_TEXT);
	$stmt->bindValue(':code', $code, SQLITE3_TEXT);
	$stmt->execute();

	$db->exec('commit');
}

// walk up the folder hierarchy to build the whole path
$folder_path = '';
$current_folder = $codeinfo['folder'];
while ($current_folder) {
	// get current folder name and parent
	$stmt = $db->prepare('select name, parent from Folders where id=:id');
	$stmt->bindValue(':id', $current_folder, SQLITE3_INTEGER);
	$folderinfo = $stmt->execute()->fetchArray();
	if (!$folderinfo) {
		break;
	}
	$folder_path = $folderinfo['name'] . '/' . $folder_path;
	$current_folder = $folderinfo['parent'];
}
$folder_path = trim($folder_path, '/');

// ANALYTICS

// get total scans, also max for the color scale
$stmt = $db->prepare('select coalesce(sum(total_scans), 0), coalesce(sum(unique_scans), 0), coalesce(max(unique_scans), 0) from ScansGraphByCode where code=:code');
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$total_analytics = $stmt->execute()->fetchArray();

// color scale has 8 levels; if there are <7 total scans, have the scale represent 0..7, otherwise 0..max
$max_scans = max(7, $total_analytics[2]);

// get scans by day and hour
$stmt = $db->prepare('select day, hour, total_scans, unique_scans from ScansGraphByCode where code=:code');
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$hourly_analytics = $stmt->execute();

// days['YYYY-MM-DD']['HH'] = array('total_scans'=>..., 'unique_scans'=>...)
// also include today and creation date, even if there are no scans then
$days = [
   $codeinfo['creation_date'] => [],
   (new DateTime())->format('Y-m-d') => [],
];

while ($hourinfo = $hourly_analytics->fetchArray()) {
   $day = $hourinfo['day'];
   $hour = $hourinfo['hour'];
   $total_scans = $hourinfo['total_scans'];
   $unique_scans = $hourinfo['unique_scans'];

   if (!array_key_exists($day, $days)) {
	   $days[$day] = [];
   }
   $days[$day][$hour] = ['total_scans' => $total_scans, 'unique_scans' => $unique_scans];
}

ksort($days); // sort by date, ascending

head($codeinfo['medium'] . ' for ' . $folder_path, [
	"<link rel='stylesheet' href='/static/code.css'>",
	"<link rel='stylesheet' href='/static/analytics.css'>",
	"<script src='https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js' integrity='sha512-ZDSPMa/JM1D+7kdg2x3BsruQ6T/JpJo3jWDWkCZsP+5yVyp1KfESqLI+7RqB5k24F7p2cV7i2YHh/890y6P6Sw==' crossorigin='anonymous' referrerpolicy='no-referrer' defer></script>",
	"<script src='/static/initQR.js' defer></script>"
]);

?>

<h1><i><?= htmlspecialchars($codeinfo['medium']) ?></i> for <i><?= htmlspecialchars($folder_path) ?></i></h1>

<section id='info'>
	Created: <samp><?= htmlspecialchars($codeinfo['creation_date']) ?></samp><br/>

	Scanned URL: <samp>https://qr.bestcj.ro/?<?= htmlspecialchars($codeinfo['code']) ?></samp><br/>

	<section id='qr'>
		<div id='generated-qr'>
			<div class='notloaded'>Please enable Javascript for the QR code to be generated.</div>
		</div>
		<a id='generated-qr-download' href='#' download='<?= urlencode($codeinfo['code']) ?>.svg'>Download svg</a>
	</section>

	<form method="POST">
		<label for="redirect-edit-field" class="form-label">Redirect URL:</label>
		<input id="redirect-edit-field" type="text" name="redirect" value="<?= htmlspecialchars($codeinfo['redirect_url']) ?>" class="form-control" required>

		<label for="medium-select" class="form-label">Medium:</label>
		<select class="form-select form-control col" name="medium" id="medium-select" required>
			<?php mediumSelectOptions($codeinfo['medium']); ?>
		</select>

		<label for="folder-select" class="form-label">Parent folder:</label>
		<select class="form-select form-control col" name="parent" id="folder-select" required>
			<?php folderSelectOptions($folder_path . '/' . $code); ?>
		</select>

		<label for="description" class="form-label">Description:</label>
		<textarea id="description" name="description" class=" form-control col"><?= htmlspecialchars($codeinfo['description']) ?></textarea>

		<button class='btn btn-outline-primary'>Apply changes</button>
	</form>
</section>

<section id='analytics'>
	<h2>Analytics</h2>

	Total scans: <?= $total_analytics[0] ?><br/>
	Unique scans: <?= $total_analytics[1] ?>

	<?php analyticsTable($days); ?>
</section>

<?php
foot();
?>
