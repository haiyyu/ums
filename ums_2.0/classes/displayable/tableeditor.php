<?php
	require_once('classes/mysql.php');
	require_once('classes/synchronizable.php');
	require_once('classes/langvar.php');
	require_once('classes/login.php');
	require_once('classes/customfield.php');
	require_once('classes/url.php');
	require_once('classes/permission.php');
	require_once('classes/usergroupassociation.php');
	
	require_once('classes/displayable.php');
	
	require_once('classes/listeners/tableeditorlistener.php');
	
	class TableEditor extends Displayable
	{
		
		class Mode
		{
			const DISPLAY = 0;
			const EDIT = 1;
			const SWAP = 2;
			const SAVE = 3;
			const DELETE = 4;
			const CREATE = 5;
			const INVALID = 6;
		}
	
		/*
		 * IMPORTANT: PERMISSION NAMING CONVENTIONS
		 *
		 *		Generally speaking, permissions are named as follows:
		 *			tableeditor.[read|write].tableName.columnName
		 *		Note that if writing is allowed but reading isn't, the column and its value will still show up on the editing page.
		 *		Default: Reading and writing disallowed.
		 *
		 *		One can also specify a permission to be active for the whole table:
		 *			tableeditor.[read|write].tableName
		 *		Permissions set for individual columns have higher priority.
		 *		Default: Reading and writing disallowed.
		 *
		 *		There are two special modes that affect the table itself as opposed to its entries.
		 *			tableeditor.[create|delete].tableName
		 *		These specify whether entries can be created and/or deleted.
		 *		Default: Creating and deleting disallowed.
		 *
		 */
	
		private $table;
		private $id;
		private $sortable;
		private $createOnly;
		private $mode;
		
		private static $pDefault = array(
			'create' => false,
			'delete' => false,
			'edit' => false,
			'read' => false,
			'write' => false,
			'move' => false,
			'obligatory' => true);
			
		private $listeners;

		public function getTable() { return $this->table; }
		public function getID() { return $this->id; }
		public function isSortable() { return $this->sortable; }
		
		public function setTable($value) { $this->table = $value; }
		public function setID($value) { $this->id = $value; }
		public function setSortable($value) { $this->sortable = $value; }
		
		// Displayable
		public function getTemplateFile() 
		{ 
			switch ($this->mode)
			{
				case Mode::DISPLAY:
					return 'templates/tableeditorDisplay.html';
				case Mode::EDIT:
					return 'templates/tableeditorEdit.html';
				default:
					return 'templates/tableeditorNotAvailable.html';
			}
		}
	
		public function __construct($table, $id = -1, $sortable = false)
		{
			$this->setTable($table);
			$this->setID($id);
			$this->setSortable($sortable);
			$this->listeners = array();
			
			/*if (!isset($_GET['tMode']))
				$mode = 'list';
			else
				$mode = $_GET['tMode'];
				
			if ($mode == 'list' && $this->id == -1)
				return $this->displayList();
			elseif ($mode == 'edit')
				return $this->editItem($this->id == -1 ? $_GET['tId'] : $this->id);
			elseif ($mode == 'save')
				return $this->saveItem($this->id == -1 ? $_GET['tId'] : $this->id);
			elseif ($mode == 'create' && $this->id == -1)
				return $this->createItem();
			elseif ($mode == 'delete' && $this->id == -1)
				return $this->deleteItem($this->id == -1 ? $_GET['tId'] : $this->id);
			elseif ($mode == 'swap' && $this->id == -1)
				return ($this->swapItems($_GET['tId1'], $_GET['tId2']) && $this->displayList());
			else
				$this->setTemplate('error', 'Bad mode.');*/
			
			if (!isset($_GET['tMode']) || (($mode = $_GET['tMode']) == 'list'))
				$this->mode = Mode::DISPLAY;
			elseif ($mode == 'edit')
				$this->mode = Mode::EDIT;
			elseif ($mode == 'save')
				$this->mode = Mode::SAVE;
			elseif ($mode == 'create')
				$this->mode = Mode::CREATE;
			elseif ($mode == 'delete')
				$this->mode = Mode::DELETE;
			elseif ($mode == 'swap')
				$this->mode = Mode::SWAP;
			else
				$this->mode = Mode::INVALID;
		}
		
		public function addListener(TableEditorListener $listener)
		{
			$this->listeners[] = $listener;
		}
		
		protected function onEdit($id)
		{
			foreach ($this->listeners as $listener)
				$listener->onEdit($id);
		}
		
		protected function onCreate()
		{
			foreach ($this->listeners as $listener)
				$listener->onCreate();
		}
		
		protected function onDeleted($id)
		{
			foreach ($this->listeners as $listener)
				$listener->onDeleted($id);
		}
		
		protected function onSaved($id)
		{
			foreach ($this->listeners as $listener)
				$listener->onSaved($id);
		}
		
		protected function onDisplayList()
		{
			foreach ($this->listeners as $listener)
				$listener->onDisplayList();
		}
		
		protected function onSwapped($id1, $id2)
		{
			foreach ($this->listeners as $listener)
				$listener->onSwapped($id1, $id2);
		}
		
		protected function verify($id, &$data)
		{
			return true;
		}
		
		// Displayable
		protected function onDisplay()
		{
			//if (!Login::isLoggedIn())
			//	return false;
		
			
				
			
		}
		
		private function getTablePermissions()
		{
			$pTable['read'] = Login::permitted('tableeditor.read.' . $this->table, self::$pDefault['read']);
			$pTable['write'] = Login::permitted('tableeditor.write.' . $this->table, $this->id == -1 ? self::$pDefault['write'] : true);
			//$pTable['write'] = Login::permitted('tableeditor.write.' . $this->table, self::$pDefault['write']);
			$pTable['create'] = Login::permitted('tableeditor.create.' . $this->table, self::$pDefault['create']);
			$pTable['edit'] = Login::permitted('tableeditor.edit.' . $this->table, self::$pDefault['edit']);
			$pTable['delete'] = Login::permitted('tableeditor.delete.' . $this->table, self::$pDefault['delete']);
			$pTable['move'] = Login::permitted('tableeditor.move.' . $this->table, self::$pDefault['move']);
			$pTable['obligatory'] = Login::permitted('tableeditor.obligatory.' . $this->table, self::$pDefault['obligatory']);
			
			return $pTable;
		}

		private function displayList()
		{
			
			$this->onDisplayList();
			
			$titleColumns = array();
			
			// get permissions
			$tablePerms = $this->getTablePermissions();
			$columnPerms = array();
			
			$result = MySQL::executeQuery('SHOW COLUMNS FROM `' . $this->table . '`');
			while ($column = mysql_fetch_assoc($result))
			{
				$columnName = $column['Field'];
				$permissionName = 'tableeditor.read.' . $this->table . ".$columnName";
				$columnPerms[$columnName] = Login::permitted($permissionName, $tablePerms['read']);		
				
				//echo "[$permissionName " . ($columnPerms[$columnName] ? 'y ' : 'n ') . ']';
				
				if ($columnPerms[$columnName])
				{
					$langVar = 'name.' . $this->table . ".$columnName";	
					$titleColumns[] = $langVar;
				}
			}
			
			$this->templateSet('titleColumns', $titleColumns);
			
			if ($this->sortable)
				$sqlOrderBy = 'COALESCE(`order`, `id`)';
			else
				$sqlOrderBy = '`id`';
			$query = 'SELECT * FROM ' . $this->table . " ORDER BY $sqlOrderBy";
			$result = MySQL::executeQuery($query);
			
			$rows = null;
			$lastOrder = -1;
			$resorted = false;
			while ($row = mysql_fetch_assoc($result))
			{
				if ($this->sortable)
				{
					if (is_null($row['order']))
					{
						$row['order'] = ($lastOrder == -1) ? 0 : ($lastOrder + 1);
					
						$updateQuery = 'UPDATE `' . $this->table . '` SET `order`=\'' . $row['order'] . '\' WHERE `id`=\'' . $row['id'] . '\'';
						MySQL::executeQuery($updateQuery);
						
						$resorted = true;
					}
					
					$lastOrder = $row['order'];
				}
				
				$rows[] = $row;
			}
			
			// if resorting has been done, the data needs to be reloaded from the database
			if ($resorted)
			{
				$rows = null;
				$result = MySQL::executeQuery($query);
			}
			
			$this->templateSet('canEdit', $tablePerms['edit']);
			$this->templateSet('canDelete', $tablePerms['delete']);
			$this->templateSet('canMove', $this->sortable && $tablePerms['move']);
			
			$templateRows = array();
			if (!is_null($rows))
			{
				for ($i = 0; $i < count($rows); $i++)
				{
					$row = $rows[$i];
					$templateRow['columns'] = array();
					
					foreach ($row as $column => $value)
					{
						$perm = $columnPerms[$column];
						
						if ($perm)
							$templateRow['columns'][$column] = $value;
					}
					
					$templateRows[] = $templateRow;
				}
			}
			
			$this->templateSet('rows', $templateRows);
			
			return true;
		}
		
		private function swapItems($id1, $id2)
		{
			$id1 = mysql_real_escape_string($id1, MySQL::getConnection());
			$id2 = mysql_real_escape_string($id2, MySQL::getConnection());
			
			$tablePerms = $this->getTablePermissions();
			$canSwap = $tablePerms['move'];
			if (!$canSwap)
				return false;
			
			$rows = MySQL::executeQuery('SELECT * FROM `' . $this->table . "` WHERE `id`='$id1' OR `id`='$id2' ORDER BY `id`");
			$id1Order = -1;
			$id2Order = -1;
			while ($row = mysql_fetch_assoc($rows))
			{
				if ($row['id'] == $id1)
					$id1Order = $row['order'];
				elseif ($row['id'] = $id2)
					$id2Order = $row['order'];
			}
			
			if ($id1Order == -1 || $id2Order == -1)
			{
				echo '<p>Fehler beim Sortiervorgang.</p>';
				return false;
			}
			
			MySQL::executeQuery('UPDATE `' . $this->table . "` SET `order`='$id2Order' WHERE `id`='$id1'");
			MySQL::executeQuery('UPDATE `' . $this->table . "` SET `order`='$id1Order' WHERE `id`='$id2'");
			
			$this->onSwapped($id1, $id2);
			return true;
		}
		
		private function editItem($id)
		{
			$this->onEdit($id);
			
			$id = mysql_real_escape_string($id, MySQL::getConnection());
		
			$query = 'SELECT * FROM ' . $this->table . ' WHERE `id`=' . $id;
			$result = mysql_fetch_assoc(MySQL::executeQuery($query));
			
			$tablePerms = $this->getTablePermissions();
			if (!$tablePerms['edit'])
			{
				echo '<p>Sie sind nicht berechtigt, Einträge zu editieren.</p>';
				return false;
			}
				
			$url = new URL();
			$url->tMode = 'save';
			$url->tId = $id;
			
			echo '<form method="post" action="' . $url->toString() . '"><table>';
			
			foreach ($result as $column => $value)
			{
				if ($column == 'id' || ($column == 'order' && $this->sortable)) continue;
				
				$canWrite = Login::permitted('tableeditor.write.' . $this->table . ".$column", $tablePerms['write']);
				if ($canWrite)
					echo '<tr><td>' . LangVar::get('name.' . $this->table . ".$column") . '</td>' . '<td><input type="text" name="' . $column . '" value="' . $value . '" /></td>';
			}
			
			// IMPLEMENT LANGUAGE VARIABLE (was too lazy) AND CSS DIS SHIT
			echo '<tr><td colspan="2"><input name="submit" type="submit" value="Speichern" style="width:100%;" /></tr></table></form>';
			
			return true;
		}
		
		private function deleteItem($id)
		{
			$id = mysql_real_escape_string($id, MySQL::getConnection());
		
			$tablePerms = $this->getTablePermissions();
			$canDelete = Login::permitted('tableeditor.delete.' . $this->table, $tablePerms['delete']);
			
			if ($canDelete)
			{
				$query = 'DELETE FROM ' . $this->table . ' WHERE `id`=\'' . $id . '\'';
				MySQL::executeQuery($query);
				
				$this->onDeleted($id);
				return true;
			}
			else
				return false;
		}
		
		private function createItem()
		{
			$result = MySQL::executeQuery('SHOW COLUMNS FROM ' . $this->table);
			
			$tablePerms = $this->getTablePermissions();
			if (!$tablePerms['create'])
			{
				echo '<p>Sie sind nicht berechtigt, Einträge zu erstellen.</p>';
				return false;
			}
			
			$url = new URL();
			$url->tMode = 'save';
			$url->tId = -1;
			
			echo '<form method="post" action="' . $url->toString() . '"><table>';
			
			while ($column = mysql_fetch_assoc($result))
			{
				$columnName = $column['Field'];
				
				if ($columnName == 'id' || ($columnName == 'order' && $this->sortable)) continue;
				
				$permName = 'tableeditor.write.' . $this->table . ".$columnName";
				//echo "<p>$permName</p>";
				$canWrite = Login::permitted($permName, $tablePerms['write']);
				if ($canWrite)
					echo '<tr><td>' . LangVar::get('name.' . $this->table . ".$columnName") . '</td>' . '<td><input type="text" name="' . $columnName . '" /></td>';
			}
			
			// IMPLEMENT LANGUAGE VARIABLE HERE TOO LOL XD
			echo '<tr><td colspan="2"><input name="submit" type="submit" value="Speichern" style="width:100%;" /></tr></table></form>';
			
			return true;
		}
		
		private function saveItem($id)
		{
			// REFACTOR THIS TO NOT USE $_POST
			$id = mysql_real_escape_string($id, MySQL::getConnection());
			
			$tablePerms = $this->getTablePermissions();
			
			$setString = '';
			$columnResult = MySQL::executeQuery('SHOW COLUMNS FROM ' . $this->table);
		
			$data = array();
			while ($column = mysql_fetch_array($columnResult))
			{
				$valid = true;
				$columnName = $column['Field'];
				$obligatory = true;
				
				if ($columnName == 'id' || ($columnName == 'order' && $this->sortable)) continue;
					
				$canSave = Login::permitted('tableeditor.write.' . $this->table . ".$columnName", $tablePerms['write']);
				if (!$canSave)
					continue;
					
				$obligatory = Login::permitted('tableeditor.obligatory.' . $this->table . ".$columnName", $tablePerms['obligatory']);;
						
				if ($_POST[$columnName] == '' && $obligatory)
				{
					echo $columnName;
					$valid = false;
				}
				
				if (!$valid)
				{
					echo '<p>Bitte geben Sie für alle obligatorischen Felder einen Wert ein.</p>';
					return false;
				}
				
				if (!($_POST[$columnName] == '' && !$obligatory))
				{
					$columnValue = mysql_real_escape_string($_POST[$columnName], MySQL::getConnection());
					$data[$columnName] = $columnValue;
				}
			}
			
			$valid = $this->verify($id, $data);
			if ($valid)
			{
				$setString = '';
				foreach ($data as $name => $value)
				{
					if (!empty($setString))
						$setString .= ', ';
						
					$setString .= "`$name`='$value'";
				}
			}
			else
				return false;
			
			$constraint = "`id`='$id'";
			$query = 'SELECT `id` FROM ' . $this->table . " WHERE $constraint";
			
			$existsResult = MySQL::executeQuery($query);
			
			if (mysql_num_rows($existsResult) == 0)
			{
				if (!Login::permitted('tableeditor.create.' . $this->table, $tablePerms['create']))
				{
					echo '<p>Sie sind nicht berechtigt, Einträge zu erstellen.</p>';
					return false;
				}
				
				$query = 'INSERT INTO ' . $this->table . " SET $setString";
				$created = true;
			}
			else
			{
				if (!Login::permitted('tableeditor.edit.' . $this->table, $tablePerms['edit']))
				{
					echo '<p>Sie sind nicht berechtigt, Einträge zu editieren.</p>';
					return false;
				}
					
				$query = 'UPDATE ' . $this->table . " SET $setString WHERE $constraint";
				$created = false;
			}
			
			//echo "<p>$query</p>";

			//areturn false; // REMOVE THIS
			
			MySQL::executeQuery($query);
			if ($created)
				$id = mysql_insert_id(MySQL::getConnection());
			
			// REFACTOR THIS!!!!!! RETURN AND LET THE USER DECIDE FOR THEMSELVES WHAT THEY WANT TO OUTPUT
			$backUrl = new Url();
			$backUrl->tMode = 'list';
			echo '<p>Speichern erfolgreich.</p>';
			
			$this->onSaved($id);
			return true;
			
		}
		
		/*private function saveItem($id)
		{
			// REFACTOR THIS TO NOT USE $_POST
			$id = mysql_real_escape_string($id, MySQL::getConnection());
			
			$tablePerms = $this->getTablePermissions();
			
			$setString = '';
			$columnResult = MySQL::executeQuery('SHOW COLUMNS FROM ' . $this->table);
		
			$verifyData = array();
			while ($column = mysql_fetch_array($columnResult))
			{
				$valid = true;
				$columnName = $column['Field'];
				$obligatory = true;
				
				if ($columnName == 'id' || ($columnName == 'order' && $this->sortable)) continue;
					
				$canSave = Login::permitted('tableeditor.write.' . $this->table . ".$columnName", $tablePerms['write']);
				if (!$canSave)
					continue;
					
				$obligatory = Login::permitted('tableeditor.obligatory.' . $this->table . ".$columnName", $tablePerms['obligatory']);;
						
				if ($_POST[$columnName] == '' && $obligatory)
				{
					echo $columnName;
					$valid = false;
				}
				
				if (!$valid)
				{
					echo '<p>Bitte geben Sie für alle obligatorischen Felder einen Wert ein.</p>';
					return false;
				}
					
				if (!($_POST[$columnName] == '' && !$obligatory))
				{
					if (!empty($setString))
						$setString .= ', ';
						
					$columnValue = mysql_real_escape_string($_POST[$columnName], MySQL::getConnection());
					$setString .= "`$columnName`='" . $columnValue . '\'';
					
					$verifyData[$columnName] = $columnValue;
				}
			}
			
			if (!$this->verify($id, $verifyData))
				return false;
			
			$constraint = "`id`='$id'";
			$query = 'SELECT `id` FROM ' . $this->table . " WHERE $constraint";
			
			$existsResult = MySQL::executeQuery($query);
			
			if (mysql_num_rows($existsResult) == 0)
			{
				if (!Login::permitted('tableeditor.create.' . $this->table, $tablePerms['create']))
				{
					echo '<p>Sie sind nicht berechtigt, Einträge zu erstellen.</p>';
					return false;
				}
				
				$query = 'INSERT INTO ' . $this->table . " SET $setString";
			}
			else
			{
				if (!Login::permitted('tableeditor.edit.' . $this->table, $tablePerms['edit']))
				{
					echo '<p>Sie sind nicht berechtigt, Einträge zu editieren.</p>';
					return false;
				}
					
				$query = 'UPDATE ' . $this->table . " SET $setString WHERE $constraint";
			}
			
			MySQL::executeQuery($query);
			
			// REFACTOR THIS!!!!!! RETURN AND LET THE USER DECIDE FOR THEMSELVES WHAT THEY WANT TO OUTPUT
			$backUrl = new Url();
			$backUrl->tMode = 'list';
			echo '<p>Speichern erfolgreich.</p>';
			
			$this->onSaved($id);
			return true;
			
		}*/
	
	}
?>