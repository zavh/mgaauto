<?php
 
class Session {
	private $db;
	
	public function __construct(){  
		$this->db = new Database;
	// Set handler to overide SESSION  
		session_set_save_handler(  
			array($this, "_open"),  
			array($this, "_close"),  
			array($this, "_read"),  
			array($this, "_write"),  
			array($this, "_destroy"),  
			array($this, "_gc")  
		);
		// Start the session  
		session_start();
	}
}  
?>