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
	
	
		/*private static $defaultPermissions = 52; // 110100 - admins may read&write, users may read, obligatory
		private static $defaultDeletePermissions = 4; // 100 - admins may delete
		private static $defaultCreatePermissions = 5; // 101 - admins&guests may create*/
	
		private $table;
		private $id;
		private $sortable;
		private $createOnly;
		
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
		public function getTemplateName() { return 'templates/tableeditor.html'; }
	
		public function __construct($table, $id = -1, $sortable = false)
		{
			$this->setTable($table);
			$this->setID($id);
			$this->setSortable($sortable);
			$this->listeners = array();
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
		
			if (!isset($_GET['tMode']))
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
				$this->setTemplate('error', 'Bad mode.');
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
		
			echo '<table><tr>';
			
			$tablePerms = $this->getTablePermissions();
			//if ($tablePerms['delete'])
			echo '<td>Bearbeiten</td>';
			
			$columnPermissions = array();
			$columnResult = MySQL::executeQuery('SHOW COLUMNS FROM ' . $this->table);
			while ($column = mysql_fetch_array($columnResult))
			{
				$columnPermissions[$column['Field']] = Login::permitted('tableeditor.read.' . $this->table . '.' . $column['Field'], $tablePerms['read']);
				
				if ($columnPermissions[$column['Field']])
				{
					$langVar = 'name.' . $this->table . '.' . $column['Field'];
					$name = LangVar::get($langVar);
					echo "<td><b>$name</b></td>";
				}
			}
			
			echo '</tr>';
			
			/*
				entries = get from database ( sort by order, id ) // sort by order, if thats not set, sort by id

				lastOrder = -1 // order of last item
				for each entry in entries do
					if order of entry = not set then
						if lastOrder = -1 then
							order of entry = 0
						else
							order of entry = lastOrder + 1
						end
						
						write to database ( for entry set order to (order of entry) )
					end
					
					lastOrder = order of entry
				end
			*/
			
			$orderBy = '';
			if ($this->sortable)
				$orderBy = 'ORDER BY COALESCE(`order`, `id`)';
			else
				$orderBy .= 'ORDER BY `id`';
			$query = 'SELECT * FROM ' . $this->table . " $orderBy";
			$rowsResult = MySQL::executeQuery($query);
			
			$rows = null;
			$lastOrder = -1;
			$resorted = false;
			while ($row = mysql_fetch_assoc($rowsResult))
			{
				if ($this->sortable)
				{
					if (is_null($row['order']))
					{
						if ($lastOrder == -1)
							$row['order'] = 0;
						else
							$row['order'] = $lastOrder + 1;
						
						$updateQuery = 'UPDATE `' . $this->table . '` SET `order`=\'' . $row['order'] . '\' WHERE `id`=\'' . $row['id'] . '\'';
						MySQL::executeQuery($updateQuery);
						
						$resorted = true;
					}
					
					$lastOrder = $row['order'];
				}
				
				//echo $row['id'] . ': ' . $row['order'] . ', ';
				$rows[] = $row;
			}
			
			if ($resorted)
			{
				$rows = null;
				$rowsResult = MySQL::executeQuery($query);

				while ($row = mysql_fetch_assoc($rowsResult))
					$rows[] = $row;
			}
				
			if (!is_null($rows))
			{
				for ($i = 0; $i < count($rows); $i++)
				{
					$row = $rows[$i];
					
					$url = new URL();
					$url->tMode = 'edit';
					$url->tId = $row['id'];
				
					echo '<tr onclick="document.location=\'' . $url->toString() . '\';">';
					
					echo '<td>';
					if ($tablePerms['delete'])
					{
						$deleteUrl = new URL();
						$deleteUrl->tMode = 'delete';
						$deleteUrl->tId = $row['id'];
						
						echo '<a href="' . $deleteUrl->toString() . '">x</a>';
					}
					
					if ($this->sortable && $tablePerms['move'])
					{
						$swapUrl = new URL();
						$swapUrl->tMode = 'swap';
						
						if ($i > 0)
						{
							$swapUrl->tId1 = $rows[$i - 1]['id'];
							$swapUrl->tId2 = $row['id'];
							
							echo '<a href="' . $swapUrl->toString() . '">^</a>';
						}
						
						if ($i < count($rows) - 1)
						{
							$swapUrl->tId1 = $row['id'];
							$swapUrl->tId2 = $rows[$i + 1]['id'];
							
							echo '<a href="' . $swapUrl->toString() . '">v</a>';
						}
					}
					
					foreach ($row as $column => $value)
					{		
						$perm = $columnPermissions[$column];
							
						if ($perm)
							echo '<td>' . $value . '</td>';
					}
					echo '</tr>';
				}
			}
			
			echo '</table>';
			
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