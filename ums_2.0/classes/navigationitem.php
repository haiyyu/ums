<?php
	require_once('proc.php');
	require_once('classes/synchronizable.php');
	
	class NavigationItem extends Synchronizable
	{
	
		public static $table = 'ums_navigationitems';
	
		private $id;
		protected $text;
		protected $destination;
		
		public function getID() { return $this->id; }
		protected function getDataProperties() { return array('text', 'destination'); }
		protected function getDataArrayProperty() { return null; }
		
		public function getText() { return $this->text; }
		public function getDestination() { return $this->destination; }
		
		protected function setID($value) { $this->id = $value; }
		
		public function setText($value) { if (is_string($value)) $this->text = $value; else { error(ERROR_DATATYPE); } }
		public function setDestination($value) { if (is_string($value)) $this->title = $value; else { error(ERROR_DATATYPE); } }
		
		public function __construct()
		{
			$this->id = -1;
		}

	}
?>