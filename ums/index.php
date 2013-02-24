<?php
	session_start();

	require_once('proc.php');
	require_once('classes/mysql.php');
	require_once('classes/user.php');
	require_once('classes/login.php');
	require_once('classes/page.php');
	require_once('classes/synchronizable.php');
	require_once('classes/navigation.php');
	
	$currentUser = Login::getCurrentUser();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Benutzerverwaltung</title>
		<meta http-equiv="content-type" value="text/html; charset=ISO-8859-1" />
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body>
		<header>
			<h1>Willkommen auf der Vereinsseite!</h1>
		</header>
		<nav>
			<p>
				<?php
					Navigation::show();
				?>
			</p>
		</nav>
		<article>
			<?php 
				if (isset($_GET['page']))
					$link = $_GET['page'];
				else
					$link = '%empty%';
					
				$pages = Synchronizable::load('Page', Page::$table, array('name' => $link));
				if (is_null($pages))
					echo 'Die von Ihnen aufgeforderte Seite existiert nicht.';
				else
				{
					$page = $pages[0];
					$permName = 'page.view.' . $page->getName();
					$perms = Login::permitted($permName, true);
					if ($perms)
						include 'pages/' . $pages[0]->getFile();
					else
						echo 'Sie haben keine Berechtigung, diese Seite aufzurufen.';
				}
			?>
		</article>
	</body>
</html>