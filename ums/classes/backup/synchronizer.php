<?php

	require_once('classes/mysql.php');
	require_once('classes/synchronizable.php');
	
	class Synchronizer
	{
	
		public static function save($object)
		{
			if ($object instanceof Synchronizable)
			{
				$id = $object->getID();
				$data = $object->getData();
				$table = $object->getTable();
				
				$set = '';
				foreach ($data as $key => $value)
				{
					if ($set != '')
						$set .= ', ';
					$set .= "`$key`='" . mysql_real_escape_string($value, MySQL::getConnection()) . "'";
				}
				
				$constraint = "`id`='$id'";
				$existsResult = MySQL::executeQuery("SELECT `id` FROM $table WHERE $constraint");
				if (mysql_num_rows($existsResult) == 0)
					$query = "INSERT INTO $table SET $set WHERE $constraint";
				else
					$query = "UPDATE $table SET $set WHERE $constraint";
				MySQL::executeQuery($query);
				
				return true;
			}
			else
				return false; // object could not be saved in database
		}

		
		
		public static function load($class)
		{
			return self::load($class, null, null);
		}
		
		public static function load($class, $id)
		{
			return self::load($class, 'id', $id);
		}
		
		public static function load($class, $column, $value)
		{
			if (is_subclass_of($class, 'Synchronizable'))
			{
				$table = $object->getTable();
				$column = mysql_real_escape_string($column, MySQL::getConnection());
				$value = mysql_real_escape_string($value, MySQL::getConnection());
				
				if (is_null($column))
					$where = '';
				else
					$where = "WHERE `$column`='$value'";
				$query = "SELECT * FROM $table $where";
				
				while ($row = mysql_fetch_assoc($query))
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
	
	}