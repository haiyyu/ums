<p>Hier können Sie die Berechtigungen der Benutzergruppen verwalten.</p>
<hr />
<?php
	require_once('classes/tableeditor.php');
	
	$editor = new TableEditor('ums_permissions');
	$editor->show();
?>
<hr />
<p><a href="?page=permissions&tMode=create">Berechtigung erstellen</a></p>