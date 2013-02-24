<?php
	session_start();
	
	require_once('proc.php');
	require_once('classes/synchronizable.php');
	require_once('classes/user.php');
	require_once('classes/login.php');
	require_once('classes/debug.php');
	require_once('classes/customfield.php');
	require_once('classes/customfieldsync.php');
	require_once('classes/langvar.php');
	require_once('classes/tableeditor.php');
	require_once('classes/url.php');
	
	/*$columnResult = MySQL::executeQuery('SHOW COLUMNS FROM ums_users');
	while ($column = mysql_fetch_array($columnResult))
	{
		print_r($column);
	}
	
	echo '<br/><br/>';
	
	$vars = Synchronizable::load('LangVar', 'ums_langvars');
	print_r($vars);*/
	
	$r = MySQL::executeQuery('SHOW COLUMNS FROM `ums_users`');
	while ($c = mysql_fetch_assoc($r))
		print_r($c);
	
?>