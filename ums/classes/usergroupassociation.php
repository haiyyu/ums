<?php
	require_once('classes/Synchronizable.php');
	
	class UserGroupAssociation extends Synchronizable
	{
		/*
		abstract protected function getID();
		abstract protected function getDataProperties();
		abstract protected function getDataArrayProperty();
		
		abstract protected function setID($value);*/
		
		public static $table = 'ums_usergroups_associations';
		
		private $id;
		protected $group;
		protected $user;
		
		public function getID() { return $this->id; }
		protected function getDataProperties() { return array('group', 'user'); }
		protected function getDataArrayProperty() { return null; }
		
		public function getGroup()
		{
			$gs = Synchronizable::load('UserGroup', UserGroup::$table, array('id' => $this->group));
			if (is_null($gs))
				return null;
			else
				return $gs[0];
		}
		
		public function getGroupID() { return $this->group; }
		
		public function getUser()
		{
			$us = Synchronizable::load('User', User::$table, array('id' => $this->user));
			if (is_null($us))
				return null;
			else
				return $us[0];
		}
		
		public function getUserID() { return $this->user; }
		
		protected function setID($value) { $this->id = $value; }
		
		public function setGroupID($value) { $this->group = $value; }
		public function setUserID($value) { $this->user = $value; }
		
	}
	
?>