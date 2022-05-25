<?php

require '../include/bestqr.inc.php';

$logs = $db->query('select id, author, log_time, creation, log_type from Logs order by log_time desc');

head('Logs'); ?>

<h1>Logs</h1>

<ol>
<?php
while($log = $logs->fetchArray()) {

	echo '<li>';
	echo '<span class="log-time">', $log['log_time'], '</span> ';
	echo '<span class="log-author">', $log['author'], '</span> ';
	echo '<span class="log-creation">', $log['creation'] ? 'created' : 'updated', '</span> ';

	switch ($log['log_type']) {
		case 'F':
			$folder = $db->query('select deletion, folder_id, parent, name, description from FolderLogs where id=' . $log['id'])->fetchArray();
			
			echo '<a href="/folder.php?folder=',$folder['folder_id'],'" class="log-folder-path">', $pathList[$folder['folder_id']], '</a>';
			if($folder['name'])
				echo '<div class="log-field"><span class="log-field-name">Name: </span><span class="log-field-value">',$folder['name'],'</span></div>';
			if($folder['parent'])
				echo '<div class="log-field"><span class="log-field-name">Parent: </span><span class="log-field-value"><a href="/folder.php?folder=',$folder['parent'],'">',$pathList[$folder['parent']],'</a></span></div>';
			if($folder['description'])
				echo '<div class="log-field"><span class="log-field-name">Description: </span><div class="log-field-value">',$folder['description'],'</div></div>';
			echo '<span class="log-folder-desc">', $folder['description'], '</span>';
			break;

		case 'C':
			$code = $db->query('select code, folder, medium, redirect_url, description from CodeLogs where id=' . $log['id'])->fetchArray();
			echo 'code ', $code['code'], ' in folder [', $code['folder'], '] ', $pathList[$code['folder']], ' redirecting to ', $code['redirect_url'], ' with description <b>', $code['description'], '</b>';
			break;
		case 'M':
			break;
		case 'U':
			break;
		
		default: break;
	}

	echo '</li>';
}
?>
</ol>

<?php foot(); ?>
