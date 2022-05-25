<?php

require '../include/bestqr.inc.php';

$folder = $_POST['folder'] ?? $_GET['folder'] ?? 'null';

if(!empty($_POST)) do {
	if(!isset($_POST['code']) || $_POST['code'] === '') {
		// generate random code; repeat if it already exists in the db
		$alph = '1234567890abcdefghijklmnopqrstuvwxyz';
		do {
			$code = '';
			for($i = 0; $i < 8; $i++) $code .= $alph[rand(0, strlen($alph)-1)];
		} while($db->querySingle('select code from Codes where code="' . $code . '"'));
	} else {
		$code = $_POST['code'];
		$stmt = $db->prepare('select code from Codes where code=:code');
		$stmt->bindValue(':code', $code, SQLITE3_TEXT);
		if($stmt->execute()->fetchArray()) {
			$error = 'Code ' . htmlspecialchars($code) . ' <a href="/code.php?code=' . htmlspecialchars($code) . '">already exists</a>';
			break;
		}
	}

	$medium = strval($_POST['medium']);
	$redirect_url = strval($_POST['redirect_url']);
	$description = create_form_param($_POST['description']);

	$db->exec('begin');

	$logid = log_new($_SESSION['user'], true, 'C');

	$stmt = $db->prepare('insert into CodeLogs(id, code, folder, medium, redirect_url, description) values (:id, :code, :folder, :medium, :redirect_url, :description)');
	$stmt->bindValue(':id', $logid, SQLITE3_INTEGER);
	$stmt->bindValue(':code', $code, SQLITE3_TEXT);
	if($folder === 'null')
		$stmt->bindValue(':folder', null, SQLITE3_NULL);
	else
		$stmt->bindValue(':folder', $folder, SQLITE3_INTEGER);
	$stmt->bindValue(':medium', $medium, SQLITE3_TEXT);
	$stmt->bindValue(':redirect_url', $redirect_url, SQLITE3_TEXT);
	$stmt->bindValue(':description', $description, SQLITE3_TEXT);
	$stmt->execute();

	$stmt = $db->prepare('insert into Codes(code, folder, medium, redirect_url, description) values (:code, :folder, :medium, :redirect_url, :description)');
	$stmt->bindValue(':code', $code, SQLITE3_TEXT);
	if($folder === 'null')
		$stmt->bindValue(':folder', null, SQLITE3_NULL);
	else
		$stmt->bindValue(':folder', $folder, SQLITE3_INTEGER);
	$stmt->bindValue(':medium', $medium, SQLITE3_TEXT);
	$stmt->bindValue(':redirect_url', $redirect_url, SQLITE3_TEXT);
	$stmt->bindValue(':description', $description, SQLITE3_TEXT);
	if(!$stmt->execute()) {
		$error = 'A database error occured';
		$db->exec('rollback');
		break;
	}

	$db->exec('commit');

	redirect('/code.php?code=' . urlencode($code));
} while(0);

head('New code');

?>

<?php if(isset($error)) { ?>
<div class='alert alert-danger'><?= $error ?></div>
<?php } ?>

<h1>Create a new QR code</h1>

<form method="POST">
	<label for="code-field" class="form-label">Code:</label>
	<div class="input-group">
	  <span class="input-group-text">https://qr.bestcj.ro/?</span>
	  <input name="code" type="text" class="form-control" id="code-field" aria-describedby="code-desc" maxlength="8">
	  <i id="code-desc">Short (at most 8 characters) code to be part of the QR code. Leave blank for a randomly generated code. e.g. <samp>cr22cub</samp> for the CodeRun 2022 Cube</samp></i>
	</div>

	<label for="url-redirect-field" class="form-label">URL to redirect to:</label>
	<input id="url-redirect-field" type="text" name="redirect_url" class="form-control" placeholder="https://bestcj.ro/" required>

	<label for="medium-select" class="form-label">Medium:</label>
	<select class="form-select form-control col" name="medium" id="medium-select" required>
		<?php mediumSelectOptions(); ?>
	</select>

	<label for="folder-select" class="form-label">Parent folder:</label>
	<select class="form-select form-control col" name="folder" id="folder-select" required disabled>
		<?php folderSelectOptions('\\', $folder); ?>
	</select>

	<label for="description" class="form-label">Description:</label>
	<textarea id="description" name="description" class=" form-control col"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>

	<button class='btn btn-primary'>Create new code!</button>
</form>

<?php foot(); ?>
