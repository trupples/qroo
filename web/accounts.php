<?php

require '../include/bestqr.inc.php';

$q = $db->prepare("select has_admin_permission from Users where email=:email");
$q->bindValue(":email", $_SESSION['user'], SQLITE3_TEXT);
$has_admin_permission = $q->execute()->fetchArray()['has_admin_permission'];

$users = $db->query("select email from Users");

head('Accounts'); ?>

<h1>Account management</h1>

<?php if(!$has_admin_permission) { ?>
You do not have admin permission!
<?php foot(); die(); } ?>

<section>
<h2>Create account</h2>

<form method="POST">
	<label for="email-field" class="form-label">Email:</label>
	<input id="email" type="text" name="email" class="form-control" placeholder="john.doe@best-eu.org" required>
	<button class='btn btn-primary'>Create new account!</button>
</form>
</section>

<section>
<h2>Account list</h2>
<table>
	<thead>
		<tr>
			<th>Email</th><th>Actions</th>
		</tr>
	</thead>
	<tbody>
<?php

	while($user = $users->fetchArray()) {
		echo '<tr><td>',$user[0],'</td><td><a href="#">Reset password</a> | <a href="#">Inactivate account</a></td></tr>';
	}

?>
	</tbody>
</table>
</section>