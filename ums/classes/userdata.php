<?php
	
	require_once('classes/synchronizable.php');
	require_once('classes/mysql.php');
	
	class UserData extends Synchronizable
	{
	
		protected $id;
		protected $username;
		protected $passwordHash;
		protected $email;
		protected $customData = array();
		
		protected function getTableName() { return 'ums_users'; }
		protected function getIDFieldName() { return 'id'; }
		protected function getExcludedFields() { return array(); }
		protected function getDataArrayFieldName() { return 'customData'; }
		
		public function getID() { return $this->id; }
		public function getUsername() { return $this->username; }
		public function getPasswordHash() { return $this->passwordHash; }
		public function getEmail() { return $this->email; }
		public function getCustomData($key) 
		{ 
			if (array_key_exists($key, $this->customData)) 
				return $this->customData[$key]; 
			else 
				return null;
		}
		
		public function setUsername($value) { if (is_string($value)) $this->username = $value; else { error(ERROR_DATATYPE); } }
		public function setPassword($value) { if (is_string($value)) $this->passwordHash = md5($value); else { error(ERROR_DATATYPE); } }
		public function setEmail($value) { if (is_string($value)) $this->email = $value; else { error(ERROR_DATATYPE); } }
		public function setCustomData($key, $value) { $this->customData[$key] = $value; }
		
		private function __construct()
		{
			$this->id = -1;
		}
		
		public static function create()
		{
			return new UserData();
		}
		
		public static function loadFromID($id)
		{
			$userData = self::create();
			if ($userData->loadFromDatabase('id', $id))
				return $userData;
			else
				return null;
		}
		
		public function save()
		{
			$this->saveToDatabase();
		}
	
	}
?>