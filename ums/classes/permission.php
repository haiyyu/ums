<?php
	require_once('classes/synchronizable.php');
	
	class Permission extends Synchronizable
	{
	
		public static $table = 'ums_permissions';
		
		private $id;
		protected $name;
		protected $group;
		protected $allowed;
		
		protected function getID() { return $this->id; }
		protected function getDataProperties() { return array('name', 'group', 'allowed'); }
		protected function getDataArrayProperty() { return null; }
		
		public function getName() { return $this->name; }
		public function getGroup() { return $this->group; }
		public function isAllowed() { return $this->allowed; }
		
		protected function setID($value) { $this->id = $value; }
		
		public function setName($value) { $this->name = $value; }
		public function setGroup($value) { $this->group = $value; }
		public function setIsAllowed($value) { $this->isAllowed = $value; }
		
		public function __construct()
		{
			$this->id = -1;
		}
		
	}