<?php
class ckFinderPlugin extends base {
	
	function ckFinderPlugin() {

	}	
	
	function initialize(){
	
        @parent::initialize(); //$this->initialize_base();

        if (!isset($this->_cms)) {
            $this->fatalError("Not linked to CMS!");
        }
		
		$this->table_classpermissions = $this->_cms->table_classpermissions;
		$this->table_pagepermissions = $this->_cms->table_pagepermissions;
		$this->table_pagelabels = $this->_cms->table_pagelabels;
		$this->table_pages = $this->_cms->table_pages;
		$this->table_images = $this->_cms->table_images;
		
		//$this->logDebug($_SESSION['CKFinder_UserRole']);
	
	}
	
	function run(){
		$this->initialize();
		
		//$this->logDebug("[CKfinder] Hello world");

	}
	
	function inheritPermission($permissionName, $fromPage, $toPage) {
		//global $db, 
		
		$pp = $this->table_pagepermissions;
		$cp = $this->table_classpermissions;
		
		$res = $this->query(
			"SELECT 
				$cp.name,
				$cp.default_who,
				$cp.default_rights,
				$cp.default_groupowner,
				$pp.who,
				$pp.rights,
				$pp.groupowner
			FROM 
				$cp
			LEFT JOIN 
				$pp
			ON
				$pp.page='$fromPage'
					AND
				$pp.name=$cp.name
			WHERE 
				$cp.name='$permissionName'
			"
		);
		$row = $res->fetch_assoc();
		if (empty($row['who'])) {
			$who = $row['default_who'];
			$rights = $row['default_rights'];
			$group = $row['default_groupowner'];
		} else {
			$who = $row['who'];
			$rights = $row['rights'];
			$group = $row['groupowner'];			
		}	
		
		$res = $this->query(
			"SELECT who FROM $pp WHERE page='$toPage' AND name='$permissionName'"
		);
		if ($res->num_rows == 0) {
			$this->query(
				"INSERT INTO $pp (page,name,who,rights,groupowner)
				VALUES ('$toPage','$permissionName','$who','$rights','$group')"
			);		
		} else {
			$this->query(
				"UPDATE $pp SET
					who = '$who',
					rights = '$rights',
					groupowner = '$group'
				WHERE page='$toPage' AND name='$permissionName'"
			);
		}
		
	}
	
	
	function getPath($page_id) {
		//global $table_pages, $db;
		
		$id = intval($id);		
		$tp = $this->table_pages;
		
		$current_dir = 1; // rot-mappen
		$sti = array();
		while ($id != 1) {
			$res = $this->query("SELECT pageslug,parent FROM $tp WHERE id=$id");
			$row = $res->fetch_assoc();
			$sti[] = $row['pageslug'];
			$id = intval($row['parent']);
			$res->close();
		}
		$sti = array_reverse($sti);
		$sti = implode("/",$sti);
		$sti = "/".trim($sti,"/")."/";
		if ($sti == "//") $sti = "/";
		return $sti;
		
	}
	
	
	function folderCreated($folderPath,$baseDir) {
		
		/*
			$folderPath:					/Users/danmichael/Sites/18bergen/bergenvs/www/uimages/bilder/testmappe
			BG_WWW_PATH:					/Users/danmichael/Sites/18bergen/bergenvs/www/
			$this->userFilesDir:			userfiles/     (defined in base.php)
			$tmp:							bilder/testmappe			
		*/
		
		$tp = $this->table_pages;
		$pl = $this->table_pagelabels;

		$pathToUserFiles = BG_WWW_PATH.$this->userFilesDir;  				// /Users/danmichael/Sites/18bergen/bergenvs/www/userfiles/
		$pathToThumbs490 = $baseDir.'_thumbs490/';							// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/
		$folderBaseDir 	 = substr($folderPath, strlen($baseDir));			// Bilder
		$folderRelDir	 = substr($folderPath, strlen($pathToUserFiles));	// Medlemsfiler/xx/Bilder
		$folderThumbPath = $pathToThumbs490 . $folderBaseDir;				// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/Bilder/

        /*
        $this->logDebug("Folder created: $folderPath");
        $this->logDebug(" -> Userfiles: $pathToUserFiles");
        $this->logDebug(" -> Thumb dir: $folderThumbPath");
        $this->logDebug(" -> folderBaseDir: $folderBaseDir");
        $this->logDebug(" -> folderRelDir: $folderRelDir");
        */
        
        CKFinder_Connector_Utils_FileSystem::createDirectoryRecursively($folderThumbPath);
		
		$tmp = explode("/",trim($folderRelDir,"/"));
		$dir_name = array_pop($tmp);
		$full_slug = $this->userFilesVirtDir.implode("/",$tmp);
		//$this->logDebug(" -> Parent dir full_slug: $full_slug");
		
		$res = $this->query("SELECT id, owner, ownergroup FROM $tp WHERE fullslug=\"".addslashes($full_slug)."\" ORDER BY id LIMIT 1");
		if ($res->num_rows != 1) {
			$this->fatalError("Parent dir '$full_slug' not found");
		}
		$row = $res->fetch_assoc();
		$parentdir_id = $row['id'];
		$owner = $row['owner'];
		$ownergroup = $row['ownergroup'];

		$new_fullslug = $full_slug."/".$dir_name;
		
		$dl = $this->default_lang;

		// error_log("tp: $new_fullslug");
		
		$this->query("INSERT INTO $tp 
			(owner,ownergroup,class,pageslug,fullslug,parent,created,lastmodified)
			VALUES
			('$owner','$ownergroup',1,'$dir_name','$new_fullslug','$parentdir_id','".time()."','".time()."')"
		);
		$dir_id = $this->insert_id();
		
		$this->query("INSERT INTO $pl
			(page,lang,label,value,multiline)
			VALUES 
			('$dir_id','$dl','page_header','$dir_name',0)"
		);
		
		$this->inheritPermission('allow_read',$parentdir_id,$dir_id);
		$this->inheritPermission('allow_write',$parentdir_id,$dir_id);
	
	}
	
	function folderDeleted($folderPath) {

        $this->logDebug("Folder deleted: $folderPath");
        
        /*
        	This can be recursive! We need to implement a recursive delete..
        */
	
	}
	
	/* 
		Checks whether the current user is allowed to modify (rename or delete)
		the file in question.
	*/
	function allowWrite($filePath) {

		$tp = $this->table_pages;
		$ti = $this->table_images;

		if (!$this->isLoggedIn()) {
			return false; // obviously...
		}
		$userId = $this->login_identifier;
		
		/* Fetch info about directory */
			
			list($sourceDir,$sourceBasename) = $this->pathParts($filePath);
        
            // $this->logDebug(" -> Parent dir full_slug: $sourceDir");
            
            $res = $this->query("SELECT id, owner, ownergroup FROM $tp WHERE fullslug=\"".addslashes($sourceDir)."\" ORDER BY id LIMIT 1");
			if ($res->num_rows != 1) {
				// Image directory not found!
				$this->logDebug("[ckfinderplugin:allowWrite] Dir not found: $sourceDir");
				return false;
			}
			$row = $res->fetch_assoc();
			$dir_id = intval($row['id']);
			$dir_owner = intval($row['owner']);
			$dir_ownergroup = intval($row['ownergroup']);
		
		/* Fetch info about source file */

			$res = $this->query("SELECT id, filename, uploader FROM $ti WHERE parent=$dir_id AND filename=\"".addslashes($sourceBasename)."\" ORDER BY id LIMIT 1");
			if ($res->num_rows != 1) {
				// Image not found!
				$this->notSoFatalError("[ckfinderplugin:allowWrite] Image not found: $sourceBasename. Query: SELECT id, filename, uploader FROM $ti WHERE parent=$dir_id AND filename=\"".addslashes($sourceBasename)."\"");
			}
			$row = $res->fetch_assoc();
			$image_id = intval($row['id']);
			$image_owner = intval($row['uploader']);
		
		// Is user the image owner?
		if ($image_owner == $userId) return true;
		
		// Is user a superuser?
		if ($this->getUserRights() == 5) return true;
		
		// Else:
		return false;
		
	}
	
	function fileUploaded($filePath) {

		/*
			Example:
			$filePath:						/Users/danmichael/Sites/18bergen/bergenvs/www/userfiles/Bilder/nyheter/Soya.jpg
			BG_WWW_PATH:					/Users/danmichael/Sites/18bergen/bergenvs/www/
			$this->userFilesDir:			userfiles/     (defined in base.php)
			$this->userFilesVirtDir:		brukerfiler/   (defined in base.php)	
			$fileShortPath:					Bilder/nyheter/Soya.jpg
			
			$fileType:						Bilder
			$full_slug:						brukerfiler/Bilder/nyheter
			$basename:						Soya.jpg
		*/

		$tp = $this->table_pages;
		$ti = $this->table_images;

		$pathToUserFiles = BG_WWW_PATH.$this->userFilesDir;
		$fileShortPath = substr($filePath, strlen($pathToUserFiles));

		$tmp = explode("/",trim($fileShortPath,"/"));
		$basename = array_pop($tmp);
		$full_slug = $this->userFilesVirtDir.implode("/",$tmp);
		$fileType = array_shift($tmp);
		
		$this->logDebug("Uploaded file \"$basename\" of type \"$fileType\"");
		
		if (in_array($fileType,array('Bilder','Medlemsfiler'))) {
		
			$res = $this->query("SELECT id, owner, ownergroup FROM $tp WHERE fullslug=\"".addslashes($full_slug)."\" ORDER BY id LIMIT 1");
			if ($res->num_rows != 1) {
				$this->fatalError("Parent dir '$full_slug' not found");
			}
			$row = $res->fetch_assoc();
			$parentdir_id = intval($row['id']);
	
			list($width, $height, $type, $attr) = getimagesize($filePath);
			$path_parts = pathinfo($filePath);
			$extension = $path_parts['extension'];
			$filesize = filesize($filePath);
	
			$this->query(
				'INSERT INTO '.$ti.' (filename,caption,extension,width,height,size,timestamp,parent,uploader,upload_date)
				VALUES ("'.addslashes($basename).'","'.addslashes($basename).'",'.
					'"'.addslashes($extension).'",'.$width.','.$height.','.$filesize.','.
					time().', '.$parentdir_id.','.$this->login_identifier.',NOW())'
			);
			if ($this->affected_rows() != 1) {
				$this->fatalError("Could not insert image into 18bg db");
			}
		
		}

	}
	
	function checkThumbnail($filePath,$baseDir) {

		/* Base dir may be: 
			- /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/
			- /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/
		*/
	
		$tp = $this->table_pages;
		$ti = $this->table_images;

		$pathToUserFiles = $baseDir;
		$pathToThumbs490 = $pathToUserFiles.'_thumbs490/';
		$fileShortPath = substr($filePath, strlen($pathToUserFiles));
		$thumbFilePath = $pathToThumbs490.$fileShortPath;

        if (!file_exists($thumbFilePath)) {
			/* 
				CKFinder automaticly creates a 100x100 thumbnail, but we are also 
				interested in a 490xX one, so let's create it now 
			*/		
	        //$this->logDebug("Creating 490xX thumbnail: $thumbFilePath");

        	$d = dirname($thumbFilePath);
        	if (!file_exists($d)) {
		        //$this->logDebug("Creating dir: $d");
	        	CKFinder_Connector_Utils_FileSystem::createDirectoryRecursively($d);
	        }

			require_once CKFINDER_CONNECTOR_LIB_DIR . "/CommandHandler/Thumbnail.php";
			CKFinder_Connector_CommandHandler_Thumbnail::createThumb(
				$filePath, 
				$thumbFilePath, 
				490, 490, 	// width, height
				75, 		// quality
				true 		// preserve aspect ratio
			);
		}
	}
	
	function pathParts($path) {

		$pathToUserFiles = BG_WWW_PATH.$this->userFilesDir;
		$shortPath = substr($path, strlen($pathToUserFiles));
		$tmp = explode("/",trim($shortPath,"/"));
		$basename = array_pop($tmp);
		$full_slug = $this->userFilesVirtDir.implode("/",$tmp);
		return array($full_slug,$basename);
	
	}
	
	function fileRenamed($sourcePath, $destPath, $baseDir) {

		$tp = $this->table_pages;
		$ti = $this->table_images;

		$pathToUserFiles = BG_WWW_PATH.$this->userFilesDir;  				// /Users/danmichael/Sites/18bergen/bergenvs/www/userfiles/
		$pathToThumbs490 = $baseDir.'_thumbs490/';							// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/
		
		$sourceBasePath  = substr($sourcePath, strlen($baseDir));			// Bilder/test.jpg
		$sourceRelPath	 = substr($sourcePath, strlen($pathToUserFiles));	// Medlemsfiler/xx/Bilder/test.jpg
		$sourceThumbPath = $pathToThumbs490 . $sourceBasePath;				// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/Bilder/test.jpg

		$destBasePath  = substr($destPath, strlen($baseDir));				// Bilder/test.jpg
		$destRelPath	 = substr($destPath, strlen($pathToUserFiles));		// Medlemsfiler/xx/Bilder/test.jpg
		$destThumbPath = $pathToThumbs490 . $destBasePath;					// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/Bilder/test.jpg

		/* Fetch info about source directory */
			
			list($sourceDir,$sourceBasename) = $this->pathParts($sourcePath);
			$res = $this->query("SELECT id, owner, ownergroup FROM $tp WHERE fullslug=\"".addslashes($sourceDir)."\" ORDER BY id LIMIT 1");
			if ($res->num_rows != 1) $this->fatalError("Source dir '$sourceDir' not found");
			$row = $res->fetch_assoc();
			$sourcedir_id = intval($row['id']);
		
		/* Fetch info about destination directory */
			
			list($destDir,$destBasename) = $this->pathParts($destPath);			
			$res = $this->query("SELECT id, owner, ownergroup FROM $tp WHERE fullslug=\"".addslashes($destDir)."\" ORDER BY id LIMIT 1");
			if ($res->num_rows != 1) $this->fatalError("Dest dir '$destDir' not found");
			$row = $res->fetch_assoc();
			$destdir_id = intval($row['id']);

		/* Fetch info about source file */

			$res = $this->query("SELECT id, filename FROM $ti WHERE parent=$sourcedir_id AND filename=\"".addslashes($sourceBasename)."\" ORDER BY id LIMIT 1");
			if ($res->num_rows != 1) $this->fatalError("File '$sourceBasename' not found");
			$row = $res->fetch_assoc();
			$image_id = intval($row['id']);


		$this->query("UPDATE $ti SET parent=$destdir_id, filename=\"".addslashes($destBasename)."\" WHERE id=$image_id");
		if ($this->affected_rows() != 1) $this->fatalError("Could not move image $image_id!");

		//$this->logDebug("Rename 490xX thumb: $sourceThumbPath -> $destThumbPath");
		if (file_exists($sourceThumbPath)) rename($sourceThumbPath,$destThumbPath);
	}

	function fileDeleted($filePath,$baseDir) {

		/* Base dir may be: 
			- /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/
			- /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/
		*/
	
		$tp = $this->table_pages;
		$ti = $this->table_images;

		$pathToUserFiles = BG_WWW_PATH.$this->userFilesDir;  				// /Users/danmichael/Sites/18bergen/bergenvs/www/userfiles/
		$pathToThumbs490 = $baseDir.'_thumbs490/';							// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/

		$fileBaseDir 	 = substr($filePath, strlen($baseDir));				// Bilder/test.jpg
		$fileRelDir	 	 = substr($filePath, strlen($pathToUserFiles));		// Medlemsfiler/xx/Bilder/test.jpg
		$fileThumbPath   = $pathToThumbs490 . $fileBaseDir;					// /Users/danmichael/Sites/bergenvs/18bergen/www/userfiles/Medlemsfiler/xx/_thumbs490/Bilder/test.jpg

        /*
        $this->logDebug("File deleted: $filePath");
        $this->logDebug("  fileBaseDir: $fileBaseDir");
        $this->logDebug("  fileRelDir: $fileRelDir");
        $this->logDebug("  fileThumbPath: $fileThumbPath");
         */

		/* Lookup database entry for the file's directory */	
        list($sourceDir,$sourceBasename) = $this->pathParts($filePath);
        $res = $this->query("SELECT id, owner, ownergroup FROM $tp WHERE fullslug=\"".addslashes($sourceDir)."\" ORDER BY id LIMIT 1");
        if ($res->num_rows == 1) {
            $row = $res->fetch_assoc();
            $sourcedir_id = intval($row['id']);
    
            /* Lookup database entry for the file itself  */
            $res = $this->query("SELECT id, filename FROM $ti WHERE parent=$sourcedir_id 
                AND filename=\"".addslashes($sourceBasename)."\" ORDER BY id LIMIT 1");
            if ($res->num_rows == 1) {
                $row = $res->fetch_assoc();
                $image_id = intval($row['id']);
                
                $this->query("DELETE FROM $ti WHERE id=$image_id LIMIT 1");
                if ($this->affected_rows() != 1) $this->notSoFatalError("Could not remove the database entry for image $image_id!");

            } else {
                $this->logDebug("ckfinderplugin:fileDeleted: Image not found: $sourceBasename");
                return;
            }
        } else {
            $this->logDebug("ckfinderplugin:fileDeleted: Dir not found: $sourceDir");
            return;
        }
		
		//$sourceDir = substr($sourceDir,strpos($sourceDir,'/')+1);		
        //$this->logDebug("Tries to remove ".$fileThumbPath);

        // CKFinder deletes the file and the 100px thumb, but not the 490px thumb, so
        // let's delete it:
		if (file_exists($fileThumbPath)) {
			unlink($fileThumbPath);
		}
		
	}

}
?>
