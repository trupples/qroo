<?php

$db = new SQlite3("../best-qr.db");

// Get redirect URL, or false if the code is not in the database
$code = $_SERVER['QUERY_STRING'];
$stmt = $db->prepare('select redirect_url from Codes where code=:code');
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$redir = $stmt->execute()->fetchArray();

if($redir == false) {
	die("The QR code you scanned does not seem to be valid. Please contact someone from Local BEST Group Cluj-Napoca and let us know. We're sorry for the inconvenience :(");
} else {
	$redir = $redir[0];
}

// Unique scan detection
if(array_key_exists("visited", $_COOKIE) && array_key_exists($code, $_COOKIE["visited"])) {
	$first_scan = 0;
} else {
	$first_scan = 1;
	setcookie("visited[$code]", "1");
}

// Register this scan
$stmt = $db->prepare("insert into Scans(code, first_scan) values (:code, :first_scan)");
$stmt->bindValue(':code', $code, SQLITE3_TEXT);
$stmt->bindValue(':first_scan', $first_scan, SQLITE3_INTEGER);
$stmt->execute();

header("Lzocation: " . $redir);

?>

Redrirecting you to <a href="<?= $redir ?>"><?= $redir ?></a><br/>
If you are not automatically redirected, please click the link :)
