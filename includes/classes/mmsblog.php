<?
/*
class mmsblog {


	var $db_addr;
	var $db_user;
	var $db_pass;
	var $db_name;
	var $db_table_main;
	var $db_table_parts;
	var $save_dir;
	var $save_dir_virtual;
	var $memberlookup_function;

	var $debug = 0;
	
	var $dblink;
	var $initialized = false;
	var $entries;

	var $allowAllNumbers = false;
	var $allowedNumbers = array("4790207510");
	var $allowAllServiceCentres = true;
	var $allowedServiceCentres = array("mmsc.mobil.telenor.no");


	function initialize(){
		if (!is_dir($this->save_dir)){

			mkdir('mydir'); // or even 01777 so you get the sticky bit set
			@mkdir($this->save_dir) or $this->fatalError("Can't create save_dir (".$this->save_dir.").");
		}
		if (!is_writable($this->save_dir)){
			$this->fatalError("save_dir (".$this->save_dir.") isn't writable.");
		}
		$this->dblink = mysql_connect($this->db_addr,$this->db_user,$this->db_pass);
		mysql_select_db($this->db_name,$this->dblink);
		$this->initialized = true;
	}

	function fatalError($str){
		print "<div style='border: 1px solid #FF0000; background: #FFEEEE; margin: 5px; padding: 5px;'>An error occured: $str</div>";
		exit();
	}

	function dbquery($str){
		if ($this->debug) print "<div style='border: 1px solid #0000FF; background: #EEEEFF; margin: 5px; padding: 5px;'>$str</div>";
		if (!$this->initialized) $this->fatalError("Class not initialized. Please initialize class first");
		$res = mysql_query($str,$this->dblink);
		if (!$res) {   
			$this->fatalError('Invalid query: ' . mysql_error());
		}
		return $res;
	}

	function addEntry($raw_data){
		if (!$this->initialized) $this->fatalError("Class not initialized. Please initialize class first");

		$mimedecoder = new MIMEDECODE($raw_data,"\r\n");
		$object = $mimedecoder->decode();

		$sender = explode("@",$object->headers['from']);
		if (!$this->allowAllNumbers){
			if (!in_array($sender[0],$this->allowedNumbers)){
				$this->fatalError("Not allowed to send from this number!");
			}
		}
		if (!$this->allowAllServiceCentres){
			if (!in_array($sender[1],$this->allowedServiceCentres)){
				$this->fatalError("Not allowed to send from this number!");
			}
		}

		$from = addslashes($object->headers['from']);
		$timestamp = strtotime($object->headers['date']);
		$subject = addslashes($object->headers['subject']);

		$this->dbquery("INSERT INTO $this->db_table_main (sender,timestamp,subject) VALUES ('$from','$timestamp','$subject')");
		$id = $this->insert_id();
		
		$main_content_type = trim($object->ctype_primary)."/".trim($object->ctype_secondary);
		$this->addPart($object, $main_content_type, $id);
	}

	function addPart($object, $main_content_type="", $db_id) {
		if(!isset($object->parts)){
			//$ctype=trim(strtok($object->headers['content-type'],";"));
			
			$ctype = trim($object->ctype_primary)."/".trim($object->ctype_secondary);
			switch($ctype){
				
				case "text/html":

					$charset = "default";
					$ctypearray = explode(";",$object->headers['content-type']);
					foreach ($ctypearray as $c){
						$tmp = explode("=",$c);
						if ($tmp[0] == "Charset") $charset = $tmp[1];
					}

					$content = $object->body;
					if ($charset == "UTF-8") $content = utf8_decode($content);

					$contenttype = addslashes($ctype);
					$content = addslashes(nl2br($content));
					$id = $db_id;
					$this->dbquery("INSERT INTO $this->db_table_parts (contenttype,content,messageid) VALUES ('$contenttype','$content','$id')");
					break;

				case "text/plain":
				
					$enc = $object->headers['content-transfer-encoding'];
					if($enc != "quoted-printable"){
						
						$charset = "default";
						$ctypearray = explode(";",$object->headers['content-type']);
						foreach ($ctypearray as $c){
							$tmp = explode("=",$c);
							if ($tmp[0] == "Charset") $charset = $tmp[1];
						}

						$content = $object->body;
						if ($charset == "UTF-8") $content = utf8_decode($content);

						$contenttype = addslashes($ctype);
						$content = addslashes(nl2br($content));
						$id = $db_id;
						$this->dbquery("INSERT INTO $this->db_table_parts (contenttype,content,messageid) VALUES ('$contenttype','$content','$id')");
					}
					break;
				
				case "image/jpeg":
				case "image/gif":

					$filename = trim($object->headers['name']);
					if (empty($filename)){
						$filename = trim($object->headers['content-location']);
					}
					if(empty($filename)){
						trim(strtok($object->headers['content-type'],"="));
						$filename = trim(strtok("=\""));
					}

					$contenttype = addslashes($ctype);
					$id = $db_id;
					$this->dbquery("INSERT INTO $this->db_table_parts (contenttype,messageid) VALUES ('$contenttype','$id')");
					$newid = $this->insert_id();

					$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
					$newfilename = "mms_$newid.$ext";

					$content = addslashes($filename.":".$newfilename);
					$this->dbquery("UPDATE $this->db_table_parts SET content='$content' WHERE id='$newid'");
					$newid = $this->insert_id();
					
					$tmpfile = $this->save_dir.$newfilename;
					
					$fp = fopen($tmpfile,"w");
					fwrite($fp,$object->body);
					fclose($fp);
													
					break;

				default:
					$filename = trim($object->headers['name']);
					if (empty($filename)){
						$filename = trim($object->headers['content-location']);
					}
					if(empty($filename)){
						trim(strtok($object->headers['content-type'],"="));
						$filename = trim(strtok("=\""));
					}

					$contenttype = addslashes($ctype);
					$id = $db_id;
					$this->dbquery("INSERT INTO $this->db_table_parts (contenttype,messageid) VALUES ('$contenttype','$id')");
					$newid = $this->insert_id();

					$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
					$newfilename = "mms_$newid.$ext";

					$content = addslashes($filename.":".$newfilename);
					$this->dbquery("UPDATE $this->db_table_parts SET content='$content' WHERE id='$newid'");
					$newid = $this->insert_id();
					
					$tmpfile = $this->save_dir.$newfilename;
					
					$fp = fopen($tmpfile,"w");
					fwrite($fp,$object->body);
					fclose($fp);
													
					break;

			}

		} else {
			foreach($object->parts as $obj){
				$this->addPart($obj, $main_content_type, $db_id);
			}
		}
	}

	function fetchEntries(){
		if (!$this->initialized) $this->fatalError("Class not initialized. Please initialize class first");

		$this->entries = array();
		$res = $this->dbquery("SELECT 
				$this->db_table_main.id,
				$this->db_table_main.sender,
				$this->db_table_main.timestamp,
				$this->db_table_parts.contenttype,
				$this->db_table_parts.content
			FROM 
				$this->db_table_main, $this->db_table_parts
			WHERE 
				$this->db_table_main.id = $this->db_table_parts.messageid
			ORDER BY timestamp DESC"
		);
		while ($row = $res->fetch_assoc()){
			$id = $row['id'];
			if (isset($this->entries[$id])){
				$this->entries[$id]['parts'][] = array(
					'content-type' => $row['contenttype'],
					'content' => stripslashes($row['content'])
				);
			} else {
				$this->entries[$id] = array(
					'id' => $id,
					'sender' => $row['sender'],
					'timestamp' => $row['timestamp'],
					'parts' => array(array(
						'content-type' => $row['contenttype'],
						'content' => stripslashes($row['content'])
					))
				);
			}
		}
	}

	function printEntries(){
		if (count($this->entries) < 1){
			print "<i>No entries</i>";
		}
		foreach ($this->entries as $e){
			$sender = $e['sender'];
			if (!empty($this->memberlookup_function)){
				$mf = $this->memberlookup_function;
				$sender_split = explode("@",$sender);
				$sender = $mf($sender_split[0]);
			}
			print "
				<div style='border-top: 1px dashed #000000; margin: 5px; padding: 5px;'>
					<b>Fra: </b>$sender<br />
					<b>Sendt: </b>".date("d. M Y, H:i",$e['timestamp'])."<br />
					<div style='margin: 5px; padding: 5px;'>";
			foreach ($e['parts'] as $p){
				if (in_array($p['content-type'],array('image/jpeg','image/gif'))){
					$fname = substr($p['content'],strpos($p['content'],":")+1);
					$fpath = $this->save_dir_virtual.$fname;
					$fdim = getimagesize($this->save_dir.$fname);
					if ($fdim[0] > 450){ 
						$width = 450; 
						$d = 450/$fdim[0];
						$height = round($fdim[1]*$d);
					} else { 
						$width = $fdim[0]; 
						$height = $fdim[1];
					}

					print "
						<div style='padding: 3px;'><img src='$fpath' width='$width' height='$height' style='border: 1px solid #333333;' /></div>
					";
				} else if (in_array($p['content-type'],array('text/plain','text/html'))){
					print "
						<div style='padding: 3px;'>".$p['content']."</div>
					";
				}
			}
			print "
					</div>
				</div>
			";
		}
	}

}


*/

?>