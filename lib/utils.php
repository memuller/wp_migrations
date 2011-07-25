<?php 
	
	void function message($text, $line_break = true){
		$break = $line_break ? "\n" : "" ;
		echo $text . $break ;
	}

	void function progress($text){
		message($text, false);
	}
 ?>