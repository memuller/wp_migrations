<?php 
	/**
	* 
	*/
	class WPConfig
	{
		public $file_content ;
		public $options;
		public $assignments ;

		function __construct($path)
		{
			$file_content = file_get_contents("$path/wp-config.php");
			$this->options = array(); $this->assignments = array();
			foreach (preg_split("/(\r?\n)/", $file_content) as $line) {
				if(strstr($line, "define(")){
					$assignment = substr($line, strpos($line, "('") , strpos($line, ");"));
					if(empty($assignment))
						$assignment = substr($line, strpos($line, "( '") , strpos($line, ");"));
					$arr = explode(",", $assignment);
					$key = explode("'", $arr[0]);
					$key = $key[1]; $value = $arr[1] ;
					if( strpos($value, "'") ){
						$value = explode("'", $value);
						$value = $value[1];

					}else{
						$value = str_replace(');', '', $value);
						$value = trim($value);
						if($value == 'false')
							$value = false ;
						if($value == 'true')
							$value = true ;
					}
					$this->options[$key] = $value;
					$this->assignments[$key] = $assignment ; 
				}
			}

		}
	}

	$cfg = new WPConfig("/var/www/wp3");
	print_r($cfg->options);

 ?>