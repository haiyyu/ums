<?php
	session_start();

	require_once('classes/displayable/tableeditor.php');
	require_once('classes/login.php');
	require_once('classes/mysql.php');
	
	echo Login::getCurrentUser()->getUsername();
	
	$editor = new TableEditor('ums_usergroups', -1, true);
	$editor->display();
	
	//print_r(MySQL::getQueryTrace());
?>