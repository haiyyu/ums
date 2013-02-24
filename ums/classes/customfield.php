<?php
	require_once('classes/mysql.php');
	require_once('classes/synchronizable.php');
	
	class CustomField extends Synchronizable
	{
		
		public static $table = 'ums_customfields';
		
		/*public static function getCustomFields()
		{
			$query = 'SELECT * FROM ' . self::$table;
			$result = MySQL::executeQuery($query);
			
		}*/
		
		private $id;
		protected $name;
		protected $title;
		protected $description;
		protected $type;
		protected $obligatory;
		
		public function getID() { return $this->id; } // Synchronizable
		//protected function getTable() { return self::$table; } // Synchronizable
		protected function getDataProperties() { return array('name', 'title', 'description', 'type', 'obligatory'); } // Synchronizable
		protected function getDataArrayProperty() { return null; } // Synchronizable
		
		public function getName() { return $this->name; }
		public function getTitle() { return $this->title; }
		public function getDescription() { return $this->description; }
		public function getType() { return $this->type; }
		public function getObligatory() { return $this->obligatory; }
		
		protected function setID($value) { $this->id = $value; } // Synchronizable
		
		public function setName($value) { if (is_string($value)) $this->name = $value; else { error(ERROR_DATATYPE); } }
		public function setTitle($value) { if (is_string($value)) $this->title = $value; else { error(ERROR_DATATYPE); } }
		public function setDescription($value) { if (is_string($value)) $this->description = $value; else { error(ERROR_DATATYPE); } }
		public function setType($value) { if (is_string($value)) $this->type = $type; else { error(ERROR_DATATYPE); } }
		public function setObligatory($value) { if (is_numeric($value)) $this->obligatory = $value; else { error(ERROR_DATATYPE); } }
		
		public function __construct()
		{
			$this->id = -1;
		}
		
	}
?>