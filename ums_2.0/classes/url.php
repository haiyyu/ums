<?php
	
	class URL
	{
		
		private $vars;
		
		public function __get($key)
		{
			if (array_key_exists($key, $vars))
				return $this->vars[$key];
			else
				return null;
		}
		
		public function __set($key, $value)
		{
			$this->vars[$key] = $value;
		}
		
		public function __construct()
		{
			$this->vars = $_GET;
		}
		
		public function toString()
		{
			$out = '';
			foreach ($this->vars as $key => $value)
			{
				if (empty($out))
					$out .= '?';
				else
					$out .= '&';
				
				$out .= htmlentities($key) . '=' . htmlentities($value);
			}
			
			return $out;
		}
		
		public static function get(array $vars)
		{
			$url = new URL();
			foreach ($vars as $key => $value)
				$url->$key = $value;
				
			return $url;
		}
		
		public static function getString(array $vars)
		{
			return self::get($vars)->toString();
		}
		
	}
	
?>