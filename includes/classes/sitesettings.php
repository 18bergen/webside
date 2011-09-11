<?

class sitesettings extends base {

	var $getvars = array("savesettings","addsetting","dropsetting");
	
	var $table_settings = "innstillinger";
	
	var $allow_viewsettings = false;
	var $allow_editsettings = false;
	
	/* Constructor */
	function sitesettings(){

	}
	
	function initialize(){
		$this->initialize_base();
	}

	function run(){
		$this->initialize();
		if (isset($_GET['savesettings'])){
			$this->saveSettings();
		} else if (isset($_GET['dropsetting'])){
			$this->dropSetting($_GET['dropsetting']);
		} else if (isset($_GET['addsetting'])){
			$this->addSetting();
		} else {
			$this->outputSettings();
		}
	}
	
	function outputSettings(){
		if (!$this->allow_viewsettings){
			$this->permissionDenied();
			return;
		}
		
		print "<h1>Sideinnstillinger</h1>
			<form method='post' action='".$this->generateURL(array("noprint=true","savesettings"))."'>
		";

		$res = $this->query("SELECT id,name,value FROM ".$this->table_settings." ORDER BY name");
		print "
		
			<table><tr><td>
			<a href='".$this->generateURL(array("noprint=true","addsetting"))."'>
			<img src='".$this->image_dir."plus.gif' border='0' alt='Drop' /> </a>
						</td><td>

			<a href='".$this->generateURL(array("noprint=true","addsetting"))."'>Ny innstilling</a>
</td></tr></table>			
			<table cellpadding='0' cellspacing='0'>
				<tr><th>Name</th><th>Value</th></tr>
		";
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			print "
				<tr><td>
					<input type='text' name='sname$id' class='setting_name' value='".stripslashes($row['name'])."' />
				</td><td>
					<input type='text' name='svalue$id' class='setting_value' value='".stripslashes($row['value'])."' />
				</td><td>
					<a href='".$this->generateURL(array("noprint=true","dropsetting=$id"))."'><img src='".$this->image_dir."minus.gif' border='0' alt='Drop' /></a>
				</td></tr>
			";
		}
		print "</table>
			<p>
				<input type='submit' value='Lagre innstillinger' />
			</p>
			</form>
			<br /><br />
		";
	}
	
	function saveSettings(){
		if (!$this->allow_editsettings){
			$this->permissionDenied();
			return;
		}
		$res = $this->query("SELECT id,name,value FROM ".$this->table_settings);
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			$oldname = stripslashes($row['name']);
			$oldvalue = stripslashes($row['value']);
			$name = addslashes($_POST["sname$id"]);
			$value = addslashes($_POST["svalue$id"]);
			if (empty($name) OR empty($value)){
				$this->fatalError("Halted. You must fill in all fields! Field $id not filled in properly. Old values: [$oldname,$oldvalue]. New values: [$name,$value]");
			}
			if (($name != $oldname) OR ($value != $oldvalue)){
				$this->addToActivityLog("Oppdaterte innstilling $id. Var: [$oldname,$oldvalue]. Endret til: [$name,$value]");
				$this->query("UPDATE ".$this->table_settings." SET name='$name', value='$value' WHERE id='$id'");
			}
		}	
		header("Location: ".$this->generateURL("")."\n\n");
		exit();
	}
	
	function dropSetting($id){
		if (!$this->allow_editsettings){
			$this->permissionDenied();
			return;
		}
		if (!is_numeric($id)){
			$this->permissionDenied();
			return;		
		}
		$res = $this->query("SELECT id,name,value FROM ".$this->table_settings." WHERE id='$id'");
		if ($res->num_rows != 1){
			$this->fatalError("Invalid request");
		}
		$row = $res->fetch_assoc();
		$oldname = stripslashes($row['name']);
		$oldvalue = stripslashes($row['value']);
		$this->addToActivityLog("Slettet innstilling $id. Var: [$oldname,$oldvalue].");

		$this->query("DELETE FROM ".$this->table_settings." WHERE id='$id' LIMIT 1");
		header("Location: ".$this->generateURL("")."\n\n");
		exit();		
	}
	
	function addSetting(){
		if (!$this->allow_editsettings){
			$this->permissionDenied();
			return;
		}
		$this->addToActivityLog("Lagt til innstilling.");
		$this->query("INSERT INTO ".$this->table_settings." (name,value) VALUES ('.NY INNSTILLING','UTEN VERDI')");
		header("Location: ".$this->generateURL("")."\n\n");
		exit();		
	}
	

}

?>