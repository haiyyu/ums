<p>Bitte geben Sie Ihre Details ein, um mit der Registrierung fortzufahren.</p>
<?php
	require_once('config.php');
	require_once('classes/user.php');
	require_once('classes/usergroupassociation.php');
	require_once('classes/tableeditor.php');
	require_once('classes/synchronizable.php');
	require_once('classes/listeners/tableeditorlistener.php');
	
	/*class DeleteListener implements TableEditorListener
	{
		// not required
		public function onDisplayList() {}
		public function onEdit($id) {}
		public function onCreate() {}
		public function onDeleted($id) {}
		public function onSwapped($id1, $id2) {}
		
		// required
		public function onSaved($id) 
		{
			$users = Synchronizable::load('User', User::$table, array('id' => $id));
			if ($users == null)
				echo '<p>Bei der Registrierung trat ein Fehler auf.</p>';
			else
			{
				$assoc = new UserGroupAssociation();
				$assoc->setUserID($id);
				$assoc->setGroupID($config['defaultGroup']);
				Synchronizable::save($assoc, UserGroupAssociation::$table);
				
				echo '<p>Der Benutzer wurde erfolgreich erstellt.</p>';
			}
		}
	}*/
	
	class RegistrationEditor extends TableEditor
	{
		private function getRandomString($length) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
			$string = '';    
			for ($p = 0; $p < $length; $p++) {
				$string .= $characters[mt_rand(0, strlen($characters))];
			}
			return $string;
		}
		
		public function __construct()
		{
			parent::__construct(User::$table);
		}
			
		protected function onSaved($id)
		{
			global $config;
		
			echo "<p>ID: $id</p>";
		
			$users = Synchronizable::load('User', User::$table, array('id' => $id));
			if (is_null($users))
				echo '<p>Bei der Registrierung trat ein Fehler auf.</p>';
			else
			{
				$assoc = new UserGroupAssociation();
				$assoc->setUserID($id);
				$assoc->setGroupID($config['defaultGroup']);
				Synchronizable::save($assoc, UserGroupAssociation::$table);
				
				echo '<p>Der Benutzer wurde erfolgreich erstellt. Du kannst dich einloggen. Bevor du jedoch Rechte bekommst, musst du von einem Administrator freigeschaltet werden.</p>';
			}
		}
		
		protected function verify($id, &$data)
		{
			global $config;
		
			$username = mysql_real_escape_string($data['username'], MySQL::getConnection());
			$email = mysql_real_escape_string($data['email'], MySQL::getConnection());
			if (strlen($username) > 24 || strlen($username) < 5)
			{
				echo '<p>Der Benutzername muss zwischen 5 und 24 Zeichen lang sein.</p>';
				return false;
			}
			
			$users = Synchronizable::loadCustom('User', 'SELECT * FROM ' . User::$table . " WHERE `username`='$username' OR `email`='$email'");
			if (!is_null($users))
			{
				echo '<p>Ein Benutzer mit derselben E-Mail bzw. demselben Benutzernamen existiert bereits.</p>';
				return false;
			}
			
			$password = $this->getRandomString(8);
			$data['passwordHash'] = md5($password);
			
			// SEND TO EMAIL, OUTPUTTING TO TO FAULTY SMTP SERVER
			echo "<p>Dein generiertes Passwort lautet: <b>$password</b></p>";
			
			return true;
		}
	}
	
	$edit = new RegistrationEditor();
	$edit->show();
?>