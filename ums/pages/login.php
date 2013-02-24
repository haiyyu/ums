<?php
	require_once('classes/login.php');

	$user = Login::getCurrentUser();
	if (isset($_POST['submitLogin']) && is_null($user))
	{
		if (Login::loginUser($_POST['username'], $_POST['password']))
			echo 'Sie wurden erfolgreich angemeldet.';
		else
			echo 'Anmeldung leider nicht erfolgreich. Stellen Sie sicher, dass Sie alle Daten richtig eingegeben haben.';
	}
?>

<p>Bitte melden Sie sich mit Ihren Benutzerdaten an.
Sollten Sie keine besitzen, bitten wir Sie, sich mit Ihren Daten mittels des Registrierungsformulars ein Konto zu erstellen.
Sie werden dann umgehend von einem Administrator freigeschaltet.</p>

<?php
	
	if ($user == null) 
	{
?>

<form method="POST" action="index.php?page=login">
	<table id="loginTable">
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

<p>Sie sind im Moment als <?php echo $user->getFirstName() . ' ' . $user->getLastName(); ?> eingeloggt.</p>
<p><a href="index.php?logout">Klicken Sie hier, um sich auszuloggen.</a></p>

<?php
	}
?>
