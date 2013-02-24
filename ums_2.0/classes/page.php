<?php
	require_once('classes/login.php');
	require_once('classes/synchronizable.php');
	
	class Page extends Synchronizable 
	{
	
		public static $table = 'ums_pages';
	
		private $id;
		protected $name;
		protected $file;
	
		public function getID() { return $this->id; } // Synchronizable
		protected function getDataProperties() { return array('name', 'file'); } // Synchronizable
		protected function getDataArrayProperty() { return null; } // Synchronizable
		
		public function getName() { return $this->name; }
		public function getFile() { return $this->file; }
		
		protected function setID($value) { $this->id = $value; } // Synchronizable
		
		public function setName($value) { $this->name = $value; }
		public function setFile($value) { $this->file = $value; }
		
		public function __construct()
		{
			$this->id = -1;
		}
	}
	
	
?>