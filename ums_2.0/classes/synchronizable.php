<?php
	
	require_once('classes/mysql.php');
	
	abstract class Synchronizable
	{
		
		abstract protected function getID();
		abstract protected function getDataProperties();
		abstract protected function getDataArrayProperty();
		
		abstract protected function setID($value);
		
		private static $cache = array();
		
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
		
		// NOT CACHED!!!
		public static function loadCustom($class, $query)
		{
			if (is_subclass_of($class, 'Synchronizable'))
			{
				//$object = new $class();
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
		
		private static function loadByData($class, $data)
		{
			if (is_subclass_of($class, 'Synchronizable'))
			{
				$object = new $class();
				
				$object->setID($data['id']);
				unset($data['id']);
				$object->setData($data);
				
				return $object;
			}
			else
			{
				echo "$class does not extend Synchronizable.";
				return null;
			}
		}
		
		/*
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
		}*/
		
		public static function load($class, $table, $constraints = null, $appendix = '')
		{
			if (!array_key_exists($table, self::$cache))
			{
				$result = MySQL::executeQuery("SELECT * FROM `$table`");
				
				while ($row = mysql_fetch_assoc($result))
					self::$cache[$table][] = $row;
			}
			
			$objects = null;
			foreach (self::$cache[$table] as $row)
			{
				if (!is_null($constraints))
				{
					foreach ($constraints as $column => $value)
						if ($row[$column] != $value)
							continue 2;
				}
				
				$objects[] = self::loadByData($class, $row);
			}
			
			return $objects;
		}
		
	}

	
?>