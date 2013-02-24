<?php
	require_once('classes/login.php');

	if (Login::logout())
	{
?>
	<p>Sie wurden erfolgreich ausgeloggt.</p>
<?php
	}
	else
	{
?>
	<p>Sie sind nicht eingeloggt.</p>
<?php
	}
?>
<p><a href="index.php">Klicken Sie hier, um zur Hauptseite zu gelangen.</a></p>