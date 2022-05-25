<?php

$head_sent = false;
$staticVersion = "v1";

function head($title = '', $extras = []) {
    global $head_sent;
    global $staticVersion;

    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>' . htmlspecialchars($title) . ' : BEST-QR</title>
    <link rel="icon" href="/static/img/favicon.png" type="image/png" />

	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<title>BEST QR management :)</title>

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<link rel="stylesheet" href="/static/style.css" />
    <link href="/static/style.css?v=' . $staticVersion . '" rel="stylesheet">
	<script src="/static/rememberCheckboxes.js" defer></script>';

    foreach ($extras as $i => $extra) {
    	echo $extra;
    }

    echo '</head><body>';

    // Nav
    echo '<aside>
		<img id="logo" src="/static/logo.png" alt="" />
		<nav class="tree"><li><a href="home.php">Home</a></li>';
	renderTreeFolder();
	renderMediaTree();
	echo '
			<li><a href="logout.php" class="icon-logout">Log out</a></li>
			<li><a href="accounts.php">Account management</a></li>
			<li><a href="logs.php">Logs</a></li>
		</nav>
	</aside>';

	echo '<div id="container"><main>';

    $head_sent = true;
}

function foot () {
    echo '</main>';
    echo '<footer>';
    //echo 'Made with &#x1F9E1; by <a href="https://trupples.github.io">trupples</a> for my most dear <a href="https://bestcj.ro">Local BEST Group Cluj-Napoca</a><br/>';
    echo 'Spatele drept!';
    echo '</footer>';
    echo '</div></body></html>';
}

$pathList = array(0 => '/');

function renderTreeFolder(int $folder_id = 0, string $base_path = '')
{
	global $pathList;
	global $db;
	if ($folder_id === 0) {
		$folder_name = 'QR Codes';
	} elseif(is_numeric($folder_id)) {
		$folder_name = $db->querySingle("select name from Folders where id=$folder_id");
		$pathList[$folder_id] = $base_path . '/' . $folder_name . '/';
	} else {
		die("renderTreeFolder($folder_id) - bad folder id");
	}

	echo "<li class='tree-folder icon-folder' id='folder$folder_id'";
	if(isset($_GET['folder']) && $folder_id == $_GET['folder']) echo 'aria-current="location"';
	echo "><input type='checkbox' id='toggle-folder$folder_id' onchange='checkboxUpdate(this)' />
		<label for='toggle-folder$folder_id'>";

	if($folder_id !== 0) echo "<a href='/folder.php?folder=$folder_id'>";
	echo htmlspecialchars($folder_name);
	if($folder_id !== 0) echo "</a>";
	echo "</label>";

	echo "<ul>
		<li class='tree-add'><a href='/newcode.php?folder=$folder_id' aria-role='button'>New code</a></li>
		<li class='tree-add-folder'><a href='/newfolder.php?parent=$folder_id' aria-role='button'>New folder</a></li>";

	$children = $db->query("select id from Folders where parent=$folder_id order by creation_date desc");
	while ($child_id = $children->fetchArray()) {
		if($folder_id === 0)
			renderTreeFolder($child_id[0]);
		else
			renderTreeFolder($child_id[0], $base_path . '/' . $folder_name);
	}

	$children = $db->query("select code, medium, redirect_url from Codes where folder=$folder_id order by creation_date desc");
	while ($child = $children->fetchArray()) {
		echo "<li class='tree-item icon-code' id='item-" . htmlspecialchars($child['code']) . "'";
		if(isset($_GET['code']) && $child['code'] === $_GET['code']) echo "aria-current='location'";
		echo "><a href='/code.php?code=" . urlencode($child['code']) . "'>" . htmlspecialchars($child['medium']) . " (" . htmlspecialchars($child['code']) . ")</a></li>";
	}

	echo "</ul></li>";
}

function renderMediaTree($current = '') {
	global $db;
	global $mediaList;

	$mediaList = [];

	echo '<li class="tree-folder icon-folder" id="media">
	<input type="checkbox" id="toggle-media" onchange="checkboxUpdate(this)" />
	<label for="toggle-media">Media</label>
	<ul><li class="tree-add"><a href="newmedium.php">New medium</a></li>';

	$media = $db->query("select name from Media");
	while($medium = $media->fetchArray()) {
		$mediaList[] = $medium[0];
		echo "<li class='tree-loc'";
		if(isset($_GET['medium']) && $medium[0] == $_GET['medium']) echo 'aria-current="location"';
		echo "><a href='medium.php?medium=" . urlencode($medium[0]) . "'>". htmlspecialchars($medium[0]) . "</a></li>";
	}

	echo "</ul></li>";
}

?>
