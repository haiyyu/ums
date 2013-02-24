<?php
	session_start();

	require_once('classes/mysql.php');
	require_once('classes/user.php');
	require_once('classes/login.php');
	
	if (isset($_POST['submitLogin']) && !Login::isLoggedIn())
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		$user = User::fromDatabaseByUsername($username);
		if ($user != null)
		{
			if (Login::loginUser($username, $password))
				echo 'Eingeloggt als ' . $user->getFirstName() . ' ' . $user->getLastName() . '.';
			else
				echo 'Passwort falsch.';
		}
		else
			echo 'Benutzer "' . $username . '" existiert nicht!';
			
		echo '<br /><br />';
	}
	elseif (isset($_GET['logout']))
	{
		unset($_SESSION['id']);
	}
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
		<?php 
			$user = Login::getCurrentUser();
			if ($user == null) 
			{
		?>
		
		<form method="POST" action="index.php">
			<table>
				<tr>
					<td>Benutzername</td>
					<td><input type="text" name="username" maxlength="32" /></td>
				</tr>
				<tr>
					<td>Passwort</td>
					<td><input type="password" name="password" /></td>
				<tr>
					<td colspan="2"><input type="submit" value="Überprüfen" id="submitButton" name="submitLogin" /></td>
				</tr>
			</table>
		</form>
		
		<?php
			}
			else
			{
		?>
		
		Sie sind im Moment als <?php echo $user->getFirstName() . ' ' . $user->getLastName(); ?> eingeloggt.<br />
		<a href="index.php?logout">Klicken Sie hier, um sich auszuloggen.</a>
		
		<?php
			}
		?>
		
	</body>
</html>