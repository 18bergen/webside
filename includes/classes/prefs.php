<?php
class prefs {

	/* Constructor */
	function prefs(){ 
		global $db;
		$this->cacheEnabled = false;
		$this->cache = Array();
		$res = $db->query("SELECT name, value FROM innstillinger"); 
		while ($row = $res->fetch_assoc()){
			$n = $row['name'];
			$v = $row['value'];
			$this->$n = $v;
		}
	}

	function beginUpdate(){

	}
	
	function getValue($name){
		global $db;
		$Result = $db->query("SELECT value FROM innstillinger WHERE name='$name'"); 
		$Row = $Result->fetch_assoc();
		return stripslashes($Row['value']);
	}

	function setValue($name,$value){
		global $db;
		$value = addslashes($value);
		$db->query("UPDATE innstillinger SET value='$value' WHERE name='$name'"); 
	}

}

?>