<?php
class addresslist extends base {

	var $getvars = array();

	var $table_addresslist = "addresslist";
	
	//var $allow_send_application = false;
	//var $allow_approve = false;
	
	function __construct() {
		$this->table_addresslist = DBPREFIX.$this->table_addresslist;
	}
	
	function initialize(){
	
		$this->initialize_base();
		
		if (count($this->coolUrlSplitted) > 0) 
			$this->action = $this->coolUrlSplitted[0];
		else 
			$this->action = "";
	
	}
	
	function run(){
		$this->initialize();

		switch ($this->action) {

			case 'new':
				$this->editForm();
				break;

			case 'edit':
				if (isset($_GET['k']))
					$this->editForm(intval($_GET['k']));
				break;

			case 'save':
				$this->saveEntry();
				break;

			case 'del':
				if (isset($_GET['k']))
					$this->delForm(intval($_GET['k']));
				break;

			case 'dodel':
				if (isset($_POST['k']))
					$this->del($_POST['k']);
				break;
			
			default:
				$this->outputList();
				break;
		
		}
	}
	
	function outputList() {
		$new = $this->generateCoolUrl('/new/');
		$res = $this->query("SELECT * FROM $this->table_addresslist ORDER BY kundenr");
		print "<p><a href='$new'>Legg til ny</a></p>\n";
		print "<table border='1' cellpadding='4' cellspacing='0'>\n";
			print "<tr><td>Kundenr.</td><td>Gate</td><td>Gatenr.</td><td>Postnr.</td><td>Stred</td><td> </td></tr>\n";		
		while ($row = $res->fetch_assoc()) {
			$kundenr = stripslashes($row['kundenr']);
			$street = stripslashes($row['street']);
			$streetno = stripslashes($row['streetno']);
			$postno = stripslashes($row['postno']);
			$city = stripslashes($row['city']);
			$edit = $this->generateCoolUrl('/edit/','k='.$row['kundenr']);
			$del = $this->generateCoolUrl('/del/','k='.$row['kundenr']);
			print "<tr><td>$kundenr</td><td>$street</td><td>$streetno</td><td>$postno</td><td>$city</td><td><a href='$edit'>Rediger</a> <a href='$del'>Slett</a></td></tr>\n";		
		}
		print "</table>\n";
	}

	function editForm($k = 0) {
		if ($k == 0) {
			$kundenr = "";
			$street = "";
			$streetno = "";
			$postno = "";
			$city = "";
		} else {
			$res = $this->query("SELECT * FROM $this->table_addresslist WHERE kundenr=\"".addslashes($k)."\"");
			if ($res->num_rows != 1) $this->fatalError('kunde not found');
			$row = $res->fetch_assoc();
			$kundenr = $k;
			$street = stripslashes($row['street']);
			$streetno = stripslashes($row['streetno']);
			$postno = stripslashes($row['postno']);
			$city = stripslashes($row['city']);
		}
		$post = $this->generateCoolUrl('/save/','noprint=true');
		print "
			<form method='post' action='$post'>
			<table>
				<tr><td>Kundenr.</td><td><input type='text' name='kundenr' value=\"".$kundenr."\" /></td></tr>
				<tr><td>Gate</td><td><input type='text' name='street' value=\"".$street."\" /></td></tr>
				<tr><td>Gatenr.</td><td><input type='text' name='streetno' value=\"".$streetno."\" /></td></tr>
				<tr><td>Postnr.</td><td><input type='text' name='postno' value=\"".$postno."\" /></td></tr>
				<tr><td>Sted</td><td><input type='text' name='city' value=\"".$city."\" /></td></tr>
			</table>
			<input type='submit' value='Lagre' />
			</form>
		";		
	}
	
	function saveEntry() {
		$kundenr = intval($_POST['kundenr']);
		$street = addslashes($_POST['street']);
		$streetno = addslashes($_POST['streetno']);
		$postno = intval($_POST['postno']);
		$city = addslashes($_POST['city']);
		if (empty($kundenr) || empty($street) || empty($streetno) || empty($postno) || empty($city)) $this->fatalError("Du må fylle inn alle feltene"); 

		$res = $this->query("SELECT * FROM $this->table_addresslist WHERE kundenr=".$kundenr);
		if ($res->num_rows > 1) $this->fatalError('invalid kundenr');
		if ($res->num_rows == 1) {
			$this->query("UPDATE $this->table_addresslist SET 
				street=\"$street\",
				streetno=\"$streetno\",
				postno=\"$postno\",
				city=\"$city\"
			WHERE kundenr=".$kundenr);			
		} else {
			$this->query("INSERT INTO $this->table_addresslist (kundenr,street,streetno,postno,city)
			VALUES (\"$kundenr\",\"$street\",\"$streetno\",\"$postno\",\"$city\")");
		}
		$this->redirect($this->generateCoolUrl("/"),"Ok, lagret");
	}
	
	function delForm($k) {
		$post = $this->generateCoolUrl('/dodel/','noprint=true');
		print "
			<form method='post' action='$post'>
				Er du sikker?
				<input type='hidden' name='k' value=\"$k\" />
				<input type='submit' value='Jada, masa' />
			</form>
		";
	}
	
	function del($k) {
		$res = $this->query("SELECT * FROM $this->table_addresslist WHERE kundenr=\"".addslashes($k)."\"");
		if ($res->num_rows != 1) $this->fatalError('kunde not found');
		$this->query("DELETE FROM $this->table_addresslist WHERE kundenr=\"".addslashes($k)."\"");
		$this->redirect($this->generateCoolUrl("/"),"Ok, slettet");	
	}

}
?>
