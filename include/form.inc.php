<?php
function folderSelectOptions($selected = '', $parent_id = -1) {
	global $pathList;

	$selected = '/' . $selected;
	$parent = implode('/', explode('/', $selected, -1)) . '/';

	foreach($pathList as $folder_id => $folder_path) {
		if(strpos($folder_path, $selected . '/') === 0) { // can't move a folder inside itself or one of its subfolders
			continue;
		}

		$myparent = $folder_path === $parent || $folder_id == $parent_id;

		echo '<option value="' . htmlspecialchars($folder_id) . '"' . ($myparent ? ' selected' : '') . '>' . htmlspecialchars($folder_path) . '</option>';
	}
}

function mediumSelectOptions($selected = '') {
	global $mediaList;
	foreach ($mediaList as $_ => $medium) {
		echo '<option value="' . htmlspecialchars($medium) . '"' . ($medium === $selected ? ' selected' : '') . '>' . htmlspecialchars($medium) . '</option>';
	}
}
?>
