<?php

require '../include/bestqr.inc.php';
require '../include/analytics-table.inc.php';

$folder = intval($_POST['folder'] ?? $_GET['folder']);

$stmt = $db->prepare('select * from Folders where id=:folder');
$stmt->bindValue(':folder', $folder, SQLITE3_INTEGER);
$folderinfo = $stmt->execute()->fetchArray();

if($folderinfo === false)
	redirect("/home.php?error=" . urlencode(htmlspecialchars("Folder $folder does not exist")));

if(!empty($_POST)) do {
	$name_modified        = modify_form_param($_POST['name'], $folderinfo['name']);
	$parent_modified      = modify_form_param($_POST['parent'], $folderinfo['parent']);
	$description_modified = modify_form_param($_POST['description'], $folderinfo['description']);

	// it is extremely important to check that no cycles are made. That is, a folder may not be reparented to itself or any of its children. check that we can walk from $parent_modified up to the root without encountering $folder
	$p = intval($parent_modified);
	while($p !== 0) {
		if($p === $folder) {
			$error = 'Cannot move a folder inside of itself';
			echo $error;
			break 2;
		}

		$p = $db->querySingle('select parent from Folders where id=' . intval($p));
	}

	$db->exec('begin');

	$logid = log_new($_SESSION['user'], false, 'F');

	$stmt = $db->prepare('insert into FolderLogs(id, deletion, folder_id, parent, name, description) values (:id, :deletion, :folder_id, :parent, :name, :description)');
	$stmt->bindValue(':id', $logid, SQLITE3_INTEGER);
	$stmt->bindValue(':deletion', 0, SQLITE3_INTEGER);
	$stmt->bindValue(':folder_id', $folder, SQLITE3_INTEGER);
	$stmt->bindValue(':parent', $parent_modified, SQLITE3_INTEGER);
	$stmt->bindValue(':name', $name_modified, $name_modified === null ? SQLITE3_NULL : SQLITE3_TEXT);
	$stmt->bindValue(':description', $description_modified, $description_modified === null ? SQLITE3_NULL : SQLITE3_TEXT);
	$stmt->execute();

	$stmt = $db->prepare('update Folders set name=:name, parent=:parent, description=:description where id=:id');
	$stmt->bindValue(':name', $folderinfo['name'], SQLITE3_TEXT);
	$stmt->bindValue(':parent', $folderinfo['parent'], SQLITE3_INTEGER);
	$stmt->bindValue(':description', $folderinfo['description'], SQLITE3_TEXT);
	$stmt->bindValue(':id', $folder, SQLITE3_INTEGER);
	$stmt->execute();

	$db->exec('commit');
} while(0);

// walk up the folder hierarchy to build the whole path
$folder_path = '';
$current_folder = $folder;
while ($current_folder) {
	// get current folder name and parent
	$stmt = $db->prepare('select * from Folders where id=:id');
	$stmt->bindValue(':id', $current_folder, SQLITE3_INTEGER);
	$parentinfo = $stmt->execute()->fetchArray();
	if (!$parentinfo) {
		break;
	}
	$folder_path = $parentinfo['name'] . '/' . $folder_path;
	$current_folder = $parentinfo['parent'];
}
$folder_path = trim($folder_path, '/');

// ANALYTICS

// recursively get all codes of this folder and its subfolders
$codes_in_folder = [];
$has_any_contents = false;

function dfs($folder) {
	global $db;
	global $codes_in_folder;
	global $has_any_contents;

	// total scans of direct descendants
	$stmt = $db->prepare('select code from Codes join Folders on Codes.folder=Folders.id where folder=:folder');
	$stmt->bindValue(':folder', $folder, SQLITE3_INTEGER);
	$codes = $stmt->execute();

	while($code = $codes->fetchArray()) {
		$has_any_contents = true;
		$codes_in_folder[] = $code[0];
	}

	// get all subfolders
	$stmt = $db->prepare('select id from Folders where parent=:folder');
	$stmt->bindValue(':folder', $folder, SQLITE3_INTEGER);
	$subfolders = $stmt->execute();

	while($subfolder = $subfolders->fetchArray()) {
		$has_any_contents = true;
		dfs($subfolder[0]);
	}
}

dfs($folder);

$total_scans = 0;
$unique_scans = 0;

$days = [
   (new DateTime())->format('Y-m-d') => [],
];

foreach ($codes_in_folder as $i => $code) {
	$stmt = $db->prepare('select day, hour, total_scans, unique_scans from ScansGraphByCode where code=:code');
	$stmt->bindValue(':code', $code, SQLITE3_TEXT);
	$hourly_analytics = $stmt->execute();
	while ($hourinfo = $hourly_analytics->fetchArray()) {
		$day = $hourinfo['day'];
		$hour = $hourinfo['hour'];

		if (!array_key_exists($day, $days)) {
			$days[$day] = [];
		}
		if (!array_key_exists($hour, $days[$day])) {
			$days[$day][$hour] = ['total_scans' => 0, 'unique_scans' => 0];
		}
		$days[$day][$hour]['total_scans'] += $hourinfo['total_scans'];
		$days[$day][$hour]['unique_scans'] += $hourinfo['unique_scans'];

		$total_scans += $hourinfo['total_scans'];
		$unique_scans += $hourinfo['unique_scans'];
	}
}

ksort($days); // sort by date, ascending

head($folder_path, [
	"<link rel='stylesheet' href='/static/folder.css'>",
	"<link rel='stylesheet' href='/static/analytics.css'>",
	"<script src='https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js' integrity='sha512-ZDSPMa/JM1D+7kdg2x3BsruQ6T/JpJo3jWDWkCZsP+5yVyp1KfESqLI+7RqB5k24F7p2cV7i2YHh/890y6P6Sw==' crossorigin='anonymous' referrerpolicy='no-referrer' defer></script>",
]);

?>

<h1><?= htmlspecialchars($folder_path) ?></h1>

<section id='info'>
	Created: <?= htmlspecialchars($folderinfo['creation_date']) ?><br/>
	Total codes within: <?= count($codes_in_folder) ?><br/>

	<form method="POST">
		<label for="name-edit-field" class="form-label">Name:</label>
		<input id="name-edit-field" type="text" name="name" value="<?= htmlspecialchars($folderinfo['name']) ?>" class="form-control" required>
		
		<label for="folder-select" class="form-label">Parent folder:</label>
		<select class="form-select form-control col" name="parent" id="folder-select" required>
			<?php folderSelectOptions($folder_path); ?>
		</select>
		
		<label for="description" class="form-label">Description:</label>
		<textarea id="description" name="description" class=" form-control col"><?= htmlspecialchars($folderinfo['description']) ?></textarea>

		<input type="hidden" name="folder" value="<?= $folder ?>">

		<button class='btn btn-outline-primary'>Apply changes</button>
		<button id='folder-delete' class='btn btn-outline-danger' <?= $has_any_contents ? 'disabled' : '' ?>>Delete folder</button>
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
