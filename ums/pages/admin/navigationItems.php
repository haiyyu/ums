<p>Hier k�nnen Sie die Navigation Ihrer Seite verwalten. Um Eintr�ge zu bewegen, verwenden Sie die Pfeile am linken Rand der Liste. Um zu l�schen, klicken Sie auf das "x".</p>
<hr />
<?php
	require_once('classes/tableeditor.php');
	require_once('classes/navigationItem.php');

	$editor = new TableEditor(NavigationItem::$table, -1, true);
	$editor->show();
	
	//if (!isset($_GET['tMode']) || $_GET['tMode'] == 'list' || $_GET['tMode'] == 'swap')
	//{
?>
<hr />
<p><a href="?page=navigationItems&tMode=create">Navigationselement manuell erstellen</a></p>
<?php
	//}
?>