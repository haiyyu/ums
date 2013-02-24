<?php
	require_once('proc.php');
	require_once('classes/mysql.php');
	require_once('classes/synchronizable.php');
	require_once('classes/usergroup.php');
	require_once('classes/usergroupassociation.php');
	
	class User extends Synchronizable
	{
		
		public static $table = 'ums_users';
		
		private static $errorCouldNotAccess = 'Could not access information in MySQL database.';
		private static $errorWrongType = 'Wrong parameter type was passed.';
		
		protected $id;
		protected $username;
		protected $passwordHash;
		protected $email;
		protected $isAdmin;
		protected $customData = array();
		
		public function getID() { return $this->id; } // Synchronizable
		protected function getDataProperties() { return array('username', 'passwordHash', 'email', 'isAdmin'); } // Synchronizable
		protected function getDataArrayProperty() { return 'customData'; } // Synchronizable
		
		public function getUsername() { return $this->username; }
		public function getPasswordHash() { return $this->passwordHash; }
		public function getEmail() { return $this->email; }
		public function getIsAdmin() { return $this->isAdmin; }
		public function getCustomData($key) 
		{ 
			if (array_key_exists($key, $this->customData)) 
				return $this->customData[$key]; 
			else 
				return null;
		}
		
		public function getGroups()
		{
			$assocs = Synchronizable::load('UserGroupAssociation', 'ums_usergroups_associations', array('user' => $this->id));
			if (is_null($assocs))
				return null;
			
			$groupIDs = null;
			/*foreach ($assocs as $assoc)
			{
				$group = Synchronizable::load('UserGroup', 'ums_usergroups', array('id' => $assoc->getID()));
				if (!is_null($group))
					$groups[] = $group[0];
			}*/
			$where = '';
			foreach ($assocs as $assoc)
			{
				if (!empty($where))
					$where .= ' OR ';
				$where .= '`id`=\'' . $assoc->getGroupID() . '\'';
			}
			
			$query = "SELECT * FROM `ums_usergroups` WHERE $where ORDER BY `order`";
			$groups = Synchronizable::loadCustom('UserGroup', $query);
			return $groups;
		}
		
		protected function setID($value) { $this->id = $value; } // Synchronizable
		
		public function setUsername($value) { if (is_string($value)) $this->username = $value; else { error(ERROR_DATATYPE); } }
		public function setPassword($value) { if (is_string($value)) $this->passwordHash = md5($value); else { error(ERROR_DATATYPE); } }
		public function setEmail($value) { if (is_string($value)) $this->email = $value; else { error(ERROR_DATATYPE); } }
		public function setIsAdmin($value) { if (is_bool($value)) $this->isAdmin = $value; else { error(ERROR_DATATYPE); } }
		public function setCustomData($key, $value) { $this->customData[$key] = $value; }
		
		public function __construct()
		{
			$this->id = -1;
		}
		
		public static function byIDs(array $ids)
		{
			$constraint = '';
			foreach ($ids as $id)
			{
				if (!empty($constraint))
					$constraint .= ' OR ';
				$constraint .= "`id`='$id'";
			}
			
			$users = Synchronizable::loadCustom('User', 'SELECT * FROM `' . User::$table . "` WHERE $constraint");
			return $users;
		}
		
	}
?>