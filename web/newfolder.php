<?php

require '../include/bestqr.inc.php';

$parent = intval($_POST['parent'] ?? $_GET['parent']);

if(!empty($_POST)) do {
	if(!is_string($_POST['name']) || !is_int($parent)) {
		$error = 'Need to specify name and parent!';
		break;
	}

	$name = strval($_POST['name']);
	$description = strval($_POST['description']);

	$db->exec('begin');

	$stmt = $db->prepare('insert into Folders(name, parent, description) values (:name, :parent, :description)');
	$stmt->bindValue(':name', $name, SQLITE3_TEXT);
	$stmt->bindValue(':parent', $parent, SQLITE3_INTEGER);
	$stmt->bindValue(':description', $description, SQLITE3_TEXT);
	if($stmt->execute()) {
		$folderid = $db->lastInsertRowID();
	} else {
		$error = 'A database error occured';
		$db->exec('rollback');
		break;
	}

	$logid = log_new($_SESSION['user'], true, 'F');

	$stmt = $db->prepare('insert into FolderLogs(id, deletion, folder_id, parent, name, description) values (:id, :deletion, :folder_id, :parent, :name, :description)');
	$stmt->bindValue(':id', $logid, SQLITE3_INTEGER);
	$stmt->bindValue(':deletion', 0, SQLITE3_INTEGER);
	$stmt->bindValue(':folder_id', $folderid, SQLITE3_INTEGER);
	$stmt->bindValue(':parent', $parent, SQLITE3_INTEGER);
	$stmt->bindValue(':name', $name, SQLITE3_TEXT);
	$stmt->bindValue(':description', $description, SQLITE3_TEXT);
	$stmt->execute();

	$db->exec('commit');

	redirect('/folder.php?folder=' . $folderid);
} while(0);

head('New folder');

?>
<?php if(isset($error)) { ?>
<div class='alert alert-danger'><?= $error ?></div>
<?php } ?>

<h1>Create a new folder</h1>

<form method="POST">
	<label for="name-field" class="form-label">Name:</label>
	<input id="name-field" type="text" name="name" class="form-control" placeholder="EventName99" required>

	<label for="folder-select" class="form-label">Parent folder:</label>
	<select class="form-select form-control col" name="parent" id="folder-select" required disabled>
		<?php folderSelectOptions('\\', $parent); ?>
	</select>

	<label for="description" class="form-label">Description:</label>
	<textarea id="description" name="description" class=" form-control col"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

	<button class='btn btn-primary'>Create new code!</button>
</form>

<?php foot(); ?>
