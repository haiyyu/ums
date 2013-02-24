<?php
	session_start();

	require_once('proc.php');
	require_once('classes/mysql.php');
	require_once('classes/user.php');
	require_once('classes/login.php');
	require_once('classes/page.php');
	require_once('classes/navigation.php');
	
	if (!Login::isLoggedIn() || !Login::getCurrentUser()->getIsAdmin())
		die('You are not permitted to view this part of the website.');
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Administration</title>
		<meta http-equiv="content-type" value="text/html; charset=ISO-8859-1" />
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<header>
			<h1>Willkommen auf der Administration</h1>
		</header>
		<nav>
			<ul id='navigationList'>
				<li><a href="?page=home">Home</a></li>
				<li><a href="?page=users">Users</a></li>
				<li><a href="?page=pages">Seitenzuweisungen</a></li>
				<li><a href="?page=navigationItems">Navigationselemente</a></li>
				<li><a href="?page=userGroups">Benutzergruppen</a></li>
				<li><a href="?page=permissions">Berechtigungen</a></li>
			</ul>
		</nav>
		<article>
			<?php
				$p = isset($_GET['page']) ? $_GET['page'] : 'home';
				$l = array(
					'home' => 'home.php',
					'users' => 'users.php',
					'pages' => 'pages.php',
					'userGroups' => 'userGroups.php',
					'userAssociations' => 'userAssociations.php',
					'navigationItems' => 'navigationItems.php',
					'permissions' => 'permissions.php');
			
				include "pages/admin/$l[$p]";
			?>
		</article>
		<footer>
			<p>
				<?php //MySQL::displayQueryTrace(); ?>
			</p>
		</footer>
	</body>
</html>