<?php

session_start();

if(isset($_SESSION['user'])) {
	header("Location: /home.php");
	die("You are already logged in!");
}

if(!empty($_POST)) do {
	if(!isset($_POST['email']) || !isset($_POST['password']) || !is_string($_POST['email']) || !is_string($_POST['password'])) {
		$error = "Incomplete login request";
		break;
	}

	$db = new SQlite3("../best-qr.db", SQLITE3_OPEN_READONLY);

	$stmt = $db->prepare('select email, pwhash from Users where email=:email');
	$stmt->bindValue(':email', $_POST['email'], SQLITE3_TEXT);
	$userdata = $stmt->execute()->fetchArray();

	if($userdata == false || !password_verify($_POST['password'], $userdata['pwhash'])) {
		$error = "Incorrect user or password";
		break;
	}

	session_start();
	$_SESSION['user'] = $_POST['email'];

	header("Location: /home.php");
} while(0);

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<title>BEST QR management :)</title>

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
		<link rel="stylesheet" href="/static/login.css" />
	</head>
	<body>
		<main>
			<img id="logo" src="/static//logo.png" alt="" />
			<h1>Log in to BEST QR</h1>
			<?php if(isset($error)) { ?>
				<div class="alert alert-danger" role="alert">Error: <?=htmlspecialchars($error)?></div>
			<?php } ?>
			<form method="POST">
				<div class="mb-3">
					<label for="email" class="form-label">Email:</label>
					<input type="email" id="email" name="email" required class="form-control" placeholder="john.doe@best-eu.org" />
				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Password:</label>
					<input type="password" id="password" name="password" required class="form-control" placeholder="Password">
				</div>
				<button type="submit" class="btn btn-primary">Log in!</button>
			</form>
			<div class="mt-3"><i>If you don't have an account or have lost access to yours, ask your IT department to create one for you or to reset your password.</i></div>
		</main>
	</body>
</html>
