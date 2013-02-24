<?php
	
	require_once('classes/mysql.php');
	
	abstract class Synchronizable
	{
		
		abstract protected function getID();
		abstract protected function getDataProperties();
		abstract protected function getDataArrayProperty();
		
		abstract protected function setID($value);
		
		public function getData()
		{
			$data = array();
			foreach ($this->getDataProperties() as $property)
			{
				$data[$property] = $this->$property;
			}
			$dataArrayProperty = $this->getDataArrayProperty();
			if (!is_null($dataArrayProperty))
			{
				foreach ($this->$dataArrayProperty as $key => $value)
				{
					$data[$key] = $value;
				}
			}
			
			return $data;
		}
		
		public function setData(array $data)
		{
			$dataProperties = $this->getDataProperties();
			$dataArrayProperty = $this->getDataArrayProperty();
			foreach ($data as $key => $value)
			{
				if (in_array($key, $dataProperties))
					$this->$key = $value;
				else
					$dataArray[$key] = $value;
			}
			if (isset($dataArray) && !is_null($this->getDataArrayProperty()))
				$this->$dataArrayProperty = $dataArray;
		}
		
		public static function save($object, $table, $returnQueryString = false)
		{
			if ($object instanceof Synchronizable)
			{
				$id = $object->getID();
				$data = $object->getData();
				
				$columnsResource = MySQL::executeQuery("SHOW COLUMNS FROM $table");
				while ($column = mysql_fetch_assoc($columnsResource))
					$columns[$column['Field']] = $column;		
				
				$set = '';
				foreach ($data as $key => $value)
				{
					if ($set != '')
						$set .= ', ';
						
					if (array_key_exists($key, $columns))
						$set .= "`$key`='" . mysql_real_escape_string($value, MySQL::getConnection()) . "'";
				}
				
				$constraint = "`id`='$id'";
				$existsResult = MySQL::executeQuery("SELECT `id` FROM $table WHERE $constraint");
				if (mysql_num_rows($existsResult) == 0)
					$query = "INSERT INTO $table SET $set";
				else
					$query = "UPDATE $table SET $set WHERE $constraint";
				
				if ($returnQueryString)
					return $query;
				else
					MySQL::executeQuery($query);
				
				return true;
			}
			else
				return false; // object could not be saved in database
		}

		public static function load_deprecated($class, $table, $column = null, $value = null)
		{
			if (is_subclass_of($class, 'Synchronizable'))
			{	
				if (is_null($column))
					$where = '';
				else
				{
					$column = mysql_real_escape_string($column, MySQL::getConnection());
					$value = mysql_real_escape_string($value, MySQL::getConnection());
					$where = "WHERE `$column`='$value'";
				}
				
				$query = "SELECT * FROM $table $where";
				$result = MySQL::executeQuery($query);
				
				while ($row = mysql_fetch_assoc($result))
				{
					$object = new $class();
					$object->setID($row['id']);
					unset($row['id']); // unset to prevent passing to setData function
					$object->setData($row);
					$objects[] = $object;
				}
				
				return $objects;
			}
			else
				return null;
		}
		
		public static function loadCustom($class, $query)
		{
			if (is_subclass_of($class, 'Synchronizable'))
			{
				$object = new $class();
				$result = MySQL::executeQuery($query);
				
				$objects = null;
				
				while ($row = mysql_fetch_assoc($result))
				{
					$object = new $class();
					$object->setID($row['id']);
					unset($row['id']);
					$object->setData($row);
					$objects[] = $object;
				}
				
				return $objects;
			}
			else
			{
				echo "$class does not extend Synchronizable.";
				return null;
			}
		}
		
		public static function load($class, $table, $constraints = null, $appendix = '')
		{
			$where = '';
			if (!is_null($constraints))
			{
				foreach ($constraints as $column => $value)
				{
					if ($where != '')
						$where .= ' AND ';
					else
						$where = 'WHERE ';
					$eColumn = mysql_real_escape_string($column, MySQL::getConnection());
					$eValue = mysql_real_escape_string($value, MySQL::getConnection());
					$where .= "`$eColumn`='$eValue'";
				}
			}
			
			$query = "SELECT * FROM `$table` $where $appendix";
			return Synchronizable::loadCustom($class, $query);
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