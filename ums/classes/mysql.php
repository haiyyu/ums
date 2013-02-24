<?php
	
	class MySQL
	{
	
		private static $connection;

		private static $host = 'localhost';
		private static $user = 'root';
		private static $pass = 'fishface';
		private static $database = 'usermanagementsystem'; // change to "test" or "usermanagementsystem", depending on computer
		
		private static $displayErrors = true;
		private static $queryTrace = array();
		
		public static function getConnection()
		{
			if (!isset(self::$connection))
			{
				self::$connection = mysql_connect(self::$host, self::$user, self::$pass);
				mysql_select_db(self::$database, self::$connection);
			}
				
			return self::$connection;
		}
		
		public static function executeQuery($query)
		{
			$con = self::getConnection();
			$res = mysql_query($query, $con);
			
			self::$queryTrace[] = $query;
			
			if (self::$displayErrors && mysql_error($con) != '') 
				echo "<p>The following query produced an error: $query<br />" . mysql_error($con);
			return $res;
		}
		
		public static function displayQueryTrace()
		{
			print_r(self::$queryTrace);
		}
		
		public static function setDisplayErrors($value)
		{
			self::$displayErrors = $value;
		}
		
		public static function columnExists($table, $column)
		{
			$result = MySQL::executeQuery("SHOW COLUMNS FROM $table");
	
			while ($fetch = mysql_fetch_array($result))
				$fieldNames[] = $fetch['Field'];
				
			return in_array($column, $fieldNames);
		}
		
	}

?>