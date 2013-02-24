<?php
	require_once('classes/synchronizable.php');
	require_once('classes/navigationitem.php');
	require_once('classes/login.php');
	
	class Navigation
	{
		
		public static function show()
		{
			$navigationItems = Synchronizable::load('NavigationItem', NavigationItem::$table, null, 'ORDER BY `order`');
			if (is_null($navigationItems))
				return;
			
			$output = '<ul id="navigationList">';
			foreach ($navigationItems as $navigationItem)
			{	
				if (Login::permitted('navigation.view.' . $navigationItem->getID(), 1))
					$output .= '<li><a href="index.php?page=' . $navigationItem->getDestination() . '">' . $navigationItem->getText() . '</a></li> ';
			}
			$output .= '</ul>';
			
			echo $output;
		}
		
	}
?>