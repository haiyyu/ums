<?php
	require_once('template.php');
	
	class Page extends Template
	{
		
		protected function getTemplateFile() { return 'pageTemplate.html'; }
		
		protected function onDisplay()
		{
			$this->templateSet('title', 'Meine Internetseite');
			$this->templateSet('navItems', array('Home', 'Kontakt', 'Login'));
		}
		
	}
?>