<?php

	require_once('classes/langvar.php');

	abstract class Displayable
	{
		
		private $variables;
		
		protected abstract function getTemplateFile();	
		protected abstract function onDisplay();
	
		public function __construct()
		{
			$this->variables = array();
		}
	
		public final function display()
		{
			$this->onDisplay();
			
			$t = $this->variables;
			include $this->getTemplateFile();
		}
		
		protected final function templateSet($name, $value)
		{
			$this->variables[$name] = $value;
		}
		
		protected final function templateGet($name)
		{
			if (array_key_exists($name, $this->variables))
				return $this->variables[$name];
			else
				return null;
		}
	
	}
	
?>	