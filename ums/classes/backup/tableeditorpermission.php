<?php
	require_once('classes/mysql.php');
	require_once('classes/synchronizable.php');
	
	class TableEditorPermission extends Synchronizable
	{
	
		public static $table_ = 'ums_tableeditor_permissions'; // REFACTOR THIS
	
		private $id;
		protected $table;
		protected $column;
		protected $permission;
	
		public function getID() { return $this->id; } // Synchronizable
		protected function getDataProperties() { return array('table', 'column', 'permission'); } // Synchronizable
		protected function getDataArrayProperty() { null; } // Synchronizable
		
		public function getTable() { return $this->table; }
		public function getColumn() { return $this->column; }
		public function getPermission() { return $this->permission; }
		
		protected function setID($value) { $this->id = $value; } // Synchronizable
		
		public function setTable($value) { $this->table = $value; }
		public function setColumn($value) { $this->column = $value; }
		public function setPermission($value) { $this->permission = $value; }
		
		public function __construct()
		{
			$this->id = -1;
		}
	
	}
	
?>
	