<?php
	
	require_once('classes/mysql.php');
	
	abstract class Synchronizable
	{
	
		abstract protected function getTableName();
		abstract protected function getIDFieldName();
		abstract protected function getExcludedFields(); // fields that are excluded
		abstract protected function getDataArrayFieldName();   // used to load and save information that isn't contained in a property
		
		final protected function saveToDatabase()
		{
		
			$data = get_object_vars($this);
			$dataArrayName = $this->getDataArrayFieldName();
			
			if (!is_null($dataArrayName))
				$data = array_merge($data, $this->$dataArrayName);
				
			$setString = '';
			foreach ($data as $name => $value)
			{
				if (in_array($name, $this->getExcludedFields()) or $name == $this->getIDFieldName() or is_array($value)) continue;
				if ($setString != '') $setString .= ', ';
				$setString .= "`$name`='" . mysql_real_escape_string($value, MySQL::getConnection()) . "'";
			}
			
			echo "[$setString]";
			
			
			//return; // TEMPORARY
		
			
			$idField = $this->getIDFieldName();
			$result = MySQL::executeQuery('SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $this->getIDFieldName() . '=' . $this->$idField . ' LIMIT 1');
			if (mysql_num_rows($result) == 0)
			{
				// doesn't exist
				MySQL::executeQuery('INSERT INTO ' . $this->getTableName() . ' SET ' . $setString);
				$this->$idField = mysql_insert_id(MySQL::getConnection());
			}
			else
			{
				// exists
				MySQL::executeQuery('UPDATE ' . $this->getTableName() . ' SET ' . $setString . ' WHERE ' . $this->getIDFieldName() . '=' . $this->$idField . ' LIMIT 1');
			}
		}
		
		final protected function loadFromDatabase($searchProperty, $value)
		{
			$result = MySQL::executeQuery('SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $searchProperty . '=\'' . $value . '\' LIMIT 1');
			
			if (mysql_num_rows($result) == 0)
				return false; // couldn't be found
			
			$dataArrayName = $this->getDataArrayFieldName();
			$dataArray = array();
			$row = mysql_fetch_assoc($result);
			foreach ($row as $key => $value)
			{
				if (in_array($key, $this->getExcludedFields())) continue;
				if (property_exists($this, $key))
					$this->$key = $value;
				else
					$dataArray[$key] = $value;
			}
			$this->$dataArrayName = $dataArray;
			
			return true;
		}
	
	}
	
	/*class Test extends Synchronizable
	{
	
		protected $id;
		protected $name;
		protected $email;
		protected $address;
		
		public function getID() { return $this->id; }
		public function getName() { return $this->name; }
		public function getEmail() { return $this->email; }
		public function getAddress() { return $this->address; }
		
		public function setName($value) { if (is_string($value)) $this->name = $value; else { error(ERROR_DATATYPE); } }
		public function setEmail($value) { if (is_string($value)) $this->email = $value; else { error(ERROR_DATATYPE); } }
		public function setAddress($value) { if (is_string($value)) $this->address = $value; else { error(ERROR_DATATYPE); } }
	
		protected function getTableName() { return 'test'; }
		protected function getIDFieldName() { return 'id'; }
		
		private function __construct()
		{
			$this->id = -1;
		}
		
		public function save()
		{
			$this->saveToDatabase();
		}
		
		public static function load($searchProperty, $value)
		{
			$obj = new Test();
			if ($obj->loadFromDatabase($searchProperty, $value))
				return $obj;
			else
				return null;
		}
		
		public static function create()
		{
			return new Test();
		}
	
	}*/
	
?>