<?

class images extends base {

	var $getvars = array("showimagedetails","cms_saveimagetitle","cms_editimagetitle","cms_replaceimage","cms_doreplaceimage",
		"uploadnewimage","douploadimage","cms_dodeleteimage","cms_deleteimage","showaslist","filterbyuser","current_image",
		"crop_profileimage","docrop_profileimage","crop_forumimage","docrop_forumimage","cropfornews","ajax_image_action",
		"newimg","savenewimg","ajax_image_action","ajax_image_id");

	var $userFilesDir = 'userfiles/';
	var $userFilesVirtDir = 'brukerfiler/';

	var $table_images = "images";
	var $table_dirs = "cms_pages";
	
	var $pathToCMS = "/lederverktoy/cms/";

	var $allow_viewimages = true;
	var $allow_addimages = true;
	var $allow_editownimages = true;
	var $allow_editothersimages = true;
	var $allow_deleteownimages = true;
	var $allow_deleteothersimages = true;
	var $userfilter = "*";
		
	var $relative_path;
	var $full_path;
	
	var $current_image = array();

	/**** FUNCTION LIST ***
	
		images ($dbLink, $table_dirs, $table_images);
		getFullPathToDir($id);
		getRelativePathToDir($id);
		findImageID($dirID, $caption, true);
		uploadImage($dirName, $varName);
				
				
	*********************** */

	function images($dblink, $table_dirs, $table_images, $pathToImages, $login_identifier, $temp_dir, $root_dir = ""){
		$this->login_identifier = $login_identifier;
		$this->table_dirs = DBPREFIX.$this->table_dirs;
		$this->table_images = DBPREFIX.$this->table_images;		
		if ($root_dir == "") {
			$f = explode("/",$_SERVER['SCRIPT_FILENAME']);
			array_pop($f);
			$this->full_path = rtrim(implode("/",$f),"/").$pathToImages;
			$this->absolute_temp_dir = rtrim(implode("/",$f),"/").$temp_dir;
		} else {
			$this->full_path = $root_dir.$pathToImages;		
			$this->absolute_temp_dir = $root_dir.$temp_dir;
		}
		
		// Check if dir exists "physically":
		$dirs = array(
			$this->absolute_temp_dir
		);
		foreach ($dirs as $d) { if (!file_exists($d)) if (!mkdir($d)) {
			print $this->notSoFatalError("Uh oh. Kunne ikke opprettet mappen: ".$d);
		}}

		$this->relative_path = $pathToImages;
		$this->setDbLink($dblink);
		$this->table_dirs = $table_dirs;
		$this->table_images = $table_images;
		$this->relative_temp_dir = $temp_dir;
	}
	
	function getPaths($row) {
		$dir = $row['parent'];
		$paths = array('thumb140' => array(), 'thumb490' => array(), 'full' => array());
		$f = stripslashes($row['filename']);
		if (empty($f)) {
			$virtual_image_dir = $this->getRelativePathToDir($dir,1); // version 1 (deprecated)
			$image_dir = $this->getFullPathToDir($dir);
			return array(
				'thumb140' => array(
					'virtual' => $virtual_image_dir."image".$row['id']."_thumb140.".$row['extension'],
					'real' => $image_dir."image".$row['id']."_thumb140.".$row['extension']
				),
				'thumb490' => array(
					'virtual' => $virtual_image_dir."image".$row['id']."_thumb490.".$row['extension'],
					'real' => $image_dir."image".$row['id']."_thumb490.".$row['extension']
				),
				'full' => array(
					'virtual' => $virtual_image_dir."image".$row['id'].".".$row['extension'],
					'real' => $image_dir."image".$row['id'].".".$row['extension']
				)
			);
		} else { 
			$virtual_image_dir = $this->getRelativePathToDir($dir,2); // version 2
			return array(
				'thumb140' => array(
					'virtual' => '/'.$this->userFilesDir.'_thumbs140/'.$virtual_image_dir.$f,
					'real' => BG_WWW_PATH.$this->userFilesDir.'_thumbs140/'.$virtual_image_dir.$f
				),
				'thumb490' => array(
					'virtual' => '/'.$this->userFilesDir.'_thumbs490/'.$virtual_image_dir.$f,
					'real' => BG_WWW_PATH.$this->userFilesDir.'_thumbs490/'.$virtual_image_dir.$f					
				),
				'full' => array(
					'virtual' => '/'.$this->userFilesDir.$virtual_image_dir.$f,
					'real' => BG_WWW_PATH.$this->userFilesDir.$virtual_image_dir.$f					
				)
			);
		}
	}
	
	function getFullPathToDir($id, $version = 1) {
		$res = $this->query(
			"SELECT fullslug FROM $this->table_dirs WHERE id='$id'"
		);
		$row = $res->fetch_assoc();

		if ($version == 1) 
			return $this->full_path . (empty($row['fullslug']) ? '' : $row['fullslug'].'/');
		else
			return BG_WWW_PATH.$this->userFilesDir.substr($row['fullslug'],strlen($this->userFilesVirtDir)).'/';
	}
	
	function getRelativePathToDir($id, $version = 1) {
		$res = $this->query(
			"SELECT fullslug FROM $this->table_dirs WHERE id='$id'"
		);
		$row = $res->fetch_assoc();

		if ($version == 1) 
			return $this->relative_path . $row['fullslug'].'/';
		else
			return substr($row['fullslug'],strlen($this->userFilesVirtDir)).'/';
	}
	
    function getFullPathToImage($id) {
        $id = intval($id);
		$res = $this->query("SELECT filename,parent FROM $this->table_images WHERE id=$id");
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			$filename = stripslashes($row['filename']);
        } else {
			return false;
        }
		$dir = $this->getFullPathToDir($row['parent'],2);
		return $dir.$filename;
	}
	
	function getRelativePathToImage($id, $thumbnail = false) {
		$res = $this->query("SELECT filename, parent FROM $this->table_images WHERE id='$id'");
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			if ($thumbnail)
				$filename = "image".$id."_thumb140.".$row['extension'];
			else
				$filename = $row['filename'];
		} else {
			return false;
		}
		$dir = $this->getRelativePathToDir($row['parent'],2);
		return $dir.$filename;
	}
	
	function findImageID($dirID, $caption, $createIfNecessary = false) {
		
		$caption = addslashes($caption);
		$res = $this->query("SELECT id FROM $this->table_images WHERE caption='$caption' AND parent='$dirID'");
		if ($res->num_rows == 1){
			$row = $res->fetch_assoc();
			return $row['id'];
		} else {
			if ($createIfNecessary) {
				$this->query(
					"INSERT INTO $this->table_images 
						(caption,timestamp,parent,uploader)
					VALUES ('$caption', '".time()."', '$dirID','$this->login_identifier')"
				);
				return $this->insert_id();
			} else {
				return false;
			}
		}
	}

	function pathParts($path) {

		$pathToUserFiles = BG_WWW_PATH.$this->userFilesDir;
		if (strpos($path,$pathToUserFiles) !== false) 
			$shortPath = substr($path, strlen($pathToUserFiles));		
		else if (strpos($path,$this->userFilesDir) !== false)
			$shortPath = substr($path, strlen($this->userFilesDir));		
		else
			$shortPath = $path;
		$tmp = explode("/",trim($shortPath,"/"));
		$basename = array_pop($tmp);
		$full_slug = $this->userFilesVirtDir.implode("/",$tmp);
		return array($full_slug,$basename);
	
	}
	
	function getImageId($fullslug) {
		list($dir,$basename) = $this->pathParts($fullslug);
		$tp = $this->table_dirs;
		$ti = $this->table_images;
		$res = $this->query("SELECT $ti.id FROM $ti,$tp 
			WHERE $tp.fullslug=\"".addslashes($dir)."\" 
			AND $tp.id=$ti.parent 
			AND $ti.filename=\"".addslashes($basename)."\"");
		if ($res->num_rows != 1) return 0;
		$row = $res->fetch_assoc();
		return intval($row['id']);
	}
	
	
	function createTempFileFrom($file_path) {
		$file_extension = $this->file_extension($file_path);
		$tempname = "";
		while (strlen($tempname) < 15) {
		
			// Get random number b/w 48 & 122 (0 - Z)
			$rnd = rand(48,122);
		
			// Limit string to 0-9, a-z, A-Z
			if (($rnd < 57 || $rnd > 65) && ($rnd < 90 || $rnd > 97))
				$tempname .= chr($rnd);
		}
		// 15 chars gives 768,909,704,948,766,668,552,634,368 possible combinations on UNIX (case sensitive)

		// Make it a little more readable
		$tempname = substr($tempname,0,5) . "-" . substr($tempname,5,5) . "-" . substr($tempname,10,5) . "." . $file_extension;
		
        $temp_path = $this->absolute_temp_dir.$tempname;

		copy($file_path,$temp_path);
		return $tempname;
	}
	
	/****************************************************************************************************
		CREATE THUMBNAIL
		**************************************************************************************************/


	/* $img can be either the image id or a filename */
	function createThumbnail($img, $keepProportions, $newWidth, $newHeight = 0, $filename_addition = "_thumb") {
		
		if (is_numeric($img)) {
			$filename = $this->getFullPathToImage($img);
		} else {
			$filename = $img;
		}
		
		list($oldWidth, $oldHeight, $type, $attr) = getimagesize($filename);
		$file_extension = ".".$this->file_extension($filename);

		if ($newWidth <= 0 && $newHeight <= 0) $this->fatalError("Can't create thumbnail with negative dimensions");
		
		if ($keepProportions){
			
			if ($newHeight <= 0) {
				$scalePercent = $newWidth / $oldWidth;
			} else if ($newWidth <= 0) {
				$scalePercent = $newHeight / $oldHeight;
			} else {
				$xScale = $newWidth / $oldWidth;
				$yScale = $newHeight / $oldHeight;
				$scalePercent = ($xScale > $yScale) ? $yScale : $xScale;
			}
			$newWidth = $oldWidth * $scalePercent;
			$newHeight = $oldHeight * $scalePercent;
		
		} else {
		
			if ($newHeight <= 0) $newHeight = $newWidth;		
			if ($newWidth <= 0) $newWidth = $newHeight;
			
		}
		
		$filename_parts = pathinfo($filename);
		
		if (empty($filename_addition)) {
			$thumb_filename = $filename;
		} else {
			$thumb_filename = $filename_parts['dirname']."/".basename($filename,$file_extension).$filename_addition.$file_extension;
			copy($filename,$thumb_filename);
				
			// Sett permissions
			/*
			$old = umask(0);
			chmod($thumb_filename, 0666);
			umask($old);
			*/
		}
		
		if ($newWidth < $oldWidth || $newHeight < $oldHeight) {
			$this->resizeImage($thumb_filename, $newWidth, $newHeight);
		}

	}
	
	function resizeImage($filename, $newWidth, $newHeight) {
				
		list($oldWidth, $oldHeight, $type, $attr) = getimagesize($filename);
		$file_extension = strtolower($this->file_extension($filename));

		switch ($file_extension) {
			case 'jpg': case 'jpeg':	$src = imagecreatefromjpeg($filename); break;
			case 'png':					$src = imagecreatefrompng($filename); break;
			case 'gif':					$src = imagecreatefromgif($filename); break;
			default:					$this->fatalError("Couldn't load $filename. Unknown filetype.");		
		}
		
		$dst = imagecreatetruecolor($newWidth, $newHeight); // Note: This function requires GD 2.0.1 or later (2.0.28 or later is recommended).

		imagealphablending($dst, false);
		imagesavealpha($dst,true);
		$transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
		imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
		imagecopyresampled($dst, $src,0,0,0,0,$newWidth,$newHeight,$oldWidth,$oldHeight); 
				
		switch ($file_extension) {
			case 'jpg': case 'jpeg':	imagejpeg($dst, $filename, 75); break;
			case 'png':					imagepng($dst, $filename); break;
			case 'gif':					
				imagetruecolortopalette($dst, true, 256);
				imagegif($dst, $filename); 
				break;
			default:					$this->fatalError("Couldn't save $filename. Unknown filetype.");		
		}
		
	}

	/****************************************************************************************************
		CROP IMAGE
		**************************************************************************************************/
		
	function cropImage($filename, $x, $y, $newWidth, $newHeight) {
	
		list($oldWidth, $oldHeight, $type, $attr) = getimagesize($filename);
		$file_extension = strtolower($this->file_extension($filename));

		switch ($file_extension) {
        case 'jpg': case 'jpeg':    	$src = imagecreatefromjpeg($filename); break;
			case 'png':					$src = imagecreatefrompng($filename); break;
			case 'gif':					$src = imagecreatefromgif($filename); break;
			default:					$this->fatalError("Couldn't load $filename. Unknown filetype.");		
		}
		
		$dst = imagecreatetruecolor($newWidth, $newHeight); // Note: This function requires GD 2.0.1 or later (2.0.28 or later is recommended).
		imagecopyresampled($dst, $src,0,0,$x,$y,$newWidth,$newHeight,$newWidth,$newHeight); 
		
		switch ($file_extension) {
			case 'jpg': case 'jpeg':	imagejpeg($dst, $filename, 75); break;
			case 'png':					imagepng($dst, $filename); break;
			case 'gif':					
				imagetruecolortopalette($dst, true, 256);
				imagegif($dst, $filename); 
				break;
			default: 
				$this->fatalError("Couldn't save $filename. Unknown filetype.");		
		}
	
	}
	
	// Set $forhold to false to allow free cropping
	function outputCropForm($id, $forhold = false, $returnOutput = false) {
		
		$max_width = 465;
		$img_fullpath = $this->getFullPathToImage($id);
        $img_relativepath = $this->getRelativePathToImage($id);

        if (!$img_fullpath) {
            $this->fatalError("Fant ikke bildet med id $id");
        }
		
		$temp_filename = $this->createTempFileFrom($img_fullpath);

		list($width, $height, $type, $attr) = getimagesize($this->absolute_temp_dir.$temp_filename);
		
		if ($width > $max_width) {
			$tmp_width = $max_width;
			$tmp_height = $tmp_width / $width * $height;			
			$this->resizeImage($this->absolute_temp_dir.$temp_filename, $tmp_width, $tmp_height);
		} else {
			$tmp_width = $width;
			$tmp_height = $height;
		}
				
		$image_original = $img_relativepath;
		$image_cropped = $this->relative_temp_dir.$temp_filename;
		
		$ideeltForhold = $forhold;
		$forhold = round($width / $height,2);
		$avvik = abs($forhold - $ideeltForhold);
		
		if ($forhold == false) {
			$info = "";
					
		} else if ($forhold < 1 && $width > $height) {
			$info = "Bildet er i breddeformat. Du bør absolutt beskjære det. Ellers vil det fremstå som strukket. 
				Den beskjærte ruten vil få riktig forhold ($ideeltForhold).
			";
			
		} else if ($forhold > 1 && $width < $height) {
			$info = "Bildet er i høydeformat. Du bør absolutt beskjære det. Ellers vil det fremstå som strukket. 
				Den beskjærte ruten vil få riktig forhold ($ideeltForhold).
			";
		
		} else if ($avvik < 0.01) {
			$info = "Bildet har riktig forhold (det er $forhold ganger så bredt som høyt). 
				Du trenger ikke beskjære bildet, men kan gjøre det hvis du vil bruke kun et mindre utsnitt av det.
				"; 		

		} else if ($avvik < 0.5) {
			$info = "Bildet har tilnærmet riktig forhold. Det er $forhold ganger så bredt som høyt, mens forholdet ideelt sett bør være $ideeltForhold. 
				Du trenger ikke beskjære bildet, men kan godt gjøre det hvis du vil ha best mulig resultat.
				"; 

		} else if ($avvik > 0.2) {
			$info = "Bildet har forholdet $forhold (det er $forhold ganger så bredt som høyt), mens det ideelt sett bør være $ideeltForhold.
				Du bør beskjære bildet, ellers vil det fremstå som litt strukket. Den beskjærte ruten vil automatisk få riktig forhold.
			";

		} else {
			$info = "Du bør absolutt beskjære det. Ellers vil det fremstå som strukket. 
				Den beskjærte ruten vil få riktig forhold ($ideeltForhold)."; 
		}
			
		
		$output = '
	
			<script type="text/javascript">
		    //<![CDATA[				
				
				/************************************************************************************************************
				(C) www.dhtmlgoodies.com, April 2006
				
				This is a script from www.dhtmlgoodies.com. You will find this and a lot of other scripts at our website.	
				
				Terms of use:
				You are free to use this script as long as the copyright message is kept intact. However, you may not
				redistribute, sell or repost it without our permission.
				
				Thank you!
				
				www.dhtmlgoodies.com
				Alf Magne Kalleland
				
				************************************************************************************************************/	
			
				
				/* Variables you could modify */
				
				var small_square_gif = "/images/image-crop/small_square.gif";
				var transparent_gif = "/images/image-crop/transparent.gif";
				
				var crop_script_server_file = "/crop_image.php";
				
				var cropToolBorderWidth = 1;	// Width of dotted border around crop rectangle
				var smallSquareWidth = 7;	// Size of small squares used to resize crop rectangle
				
				// Size of image shown in crop tool
				var crop_imageWidth = '.$tmp_width.';
				var crop_imageHeight = '.$tmp_height.';
				
				// Size of original image
				var crop_originalImageWidth = '.$width.';
				var crop_originalImageHeight = '.$height.';
				
				var crop_minimumPercent = 10;	// Minimum percent - resize
				var crop_maximumPercent = 200;	// Maximum percent -resize
								
				var crop_minimumWidthHeight = 15;	// Minimum width and height of crop area
				
				var updateFormValuesAsYouDrag = false;	// This variable indicates if form values should be updated as we drag. This process could make the script work a little bit slow. That\'s why this option is set as a variable.
				if(!document.all) updateFormValuesAsYouDrag = false;	// Enable this feature only in IE
				
				'.(($ideeltForhold === false) ? '
				var crop_script_alwaysPreserveAspectRatio = false;
				var crop_script_fixedRatio = false;	 // Fixed aspect ratio(example of value: 1.5). Width of cropping area relative to height(1.5 means that the width is 150% of the height)
										 // Set this variable to false if you don\'t want a fixed aspect ratio
				':'
				var crop_script_alwaysPreserveAspectRatio = true;
				var crop_script_fixedRatio = '.$ideeltForhold.';	 // Fixed aspect ratio(example of value: 1.5). Width of cropping area relative to height(1.5 means that the width is 150% of the height)
										 // Set this variable to false if you don\'t want a fixed aspect ratio
				').'
				/* End of variables you could modify */
				
    		//]]>
			</script>
	
			<script type="text/javascript" src="/jscript/image-crop.js"></script>
	
			<input type="hidden" id="input_image_ref" value="'.$image_original.'">
			<input type="hidden" class="textInput" name="crop_x" id="input_crop_x">
			<input type="hidden" class="textInput" name="crop_y" id="input_crop_y">
			<input type="hidden" class="textInput" name="crop_width" id="input_crop_width">
			<input type="hidden" class="textInput" name="crop_height" id="input_crop_height">
			<input type="hidden" class="textInput" name="crop_percent_size" id="crop_percent_size" value="100">

			<p>
				Her kan du beskjære bildet du valgte (lage et utsnitt) hvis du vil. 
				Det gjør du isåfall ved å trykke og dra de små firkantene i hjørnene til bildet.
				Det er to grunner til å beskjære et bilde:
			</p>
			<ul>
				<li>Du kan skjære til riktig forhold</li>
				<li>Du kan fjerne andre personer eller annet som ikke passer inn</li>
			</ul>
			<p>
				Bildet du har lastet opp er <span id="label_dimension"></span> px. '.$info.'
			</p>	
			
			<div class="crop_content">
				<div id="imageContainer">
					<img src="'.$image_cropped.'" style="width:'.$tmp_width.'px; height:'.$tmp_height.'px;" />
				</div>
			</div>
			
			<p style="clear:both;">
				Det kan være du må holde musen nede litt før markeringen blir oppdatert.
				Når du er ferdig trykker du på "Lagre bilde".
				Hvis du ikke ønsker å beskjære bildet trykker du bare på "Lagre bilde" med en gang.
			</p>
			<p>
				<input type="submit" value="Lagre bilde" />
			</p>
			
			<div id="crop_progressBar">
			
			</div>
									
			<script type="text/javascript">
			  init_imageCrop();
			</script>

		';
		return $output;	
	}
	
	

/*

		// Resize
		$bpath = $u1->directory.$u1->filename.$u1->file_extension;
		$iedit = new imageeditor();
		if ($iedit->gd){
			list($width, $height, $type, $attr) = getimagesize($bpath);
			if (($width > $w) || ($h != 0)) {
				$iedit->load($bpath);
				if ($h == 0){
					$forhold = $w/$width;
					$h = $height*$forhold;
				}
				$iedit->createthumb($w,$h);
				$iedit->save($bpath);
			}
		}
		$this->createthumb($bpath,$u1->directory.$u1->filename."_thumb".$u1->file_extension,100,100,true);
		return $u1->file_extension;

*/

	function file_extension($f){
		$t = explode(".",$f);
		return array_pop($t);
	}

	function getThumbDimensions($oldWidth,$oldHeight,$newWidth,$newHeight){
		if (($oldWidth == 0) || ($oldHeight == 0)){
			return array(0,0);
		}
		$xScale = $newWidth/$oldWidth;
		$yScale = $newHeight/$oldHeight;
		$scalePercent = (($xScale < $yScale) ? $xScale : $yScale);
		$newWidth = $oldWidth*$scalePercent;
		$newHeight = $oldHeight*$scalePercent;
		return array($newWidth,$newHeight);
	}

	function printImageDetails($id){
		$rs = $this->query(
			"SELECT id,filename,parent,extension,caption,size,timestamp,width,height,uploader FROM $this->table_images WHERE id=$id");
		if ($rs->num_rows != 1){ $this->notSoFatalError("Bildet finnes ikke!"); return 0; }
		$row = $rs->fetch_assoc();
		$w = $row["width"];
		$h = $row["height"];
		$uploader = call_user_func($this->make_memberlink, $row['uploader']);
		$dir = $row['parent'];
		$filename = stripslashes($row['filename']);

		$allowedit = (($this->allow_editownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_editothersimages);
		$allowdelete = (($this->allow_deleteownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_deleteothersimages);
				
		if (empty($filename)) {
		
			$virtual_image_dir = $this->getRelativePathToDir($dir,2);
			$real_image_dir = $this->getFullPathToDir($dir,2);
			$filename = "image".$row['id'].".".$row['extension'];
			$filename100 = "image".$row['id']."_thumb140.".$row['extension'];
			$filename490 = "image".$row['id']."_thumb490.".$row['extension'];

			$src = $virtual_image_dir.$filename;
			$src490 = $virtual_image_dir.$filename490;
			$full_src = $real_image_dir.$filename;
			$full_src100 = $real_image_dir.$filename100;
			$full_src490 = $real_image_dir.$filename490;
			
		} else {

			$virtual_image_dir = $this->getRelativePathToDir($dir,2);

			$src = '/'.$this->userFilesDir.$virtual_image_dir.$filename;
			$src490 = '/'.$this->userFilesDir.'_thumbs490/'.$virtual_image_dir.$filename;
			$full_src = BG_WWW_PATH.$this->userFilesDir.$virtual_image_dir.$filename;
			$full_src100 = BG_WWW_PATH.$this->userFilesDir.'_thumbs140/'.$virtual_image_dir.$filename;
			$full_src490 = BG_WWW_PATH.$this->userFilesDir.'_thumbs490/'.$virtual_image_dir.$filename;
		
		}
		
		$caption = stripslashes($row["caption"]);
		
		list($width100, $height100, $type, $attr) = getimagesize($full_src100);
		list($width390, $height490, $type, $attr) = getimagesize($full_src490);
		
		$width = $row['width'];
		$height = $row['height'];
		
		if (!file_exists($full_src490)) {
			$src = $this->image_dir."notfound100.jpg";
			$width390 = 100;
			$height490 = 100;
		}
		
		$filesize_org = round($row['size']/1024);
		$filesize_490 = round(filesize($full_src490)/1024);
		$filesize_100 = round(filesize($full_src100)/1024);
		
		$forhold = (round($width/$height*100)/100)." (bredde/høyde)";
		
		return '
			<h2>Bilde: '.$caption.'</h2>
			<div class="epEditHolder">
				<table width="100%"><tr><td valign="top" width="55%">
					BB-kode: [image='.$id.']<br />
					Filnavn: '.$filename.'<br />
					Lastet opp: '.date("d. M Y",$row["timestamp"]).'<br />
					Lastet opp av: '.$uploader.'
				</td><td valign="top" width="50%">
					Format: '.$row["extension"].'<br />
					Original: '.$width.'x'.$height.' px ('.$filesize_org.' kB)<br />
					Tilpasset: '.$width390.'x'.$height490.' px ('.$filesize_490.' kB)<br />
					Thumbnail: '.$width100.'x'.$height100.' px ('.$filesize_100.' kB)<br />
					Forhold: '.$forhold.'
				 </td></tr></table>
				 <p align="center">
					<a href="'.$src.'"><img src="'.$src490.'" width="'.$width390.'" height="'.$height490.'" alt="'.$caption.'" style="border: none;" /></a>
				</p>
				<p>
					'.($allowedit ? '<a href="'.$this->generateURL(array('current_image='.$row['id'],'cms_editimagetitle')).'" class="icn" style="background-image:url(/images/icns/textfield.png);">Endre tittel</a>
					<a href="'.$this->generateURL(array('current_image='.$row['id'],'cropimage')).'" class="icn" style="background-image:url(/images/crop_icon.png);">Beskjær</a> 
					<a href="'.$this->generateURL(array('current_image='.$row['id'],'cms_replaceimage')).'" class="icn" style="background-image:url(/images/icns/picture_edit.png);">Erstatt bilde med et nytt</a> ' : '').'
					'.($allowdelete ? '<a href="'.$this->generateURL(array('current_image='.$row['id'],'cms_deleteimage')).'" class="icn" style="background-image:url(/images/icns/picture_delete.png);">Slett bilde</a> ' : '').'
				</p>
				<p>
					<a href="'.$this->generateURL('').'" class="icn" style="background-image:url(/images/icns/arrow_up.png);">Tilbake til oversikt</a>
				</p>
			</div>
		';
	}

	function printImageList(){
		$ml = $this->memberlookup_function;
		$vishva = (
			($this->userfilter != "*") ? 
				($this->showThumbs ? "<a href='".$this->generateURL("")."'>Vis alle</a>" : "<a href='".$this->generateURL("showaslist=true")."'>Vis alle</a>" )
				: 
				($this->showThumbs ? "<a href='".$this->generateURL("filterbyuser=$this->login_identifier")."'>Vis bare mine</a>" : "<a href='".$this->generateURL(array("filterbyuser=$this->login_identifier","showaslist=true"))."'>Vis bare mine</a>")
		);
		$visthumbs = (
			$this->showThumbs ? 
				(($this->userfilter != "*") ? "<a href='".$this->generateURL(array("filterbyuser=$this->userfilter","showaslist=true"))."'>Vis som liste</a>" : "<a href='".$this->generateURL("showaslist=true")."'>Vis som liste</a>")
				: 
				(($this->userfilter != "*") ? "<a href='".$this->generateURL("filterbyuser=$this->userfilter")."'>Vis som bilder</a>" : "<a href='".$this->generateURL("")."'>Vis som bilder</a>")
		);
		print "<h1>Opplastede bilder</h1>
		<p>".(($this->userfilter != "*") ? "Viser bare bilder lastet opp av ".$ml($this->userfilter) : "")."</p>
			<p>
				$vishva |
				$visthumbs
				".($this->allow_addimages ? " | <a href='".$this->generateURL("uploadnewimage=true")."'>Last opp et nytt bilde</a>" : "")."
			</p>";
		if ($this->showThumbs){
				
			print("<h2>Nyhetsbilder</h2>\n");
			$rs = $this->query(
				"SELECT id,filename,caption,size,timestamp,width,height FROM $this->table_images WHERE category='news'".
				(($this->userfilter != "*") ? " AND uploader='$this->userfilter'" : "")
			);
			if ($rs->num_rows == 0){
				print("<i>Ingen bilder lastet opp enda</i>\n");
			} else {
				while ($row = $rs->fetch_assoc()){
					print "<a href='".$this->generateURL("showimagedetails=".$row['id'])."'><img src=\"fetchnewsimage.php?filename=".$row["filename"]."\" alt=\"".stripslashes($row["caption"])."\" title=\"".stripslashes($row["caption"])."\" style=\"width: 100px; height: 80px; border: 1px solid #000000; margin: 3px; padding: 0px;\" /></a>";
				}
			}
			print("<h2>Andre bilder</h2>\n");
			$rs = $this->query(
				"SELECT id,filename,caption,size,timestamp,width,height FROM $this->table_images WHERE category='other'".
				(($this->userfilter != "*") ? " AND uploader='$this->userfilter'" : "")
			);
			if ($rs->num_rows == 0){
				print("<i>Ingen bilder lastet opp enda</i>\n");
			} else {
				while ($row = $rs->fetch_assoc()){
					print "<a href='".$this->generateURL("showimagedetails=".$row['id'])."'><img src=\"fetchfile.php?group=general&amp;src=".$row["id"]."\" alt=\"".stripslashes($row["caption"])."\" title=\"".stripslashes($row["caption"])."\" style=\"width: ".$row['width']."px; height: ".$row['height']."px; border: 1px solid #000000; margin: 3px; padding: 0px;\" /></a>";
				}
			}

		} else {
			
			print("<h2>Nyhetsbilder</h2>\n");
			$rs = $this->query(
				"SELECT id,filename,caption,size,timestamp,width,height FROM $this->table_images WHERE category='news'".
				(($this->userfilter != "*") ? " AND uploader='$this->userfilter'" : "")
			);
			if ($rs->num_rows == 0){
				print("<i>Ingen bilder lastet opp enda</i>\n");
			} else {
				print("<ul>\n");
				while ($row = $rs->fetch_assoc()){
					print("<li><a href='".$this->generateURL("showimagedetails=".$row['id'])."'>".$row["caption"]."</a> (".date("d.m.y",$row["timestamp"]).")</li>\n");
				}
				print("</ul>\n");
			}
			print("<h2>Andre bilder</h2>\n");
			$rs = $this->query(
				"SELECT id,filename,caption,size,timestamp,width,height FROM $this->table_images WHERE category='other'".
				(($this->userfilter != "*") ? " AND uploader='$this->userfilter'" : "")
			);
			if ($rs->num_rows == 0){
				print("<i>Ingen bilder lastet opp enda</i>\n");
			} else {
				print("<ul>\n");
				while ($row = $rs->fetch_assoc()){
					print("<li><a href='".$this->generateURL("showimagedetails=".$row['id'])."'>".$row["caption"]."</a> (".date("d.m.y",$row["timestamp"]).")</li>\n");
				}
				print("</ul>\n");
			}
		}
	}
	
	/****************************************************************************************************
		EDIT IMAGE TITLE
		**************************************************************************************************/

	function printImageTitleForm($id){
		$output = "<h2>Endre bildetittel</h2>";
		$rs = $this->query("SELECT id,parent,filename,extension,caption,size,timestamp,width,height,uploader FROM $this->table_images WHERE id='$id'");
		$row = $rs->fetch_assoc();
		$allowedit = (($this->allow_editownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_editothersimages);
		if (!$allowedit) return $this->permissionDenied(); 
		$caption = stripslashes($row['caption']);
		
		$paths = $this->getPaths($row);
		
		list($width, $height, $type, $attr) = getimagesize($paths['thumb140']['real']);
		
		$url_back = $this->generateURL("current_image=$id");
		$url_post = $this->generateURL(array("current_image=$id","cms_saveimagetitle","noprint=true"));
		$output .= '
			<h3>'.$caption.'</h3>
			<p align="center">
				<img src="'.$paths['thumb140']['virtual'].'" width="'.$width.'" height="'.$height.'" alt="'.$caption.'" />
			</p>
			<form method="post" action="'.$url_post.'">
				Ny tittel: <input name="newtitle" type="text" value="'.$caption.'" size="48" /><br /><br />
				<input type="button" value="Avbryt" onclick=\'location="'.$url_back.'"\' /> 
				<input type="submit" name="lagre" value="Lagre" />
			</form>
		';
		return $output;
	}

	function saveNewImageTitle($id){
		$rs = $this->query("SELECT uploader FROM $this->table_images WHERE id='$id'");
		$row = $rs->fetch_assoc();
		$allowedit = (($this->allow_editownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_editothersimages);
		if (!$allowedit){ $this->permissionDenied(); return 0; }
		$bildenavn = addslashes($_POST["newtitle"]);
		if (empty($bildenavn)){ $this->notSofatalError("Vennligst skriv inn en tittel!"); return 0; }			
		$this->query("UPDATE $this->table_images SET caption='$bildenavn' WHERE id='$id'");
	}

	/****************************************************************************************************
		REPLACE IMAGE
		**************************************************************************************************/

	function printReplaceImageForm($id){

		$rs = $this->query("SELECT parent,id,extension,caption,size,timestamp,width,height,uploader FROM $this->table_images WHERE id='$id'");
		if ($rs->num_rows != 1) $this->fatalError("bildet finnes ikke!");
		$row = $rs->fetch_assoc();
		$allowedit = (($this->allow_editownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_editothersimages);
		if (!$allowedit){ $this->permissionDenied(); return 0; }
		$caption = stripslashes($row["caption"]);		
		
		$dir = $row['parent'];
		$virtual_image_dir = $this->getRelativePathToDir($dir);
		$real_image_dir = $this->getFullPathToDir($dir);
		
		$filename = "image".$row['id'].".".$row['extension'];
		$filename100 = "image".$row['id']."_thumb140.".$row['extension'];
		$src100 = $virtual_image_dir.$filename100;
		$full_src100 = $real_image_dir.$filename100;		
		
		list($width, $height, $type, $attr) = getimagesize($full_src100);
		
		$url_back = $this->generateURL("current_image=$id");
		$url_post = $this->generateURL(array("noprint=true","current_image=$id","cms_doreplaceimage"));
		return '
			<h2>Erstatt bilde</h2>
			<h3>'.$caption.'</h3>
			<p align="center">
				<img src="'.$src100.'" width="'.$width.'" height="'.$height.'" alt="'.$caption.'" />
			</p>
			<div class="epEditHolder">
				<form enctype="multipart/form-data" action="'.$url_post.'" method="post">
					
					Her kan du erstatte bildet med et nytt. På denne måten beholder du eventuelle referanser til bildet.<br /><br />

					Nytt bilde: <input name="bildefil" type="file" size="50" /><br /><br />
					
					<input type="button" value="Avbryt" onclick="location="'.$url_back.'"" /> 
					<input type="submit" name="lagre" value="Last opp" />
				</form>
			</div>
		';
	}

	/****************************************************************************************************
		DELETE IMAGE
		**************************************************************************************************/

	function printDeleteImageForm($id){
		$res = $this->query("SELECT id,parent,caption,uploader,width,height,extension FROM $this->table_images WHERE id='$id'");
		if ($res->num_rows != 1) $this->fatalError("Bildet eksisterer ikke!"); 
		$row = $res->fetch_assoc();
		$allowdel = (($this->allow_deleteownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_deleteothersimages);
		if (!$allowdel) return $this->permissionDenied(); 
		$caption = stripslashes($row['caption']);
		
		$dir = $row['parent'];
		$virtual_image_dir = $this->getRelativePathToDir($dir);
		$real_image_dir = $this->getFullPathToDir($dir);
		
		$filename = "image".$row['id'].".".$row['extension'];
		$filename100 = "image".$row['id']."_thumb140.".$row['extension'];
		$src100 = $virtual_image_dir.$filename100;
		$full_src100 = $real_image_dir.$filename100;		
		
		list($width, $height, $type, $attr) = getimagesize($full_src100);

		$url_back = $this->generateURL("current_image=$id");
		$url_post = $this->generateURL(array("current_image=$id","noprint=true","cms_dodeleteimage"));
		
		return '<h2>Slette bilde?</h2>
			<p>
				Er du sikkert på at du vil slette bildet "'.$caption.'"? Du bør ikke slette bilder om det 
				eksisterer referanser til det (f.eks. hvis du har brukt det på nyhetssiden)!
			</p>
			<p align="center">
				<img src="'.$src100.'" width="'.$width.'" height="'.$height.'" alt="'.$caption.'" />
			</p>
			<form method="post" action="'.$url_post.'">
				<input type="submit" value="     Ja      " /> 
				<input type="button" value="     Nei      " onclick="window.location=\'"'.$url_back.'"\' />
			</form>
		';
	}

	function deleteImage($id){
		$res = $this->query("SELECT caption,uploader FROM $this->table_images WHERE id='$id'");
		if ($res->num_rows != 1) $this->fatalError("Bildet eksisterer ikke!"); 
		$row = $res->fetch_assoc();
		$allowdel = (($this->allow_deleteownimages && ($row['uploader'] == $this->login_identifier)) || $this->allow_deleteothersimages);
		if (!$allowdel){ $this->permissionDenied(); return 0; }
		$path = $this->getFullpathToImage($id);
		$base = substr($path,0,strrpos($path,"."));
		$ext = substr($path,strrpos($path,"."));
		unlink($base.$ext);
		unlink($base."_thumb490".$ext);
		unlink($base."_thumb140".$ext);
		$this->query("DELETE FROM $this->table_images WHERE id='$id'");
	}
	
	/****************************************************************************************************
		AJAX IMAGE SELECTOR
		**************************************************************************************************/

	
	function listImagesIntro($dir, $identifier) {
		$default = $_SESSION['ajax_imageselector_defaultimage'];
		$firstImage = $_SESSION['ajax_imageselector_firstimage'];
		$count = $_SESSION['ajax_imageselector_imagesperpage'];
		return '
				
			<div id="ajaximagelist">
				'.$this->listImages($dir, $default, $identifier, $firstImage, $count).'
			</div>
			
			<script type="text/javascript">
			//<![CDATA[
				selectImage(default_image);
			//]]>
			</script>
		';
	}
	
	function listImages($dir, $default, $identifier, $firstImage = 0, $count = 12, $includeRoot = false){
		
		$_SESSION['ajax_imageselector_firstimage'] = $firstImage;

		$res = $this->query(
			"SELECT COUNT(id) as imagecount FROM $this->table_images WHERE parent='$dir'"
		);
		$row = $res->fetch_assoc();
		$imageCount = $row['imagecount'];
		
		$res = $this->query("SELECT 
				id,caption,extension
			FROM $this->table_images
			WHERE parent='$dir'
			ORDER BY id DESC
			LIMIT $firstImage, $count"
		);
		$rowCount = $res->num_rows;
		if ($rowCount == 0) {
			$theList = "
				<p style='margin:0px;'>
					Denne bildemappen er tom
					<span id='imagelistmoreimages'>
						<a href='#' onclick='uploadNewForm(); return false;' style='font-weight: bold; border: 1px solid #ddd; padding: 3px;'>Last opp nytt bilde</a>
					</span>
				</p>
				<p class='imagethumbs'></p>
				<input type='hidden' id='current_images' value='' />
				<div style='clear:both;'></div>
			";
			return $theList;
		}
		$til = $firstImage + $count;
		$til = ($til > $imageCount) ? $imageCount : $til;
		$theList = "
			<div style='margin:0px;font-weight:bold;border-bottom:1px solid #bbb;padding:4px;height:28px;'>
				<div style='float:right;'>
		";
		if ($firstImage == 0 && $til == $imageCount) { 
			$theList .= "
				<span id='imagelistmoreimages'>
					<a href='#' onclick='uploadNewForm(); return false;' style='font-weight: bold; border: 1px solid #ddd; padding: 3px;'>Last opp nytt bilde</a>
				</span>
			";		
		} else {
			$theList .= '
				<span id="imagelistmoreimages">
					<a href="#" onclick="prevImages(); return false;" style="font-weight: bold; border: 1px solid #ddd; padding: 4px; background:white;">&lt;&lt; Tilbake</a>
					<a href="#" onclick="nextImages(); return false;" style="font-weight: bold; border: 1px solid #ddd; padding: 4px; background:white;">Frem &gt;&gt;</a>
					<a href="#" onclick="uploadNewForm(); return false;" style="font-weight: bold; border: 1px solid #ddd; padding: 4px; background:white;">Last opp nytt bilde</a>
				</span>
			';
		}	
		$theList .= "
				</div>
				Viser bilde ".($firstImage+1)." til ".$til." av totalt $imageCount bilder.
			</div>
			<p class='imagethumbs'>
		";
		$classNo = 1;
		
		$image_dir = $this->getFullPathToDir($dir);
		$virtual_image_dir = $this->getRelativePathToDir($dir);		
		
		$listedImages = array();
		while ($row = $res->fetch_assoc()){
			
			$id = $row['id'];
			$listedImages[] = $id;
			$filename = 'image'.$id.'_thumb140.'.$row['extension'];
			$path = $image_dir.$filename;
			$src = $virtual_image_dir.$filename;

			if (file_exists($path)){
				list($width, $height, $type, $attr) = getimagesize($path);
				$ml = (floor((102-$width)/2))."px";
				$mt = (floor((102-$height)/2))."px";
			} else {
				$ml = "1px";
				$mt = "1px";
				$width = 100;
				$height = 100;
				$src = $this->image_dir."notfound100.jpg";
			}
			if ($includeRoot) $src = ROOT_DIR.$src;
									
			$url = $this->generateURL("current_image=".$row['id']);
			$theList .= sprintf('<a href="%s" id="%s" onclick="selectImage(%d); return false;"><img id="%s" src="%s" alt="%s" style="padding: %d %d %d %d; width: %dpx; height: %dpx;" /></a>',
				$url,'listedimagelink'.$id,$id,'listedimage'.$id,$src,htmlspecialchars($row['caption']),$mt,$ml,$mt,$ml,$width,$height);
			
		}
		$listedImages = implode(",",$listedImages);
		
		$theList .= '
			</p>

			<input type="hidden" id="current_images" value="'.$listedImages.'" />

			<div style="clear:both;"></div>
			
		';
		return $theList;
		
	}
	
	function makeAjaxImageListing($dir, $default = -1, $identifier = 'selected_image', $imagesPerPage = 12) {
		
		$res = $this->query(
			"SELECT COUNT(id) as imagecount FROM $this->table_images WHERE parent='$dir'"
		);
		$row = $res->fetch_assoc();
		$imageCount = $row['imagecount'];

		if ($default != -1) {
			$res = $this->query(
				"SELECT COUNT(id) as imagecount FROM $this->table_images WHERE parent='$dir' AND id > $default"
			);
			$row = $res->fetch_assoc();
			$firstImage = floor($row['imagecount']/$imagesPerPage)*$imagesPerPage;
		} else {
			$firstImage = 0;
		}
				
		return '
			
			<!-- BEGIN makeAjaxImageListing -->
			
			<input type="hidden" name="'.$identifier.'" id="selected_image" value="'.$default.'" />
				
			<div id="ajaximagelist">
				'.$this->listImages($dir, $default, $identifier, $firstImage, $imagesPerPage).'
			</div>
			<script type="text/javascript">
		    //<![CDATA[

				var first_image = '.$firstImage.';
				var default_image = '.$default.';
				var image_count = '.$imageCount.';
				
				//selectImage(default_image);
				/*
				function moreImages() {
					//alert("more images BEGIN");
					first_image += '.$imagesPerPage.';
					if (first_image >= image_count) first_image = 0;
					var url = "'.$this->generateURL(array("noprint=true","list_images"),true).'";				
					var pars = new Array();
					pars.push("first_image="+first_image);
					pars.push("default_image="+default_image);
					pars = pars.join("&");
					var success = function(t){ 
						setText("ajaximagelist",t.responseText);
						selectImage(default_image);
					}
					setText("imagelistmoreimages", "<img src=\"'.$this->image_dir.'progressbar1.gif\" style=\"width:100px; height:9px\" />");
					var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
					//alert("more images END");
				}

				function selectImage(id) {
					//alert("select image: "+id+" BEGIN");
					$("selected_image").value = id;
					default_image = id;
					var listedImages = $F("current_images").split(",");
					
					var foundInArray = false;
					for (var i = 0; i < listedImages.length; i++)
						if (listedImages[i] == id) foundInArray = true;
					if (!foundInArray)
						return;
						
					for (var i = 0; i < listedImages.length; i++) {
						var s = getStyleObject("listedimage"+listedImages[i]);
						s.border = "2px solid #EDF0ED";
					}
					var s = getStyleObject("listedimage"+id);
					s.border = "2px solid red";					
					//alert("select image: "+id+" END");
				}
*/
    		//]]>
			</script>
			
			<!-- END makeAjaxImageListing -->
			
		';
	}
	
	
	
	
	
	
	function ajaxImageAction($dir, $images_per_page, $identifier = 'selected_image') {
		switch ($_GET['ajax_image_action']) {
			
			case 'lookup_image':
				print $this->ajaxLookupImage(intval($_GET['ajax_image_id']),true);
				exit();
				
			case 'list_images':
				print $this->listImages(
					$dir,
					$_POST['default_image'],
					$identifier,
					$_POST['first_image'],
					$images_per_page,
					true
				);
				exit();
				
			case 'init_image_list':
				print $this->listImagesIntro(
					$dir,
					$identifier
				);
				exit();
			
			case 'upload_image_form':
				header("Content-Type: text/html; charset=utf-8"); 
				print $this->uploadImageForm();
				exit();			

			case 'upload_image':
				if (isset($_FILES['bildefil']) && ($_FILES['bildefil']['error'] == '0')) {
									
					if (call_user_func($this->is_allowed,"w",$dir)){ 
						
						$tittel = $_POST['bildenavn'];
						if (empty($tittel)) $tittel = "Uten tittel";
						
						$img_id = $this->uploadImage('bildefil',$dir,$tittel);
						$this->createThumbnail($img_id,true,100,100,"_thumb140");
						$this->createThumbnail($img_id,true,500,-1,"_thumb490");
												
						print '
							<script type="text/javascript">
							//<[CDATA[
								window.parent.imageUploaded('.$img_id.');
							//]]>
							</script>
						';
						exit();						
	
					} else {
						print '
							<script type="text/javascript">
							//<[CDATA[
								alert("Beklager, du har ikke tilgang til å laste opp filer til denne mappen.");
							//]]>
							</script>
						';
						exit();					
					}
							
				}
				print '
					<script type="text/javascript">
					//<[CDATA[
						alert("Bildet ble ikke lastet opp pga. en ukjent feil.");
					//]]>
					</script>
				';
				exit();
			
		}
	}
	
	
	function ajaxLookupImage($id,$includeRoot = false) {
		$_SESSION['ajax_imageselector_defaultimage'] = $id;
		$prefix = $includeRoot ? ROOT_DIR : '';
		if ($id > 0) {
			return '<img src="'.$prefix.$this->getRelativePathToImage($id,true).'" title="Velg nytt bilde" style="border: none; width:100px; height:80px;">';
		} else {
			return 'Velg bilde';
		}
	}
	
	function makeAjaxImageSelector($dir, $identifier = 'selected_image') {
		
		$default = $_SESSION['ajax_imageselector_defaultimage'];
		$imagesPerPage = $_SESSION['ajax_imageselector_imagesperpage'];

		$res = $this->query(
			"SELECT COUNT(id) as imagecount FROM $this->table_images WHERE parent='$dir'"
		);
		$row = $res->fetch_assoc();
		$imageCount = $row['imagecount'];

		if ($default != -1 && $default != 0) {
			$res = $this->query(
				"SELECT COUNT(id) as imagecount FROM $this->table_images WHERE parent='$dir' AND id > $default"
			);
			$row = $res->fetch_assoc();
			$firstImage = floor($row['imagecount']/$imagesPerPage)*$imagesPerPage;
		} else {
			$firstImage = 0;
		}
		$_SESSION['ajax_imageselector_firstimage'] = $firstImage;
	
		$onSelectFunction = "closeAjaxImagePopup(id);";
		
		return array('
			<script type="text/javascript">
			//<![CDATA[
			
				var selected_image = 0;

				// Define various event handlers for Dialog
				var handleSubmit = function() {
					if (selected_image > 0) {
						closeAjaxImagePopup(selected_image);
					}
					this.hide();
				};
				var handleCancel = function() {
					this.cancel();
				};
				var handleSuccess = function(o) {
					var response = o.responseText;
					response = response.split("<!")[0];
					document.getElementById("resp").innerHTML = response;
				};
				var handleFailure = function(o) {
					alert("Submission failed: " + o.status);
				};
				function openImageSelector(e) {
					YAHOO.util.Event.stopEvent(e);
					imageSelectorDialog.show();		
				}
				
				function initImageSelector() {
					imageSelectorDialog = new YAHOO.widget.Dialog("dialog1", { 
						width : "850px",
						fixedcenter : true,
						visible : false, 
						modal: true,
						constraintoviewport : true,
						buttons : [ { text:"Ok", handler:handleSubmit, isDefault:true },
									{ text:"Avbryt", handler:handleCancel } ]
					} );				
					imageSelectorDialog.render();
					YAHOO.util.Event.addListener("ingressbildeLink","click",openImageSelector);
				}
				
				YAHOO.util.Event.onDOMReady(initImageSelector); 


				function closeAjaxImagePopup(new_selection) {
					//console.info("Update span:\'ingressbildespan\' with new image: "+new_selection);
					new Ajax.Updater("ingressbildespan","'.$this->generateURL(array("noprint=true","ajax_image_action=lookup_image","ajax_image_id"),true).'="+new_selection);
				}

				var first_image = '.$firstImage.';
				var default_image = '.$default.';
				var image_count = '.$imageCount.';
				
				function uploadNewForm() {
					var url = "'.$this->generateURL(array("noprint=true","ajax_image_action=upload_image_form"),true).'";				
					var pars = new Array();
					pars.push("first_image="+first_image);
					pars.push("default_image="+default_image);
					pars = pars.join("&");
					//console.info("Load upload form...");
					var success = function(t){ 
						setText("ajaximagelist",t.responseText);
						//console.info("Upload form loaded");
						//selectImage(default_image);
					}
					setText("imagelistmoreimages", "<img src=\"'.$this->image_dir.'progressbar1.gif\" style=\"width:100px; height:9px\" />");
					var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
				}
				
				function showImageList() {
					var url = "'.$this->generateURL(array("noprint=true","ajax_image_action=init_image_list"),true).'";				
					var pars = new Array();
					pars.push("first_image="+first_image);
					pars.push("default_image="+default_image);
					pars = pars.join("&");
					//console.info("Load upload form...");
					var success = function(t){ 
						setText("ajaximagelist",t.responseText);
						//console.info("Upload form loaded");
						//selectImage(default_image);
					}
					setText("imagelistmoreimages", "<img src=\"'.$this->image_dir.'progressbar1.gif\" style=\"width:100px; height:9px\" />");
					var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});
				}

								
				function prevImages() {
					first_image -= '.$imagesPerPage.';
					if (first_image < 0) first_image = (Math.floor(image_count/'.$imagesPerPage.'))*'.$imagesPerPage.';
					loadImages();
				}
				
				function nextImages() {
					first_image += '.$imagesPerPage.';
					if (first_image >= image_count) first_image = 0;
					loadImages();
				}
				
				function newestImages() {
					first_image = 0;
					loadImages();
				}
				
				function loadImages() {
					//console.group("Load thumbs from "+first_image);
					var url = "'.$this->generateURL(array("noprint=true","ajax_image_action=list_images"),true).'";				
					var pars = new Array();
					pars.push("first_image="+first_image);
					pars.push("default_image="+default_image);
					pars = pars.join("&");
					var success = function(t){ 
						setText("ajaximagelist",t.responseText);
						selectImage(default_image);
						//console.groupEnd();
					}
					setText("imagelistmoreimages", "<img src=\"'.$this->image_dir.'progressbar1.gif\" style=\"width:100px; height:9px\" />");
					var myAjax = new Ajax.Request(url, {method: "post", parameters: pars, onSuccess:success});				
				}

				function selectImage(id) {
					//console.group("Select image "+id);
					selected_image = id;
					
					var listedImages = $F("current_images").split(",");
					
					var foundInArray = false;
					for (var i = 0; i < listedImages.length; i++)
						if (listedImages[i] == id) foundInArray = true;
					if (!foundInArray) {
						//console.info("Image not found in the currently visible thumbs");
						//console.groupEnd();
						return;
					}
					for (var i = 0; i < listedImages.length; i++) {
						var s = getStyleObject("listedimage"+listedImages[i]);
						s.border = "2px solid #EDF0ED";
					}
					var s = getStyleObject("listedimage"+id);
					s.border = "2px solid red";

					if (default_image == id) {
						//console.info("Image already selected. No reload needed");
						//console.groupEnd();
						return;
					}
					$("selected_image").value = id;
					default_image = id;
					
					//console.groupEnd();
					
				}
				
				function imageUploaded(id) {
					//console.group("New image uploaded: "+id);
						
					$("selected_image").value = id;
					default_image = id;
					closeAjaxImagePopup(id);
					
					//console.groupEnd();
					
					newestImages();		
					//selectImage(id);
				}


    		//]]>
			</script>
			<div id="dialog1" style="visibility:hidden;">
				<div class="hd">Velg bilde</div>
				<div class="bd">
					
						'.$this->listImagesIntro($dir,$identifier).'


				</div>
			</div>
			','
			
			<input type="hidden" name="'.$identifier.'" id="selected_image" value="'.$default.'" />
			<a href="javascript:alert(\'feil\');" id="ingressbildeLink">
				<span id="ingressbildespan">
				'.$this->ajaxLookupImage($default).'
				</span>
			</a>
		');
	}
	
	/****************************************************************************************************
		UPLOAD NEW IMAGE
		**************************************************************************************************/
	
	function uploadImageForm($useIFrame = true){
	
		$url_post = $this->generateURL(array("noprint=true","ajax_image_action=upload_image"));
		$target = '';
		$iframeAdd = '';
		$progressDiv = '';
		if ($useIFrame) {
			$url_post = ROOT_DIR.$url_post;
			$target = 'target="upload_frame"';
			$iframeAdd = 'Effect.Appear(\'imguploadprogress\');';
			$progressDiv = '<p id="imguploadprogress" style="display:none; background:#eee; border: 1px solid #ddd; padding: 10px; margin:10px;">
				<strong>Vennligst vent mens bildet lastes opp...</strong> <br /><br />
				Merk at dette kan ta ganske lang tid hvis bildet er stort!<br /><br />
				<img src="'.ROOT_DIR.$this->image_dir.'progressbar1.gif" alt="Progress" />
			</p>';
		}		

		return '
			<h2>Last opp bilde</h2>

			'.$progressDiv.'

			<form id="imguploadform" '.$target.' enctype="multipart/form-data" action="'.$url_post.'" method="post" onsubmit="$(\'submitbtn\').disabled=\'disabled\';'.$iframeAdd.'">
				<p>
					Her kan du laste opp et bilde, som du senere kan bruke i f.eks. nyheter, artikler, 
					på forumet, etc.. Legg merke til at det vil ta litt tid å laste opp bilde, men 
					ikke trykk mer enn en gang på "Last opp"-knappen. Det kan føre til at bildet blir lagt 
					til flere ganger.
				</p>
				<p>
					Skal du bruke bildet på nyhetssiden blir det automatisk skalert ned til 100x80 pixler.
				</p>
				<table>
					<tr><td>Tittel: </td><td><input name="bildenavn" type="text" size="48" /></td></tr>
					<tr><td>Bilde: </td><td><input name="bildefil" type="file" size="50" /></td></tr>
				</table><br />
				<input id="cancelbtn" type="button" name="cancel" value="    Avbryt    " onclick="showImageList()" />
				<input id="submitbtn" type="submit" name="lagre" value="    Last Opp    " />
			</form>
			<iframe id="upload_frame" name="upload_frame" style="visibility:hidden;"></iframe>
		';
	}

	function uploadImage($varName, $dir_id, $caption = "", $printErrors = true, $img_id = -1){
		
		if (!isset($_FILES[$varName])) $this->fatalError("Det ble ikke lastet opp noe bilde!");
		if ($_FILES[$varName]['error'] == 4) return 0;

		// Determine path
		$dir = $this->getFullPathToDir($dir_id);
		$res = $this->query(
			"SELECT fullslug FROM $this->table_dirs WHERE id='$dir_id'"
		);
		$row = $res->fetch_assoc();
		$rdir = $row['fullslug']."/";
		
		$image_already_exists = true;
		if ($img_id == -1) {
			$image_already_exists = false;
			if (empty($caption)) {				
				$image_already_exists = true;
				$i = 0;
				while ($image_already_exists) {
					$i++;
					$caption = "Uten tittel $i";
					$res = $this->query("SELECT id FROM $this->table_images WHERE caption='$caption' AND parent='$dir_id'");
					if ($res->num_rows == 0) 
						$image_already_exists = false;
				}
			}
			$img_id = $this->findImageID($dir_id, $caption, true);
		}
		$filename = "image$img_id";
		if (empty($caption)) $caption = "Hallo?";
		
		// Upload the image
		$u1 = new fileupload();
		$u1->varname = $varName;
		$u1->filetype = "image";
		$u1->directory = $dir;
		$u1->filename = $filename;
		$u1->sizelimit = 4096000;
		$u1->upload();
		if (count($u1->errors) > 0){
			if ($printErrors) {
				$str = "<ul>";
				foreach ($u1->errors as $error){
					$str .= "<li>".$error."</li>\n";
				}
				$str .= "</ul>";
				$this->fatalError($str);
			} else {
				$this->upload_errors = $u1->errors;
			}
			return -1;
		} else {
			$this->upload_errors = array();
		}
		$uploaded_image = $u1->fullpath;
		
		$this->updateDatabaseInfo($img_id, $uploaded_image);
				
		$this->addToActivityLog('lastet opp et nytt bilde: <a href="'.$this->pathToCMS.$rdir.'?current_image='.$img_id.'">'.$caption.'</a>');
		
		return $img_id;
	
	}
	
	function updateDatabaseInfo($img_id, $path = "") {

		if (empty($path)) $path = $this->getFullpathToImage($img_id);
		
		list($width, $height, $type, $attr) = getimagesize($path);
		$path_parts = pathinfo($path);
		$extension = $path_parts['extension'];
		$filesize = filesize($path);
		
		$this->query("UPDATE $this->table_images 
			SET
				extension='$extension',
				size='$filesize',
				width='$width',
				height='$height'
			WHERE id='$img_id'"
		);
		
	}
	
	
}


?>
