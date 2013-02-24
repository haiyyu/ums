<p>Hier können Sie die Benutzergruppen Ihrer Webseite verwalten.</p>
<hr />
<?php
	require_once('classes/tableeditor.php');
	require_once('classes/usergroup.php');
	require_once('classes/synchronizable.php');
	require_once('classes/usergroupassociation.php');
	require_once('classes/listeners/tableeditorlistener.php');
	
	class DeleteListener implements TableEditorListener
	{
		// not required
		public function onDisplayList() {}
		public function onEdit($id) {}
		public function onCreate() {}
		public function onSaved($id) {}
		public function onSwapped($id1, $id2) {}
		
		// required
		public function onDeleted($id)
		{
			MySQL::executeQuery('DELETE FROM `' . UserGroupAssociation::$table . '` WHERE `group`=\'' . $id . '\'');
		}
	}
	
	$editor = new TableEditor('ums_usergroups');
	$editor->setSortable(true);
	$editor->addListener(new DeleteListener());
	$editor->show();
?>
<hr />
<p>
	<a href="?page=userGroups&tMode=create">Benutzergruppe erstellen</a><br />
	<a href="?page=userAssociations">Benutzerassoziationen verwalten</a>
</p>