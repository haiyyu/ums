<?php
	require_once("classes/mysql.php");
	
	class User
	{
		
		public static $table = 'ums_users';
		
		private static $errorCouldNotAccess = 'Could not access information in MySQL database.';
		private static $errorWrongType = 'Wrong parameter type was passed.';
		
		private $id;
		private $firstName;
		private $lastName;
		private $address;
		private $dateJoined;
		private $dateNextFee;
		private $username;
		private $passwordHash;
		private $email;
		private $isAdmin;
		
		public function getID()				{ return $this->id; }
		public function getFirstName()		{ return $this->firstName; }
		public function getLastName()		{ return $this->lastName; }
		public function getAddress()		{ return $this->address; }
		public function getDateJoined()		{ return $this->dateJoined; }
		public function getDateNextFee()	{ return $this->dateNextFee; }
		public function getUsername()		{ return $this->username; }
		public function getPasswordHash()	{ return $this->passwordHash; }
		public function getEmail()			{ return $this->email; }
		public function isAdmin()			{ return $this->isAdmin; }
		
		public function setFirstName($value) 	{ if (is_string($value)) $this->firstName = $value; else { error(ERROR_DATATYPE); } }
		public function setLastName($value)		{ if (is_string($value)) $this->lastName = $value; else { error(ERROR_DATATYPE); } }
		public function setAddress($value)		{ if (is_string($value)) $this->address = $value; else { error(ERROR_DATATYPE); } }
		public function setDateJoined($value) 	{ if ($value instanceof DateTime) $this->dateJoined = $value; else { error(ERROR_DATATYPE); } }
		public function setDateNextFee($value) 	{ if ($value instanceof DateTime) $this->dateNextFee = $value; else { error(ERROR_DATATYPE); } }
		public function setUsername($value)		{ if (is_string($value)) $this->username = $value; else { error(ERROR_DATATYPE); } }
		public function setPassword($value)		{ if (is_string($value)) $this->passwordHash = md5($value); else { error(ERROR_DATATYPE); } }
		public function setEmail($value)		{ if (is_string($value)) $this->email = $value; else { error(ERROR_DATATYPE); } }
		public function setAdmin($value)		{ if (is_bool($value)) $this->isAdmin = $value; else { error(ERROR_DATATYPE); } }
		
		// can only be called privately
		private function __construct()
		{
			$this->id = -1; // invalid id, used for not yet existent users
			$this->isAdmin = false;
		}
		
		// saves changes to database or creates user if non-existent
		public function saveInformation()
		{
			$con = MySQL::getConnection();
			$data = array(
				'firstName' => mysql_real_escape_string($this->getFirstName(), $con),
				'lastName' => mysql_real_escape_string($this->getLastName(), $con),
				'address' => mysql_real_escape_string($this->getAddress(), $con),
				'dateJoined' => $this->getDateJoined()->format('Y-m-d H:i:s'),
				'dateNextFee' => $this->getDateNextFee()->format('Y-m-d H:i:s'),
				'username' => mysql_real_escape_string($this->getUsername(), $con),
				'passwordHash' => $this->getPasswordHash,
				'email' => mysql_real_escape_string($this->getEmail(), $con),
				'isAdmin' => $this->isAdmin());
			$dataString = 
			   'firstName=\'' . $data['firstName'] . '\',
				lastName=\'' . $data['lastName'] . '\',
				address=\'' . $data['address'] . '\',
				dateJoined=\'' . $data['dateJoined'] . '\',
				dateNextFee=\'' . $data['dateNextFee'] . '\',
				username=\'' . $data['username'] . '\',
				passwordHash=\'' . $data['passwordHash'] . '\',
				email=\'' . $data['email'] . '\',
				isAdmin=\'' . $data['isAdmin'] . '\''; // string to be used in conjunction with MySQL's "SET" syntax
				
			if ($this->getID() == -1) 
			{	
				MySQL::executeQuery('INSERT INTO ' . self::$table . ' SET ' . $dataString);
				$this->id = mysql_insert_id(MySQL::getConnection());
			}
			else
			{
				// user already exists and needs to be updated
				MySQL::executeQuery('UPDATE ' . self::$table . ' SET ' . $dataString . ' WHERE id=' . $this->getID());
			}
		}
		
		// returns User object containing information about user associated with passed ID
		public static function fromDatabase($id)
		{	
			if (!is_numeric($id)) 
				return null;
				
			$result = MySQL::executeQuery('SELECT * FROM ' . self::$table . " WHERE id=$id LIMIT 1");
			
			if (!$result)
				error(ERROR_MYSQL);	// database could not be accessed
			elseif (mysql_num_rows($result) == 0)
				return null;						// user with passed id could not be found
			
			$data = mysql_fetch_array($result);
			
			$object = new User();
			$object->id = $data['id'];
			$object->setFirstName($data['firstName']);
			$object->setLastName($data['lastName']);
			$object->setAddress($data['address']);
			$object->setDateJoined(new DateTime($data['dateJoined']));
			$object->setDateNextFee(new DateTime($data['dateNextFee']));
			$object->setUsername($data['username']);
			$object->passwordHash = $data['passwordHash'];
			$object->setEmail($data['email']);
			$object->setAdmin($data['isAdmin'] == 1 ? true : false);
			
			return $object;
		}
		
		public static function fromDatabaseByUsername($username)
		{
			$escaped = mysql_real_escape_string($username, MySQL::getConnection());
			$result = MySQL::executeQuery('SELECT id FROM ' . self::$table . " WHERE username='$escaped' LIMIT 1");
			
			if (!$result)
				error(ERROR_MYSQL);	// database could not be accessed
			elseif (mysql_num_rows($result) == 0)
				return null;						// user with passed username could not be found
			
			$data = mysql_fetch_array($result);
			return self::fromDatabase($data['id']);
		}
		
		// returns User object empty of information, associated with non-occupied ID
		public static function createNew()
		{
			return new User();
		}
		
	}
?>