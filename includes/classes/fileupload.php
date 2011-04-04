<?

class fileupload extends base {

	var $errors = Array();
	var $file_extension;
	var $directory;
	var $varname;
	var $filename;
	var $filetype;
	var $fullpath;
	var $sizelimit = 204800;
	var $imageMimeTypes = Array(
		'image/gif' 	=> 'gif',
		'image/x-gif' 	=> 'gif',
		'image/jpeg' 	=> 'jpg',
		'image/pjpeg' 	=> 'jpg',
		'image/x-jpeg'	=> 'jpg',
		'image/png' 	=> 'png',
		'image/x-png' 	=> 'png',
	);
	
	function uploadError($Error){
		print("<div style=\"background:#EEEEEE; border:#FF0000 2px solid; margin:10px; padding:10px; color:#FF0000; font: 12px 'Arial';\">\n");
		print("<table><tr><td valign=\"top\"><img src=\"images/banging-head-against-a-brick-wall.jpg\" style=\"float:left; width: 200px; height: 150px;\" />\n");
		print("</td><td>\n<b>Det oppstod en feil under opplastingen av filen.</b><br />\n");
		print("$Error\n");
		print("</td></tr></table>\n");
		print("</div>\n");
		if (isset($this->_eventlog)){
			$this->_eventlog->addToErrorLog("Det oppstod en feil under opplastingen av en fil:<br />$Error");
		}
		exit;
 	}
	
	function upload(){
		if ($this->filetype == "image"){
			$mimetypes_allowed = $this->imageMimeTypes;
		} else if ($this->filetype == "word"){
			$mimetypes_allowed = $this->wordMimeTypes;			
		}
			
		if (is_uploaded_file($_FILES[$this->varname]['tmp_name'])){
			
			$this->file_extension = in_array($_FILES[$this->varname]['type'],array_keys($mimetypes_allowed))
				? $mimetypes_allowed[$_FILES[$this->varname]['type']] : "unknown";
			if ($this->file_extension == "unknown"){ 
				array_push($this->errors,"
					Bildet du forsøkte å laste opp var av typen ".$_FILES[$this->varname]['type']." 
					og ble derfor ikke gjenkjent som et bilde som kan brukes på nettsiden.
					Godkjente bildetyper er:<br />
					&nbsp;&nbsp;&nbsp;&nbsp;".implode("<br />&nbsp;&nbsp;&nbsp;&nbsp;",array_keys($this->imageMimeTypes))
				);
				return 0;
			}
			if ($_FILES[$this->varname]['size'] > $this->sizelimit){
				$sbd = round($this->sizelimit/1024);
				array_push($this->errors,"Sorry, the image \"".$_FILES[$this->varname]['name']."\" is too big.<br />
					Max filesize is $sbd kB ($this->sizelimit bytes).");
				return 0;
			}
			$this->fullpath = "$this->directory$this->filename.$this->file_extension";
			
			
			// Eksisterer mappen vi skal flytte filen til?
			$tmp = trim($this->directory,"/");
			$tmp = explode("/",$tmp);
			$i = 0;
			$tmp2 = "/";
			foreach ($tmp as $f){
				$tmp2 .= $f."/";
				if (!file_exists($tmp2)){
					if (!mkdir($tmp2)){
						$this->fatalError("Fikk ikke tilgang til å opprette \"$tmp2\"!");			
					}
				}
			}
			
			// Flytt filen dit den skal
			if (move_uploaded_file($_FILES[$this->varname]['tmp_name'], $this->fullpath)) {
				/*
				$old = umask(0);
				chmod($this->fullpath, 0666);
				umask($old);
				*/
				if (isset($this->_eventlog)){
					$this->_eventlog->addToActivityLog("Lastet opp fil '".$_FILES[$this->varname]['name']."'. Lagret som '$this->fullpath' (fileupload.php)");
				}
			} else {
				array_push($this->errors,"Kunne ikke flytte den opplastede filen \"".$_FILES[$this->varname]['name']."\" til \"".$this->fullpath."\" pga. en ukjent feil. Manglende rettigheter?");
				return 0;
			}
			
		} else {
			array_push($this->errors,"Filen \"".$_FILES[$this->varname]['name']."\" ble ikke lastet opp pga. en ukjent feil.");
			return 0;
		}
	}
	

}

?>