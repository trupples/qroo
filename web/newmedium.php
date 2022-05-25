<?php

require '../include/bestqr.inc.php';

if(!empty($_POST)) do {
	if(!isset($_POST['name']) || !is_string($_POST['name']) || trim($_POST['name']) == '') {
		$error = 'Name must be specified!';
		break;
	}

	$name = trim($_POST['name']);
	$description = $_POST['description'];

	$db->exec('begin');

	$stmt = $db->prepare('insert into Media(name, description) values (:name, :description)');
	$stmt->bindValue(':name', $name, SQLITE3_TEXT);
	$stmt->bindValue(':description', $description, SQLITE3_TEXT);
	if(!$stmt->execute()) {
		$error = 'A database error occured';
		$db->exec('rollback');
		break;
	}

	$logid = log_new($_SESSION['user'], true, 'M');

	$stmt = $db->prepare('insert into MediumLogs(id, name, description) values (:id, :name, :description)');
	$stmt->bindValue(':id', $logid, SQLITE3_INTEGER);
	$stmt->bindValue(':name', $name, SQLITE3_TEXT);
	$stmt->bindValue(':description', $description, SQLITE3_TEXT);
	$stmt->execute();

	$db->exec('commit');

	redirect('/medium.php?medium=' . urlencode($name));
} while(0);

head('New medium');

?>

<?php if(isset($error)) { ?>
<div class='alert alert-danger'><?= $error ?></div>
<?php } ?>

<h1>Create a new medium</h1>

<form method="POST">
	<label for="name-field" class="form-label">Name:</label>
	<input name="name" type="text" class="form-control" id="name-field" value="<?= htmlspecialchars($_POST['name']) ?>">

	<label for="description" class="form-label">Description:</label>
	<textarea id="description" name="description" class=" form-control col"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

	<button class='btn btn-primary'>Create new medium!</button>
</form>

<?php foot(); ?>
