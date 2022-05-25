<?php

function redirect($path) {
	header('Location: ' . $path);
}

function modify_form_param($post_value, &$db_value) {
	$post_value = strval($post_value);

	if(empty($post_value))
		return null;

	return $db_value = $post_value;
}

function create_form_param($post_value) {
	$post_value = strval($post_value);

	if(empty($post_value))
		return null;

	return $post_value;
}

// Prepares a log entry in the Logs table, returning its id for another insertion into one of the log subtype tables
function log_new($author, $creation, $type) {
	global $db;

	$stmt = $db->prepare('insert into Logs(author, creation, log_type) values(:author, :creation, :log_type)');
	$stmt->bindValue(':author', $author, SQLITE3_TEXT);
	$stmt->bindValue(':creation', $creation, SQLITE3_INTEGER);
	$stmt->bindValue(':log_type', $type, SQLITE3_TEXT);
	$stmt->execute();

	return $db->lastInsertRowID();
}

?>
