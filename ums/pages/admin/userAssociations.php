<p>Hier können Sie Benutzer mit Gruppen assoziieren.</p>
<hr />
<?php
	require_once('classes/tableeditor.php');
	
	$editor = new TableEditor('ums_usergroups_associations');
	$editor->show();
?>
<hr />
<p><a href="?page=userAssociations&tMode=create">Assoziation erstellen</a></p>
	