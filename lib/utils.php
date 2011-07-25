<?php 
	error_reporting(E_ALL);
	function block_non_cli(){
		if (! defined('STDIN') )
			die() ; 
	}

	function message($text, $line_break = true){
		$break = $line_break ? "\n" : "" ;
		echo $text . $break ;
	}

	function step($text){
		message($text, false);
	}
	function done(){
		message(" done.");
	}

	block_non_cli();
 ?>