<?php
class image_editor {

	var $gd;		// gd on server?
	var $gd2;		// gd2 on server?
	var $filename;
	var $fileextension;
	var $src_img;
	var $dst_img;
	
	var $temp_dir;
	var $current_image_dir;
	var $current_image_file;
	
	function run() {
	
	}
	
	function outputCropForm() {
	
		print "
	
			<script type='text/javascript'>
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
	
			var crop_script_server_file = 'crop_image.php';
	
			var cropToolBorderWidth = 1;	// Width of dotted border around crop rectangle
			var smallSquareWidth = 7;	// Size of small squares used to resize crop rectangle
	
			// Size of image shown in crop tool
			var crop_imageWidth = 465;
			var crop_imageHeight = 348;
	
			// Size of original image
			var crop_originalImageWidth = 2272;
			var crop_originalImageHeight = 1704;
	
			var crop_minimumPercent = 10;	// Minimum percent - resize
			var crop_maximumPercent = 200;	// Maximum percent -resize
	
	
			var crop_minimumWidthHeight = 15;	// Minimum width and height of crop area
	
			var updateFormValuesAsYouDrag = true;	// This variable indicates if form values should be updated as we drag. This process could make the script work a little bit slow. That's why this option is set as a variable.
			if(!document.all)updateFormValuesAsYouDrag = false;	// Enable this feature only in IE
	
			/* End of variables you could modify */
			</script>
			<script type='text/javascript' src='js/image-crop.js'></script>

			<div style='border: 1px solid #000000; background: #FFFFFF; width: 490px; position: absolute; left: 100px; top: 50px;'>

			<form>

				<input type='hidden' id='input_image_ref' value='demo-images/nature_orig.jpg'>
				<input type='hidden' class='textInput' name='crop_x' id='input_crop_x'>
				<input type='hidden' class='textInput' name='crop_y' id='input_crop_y'>
				<input type='hidden' class='textInput' name='crop_width' id='input_crop_width'>
				<input type='hidden' class='textInput' name='crop_height' id='input_crop_height'>
				<input type='hidden' class='textInput' name='crop_percent_size' id='crop_percent_size' value='100'>

			<p>
				<b>Picture from Norway (<span id='label_dimension'>)</b>
			</p>
			<p>
				To select crop area, drag and move the dotted rectangle or type in values directly into the form.
			</p>	
	
			<div class='crop_content'>
				<div id='imageContainer'>
					<img src='demo-images/nature.jpg'>
				</div>
			</div>
			
			<p style='clear:both;'>
				<input type='button' onclick='cropScript_executeCrop(this)' value='Crop'>
			</p>
			
			<div id='crop_progressBar'>
					
			</div>		
		
		</form>
		
		</div>
		
		<script type='text/javascript'>
			init_imageCrop();
		</script>
	
	
	
		";
		
	}
	
	/* Constructor (checks for gd on server) */
	function image_editor(){
		$this->gd = false;
		$this->gd2 = false;
		ob_start();
		phpinfo(8);
		$phpinfo=ob_get_contents();
		ob_end_clean();
		$phpinfo=strip_tags($phpinfo);
		$phpinfo=stristr($phpinfo,"gd version");
		$phpinfo=stristr($phpinfo,"version");
		preg_match('/\d/',$phpinfo,$gd);
		if ($gd[0]=='1'){ $this->gd = true; }
		if ($gd[0]=='2'){ $this->gd = true; $this->gd2=true; }
	}

	/*
		Function ditchtn($arr,$thumbname)
		filters out thumbnails
	*/
	function ditchtn($arr,$thumbname){
		foreach ($arr as $item){
			if (!preg_match("/^".$thumbname."/",$item)){$tmparr[]=$item;}
		}
		return $tmparr;
	}
	
	// Load a file into a gd image (jpg, gif and png supported)
	function load($filename){
		$fsp=explode(".",$filename);
		$this->filename = $filename;
		$this->fileextension = $fsp[count($fsp)-1];
		if ($this->fileextension == 'jpg' || $this->fileextension == 'jpeg'){
			$this->src_img=imagecreatefromjpeg($filename);
		} else if ($this->fileextension == 'png'){
			$this->src_img=imagecreatefrompng($filename);
		} else if ($this->fileextension == 'gif'){
			$this->src_img=imagecreatefromgif($filename);
		} else {
			print("[createthumb] unknown filetype $this->fileextension, only accepts jpg, gif and png."); exit;
		}
	}
	
	// Resizes the image (new_h = -1 will set height to a value so the image keeps the original proportions
	function createthumb($new_w,$new_h){
		$old_x = imageSX($this->src_img);
		$old_y = imageSY($this->src_img);
		if ($new_h == -1) {
			$forhold = ($new_w/$old_x);
			$thumb_w = $new_w;
			$thumb_h = round($forhold*$old_y);
		} else {
			$thumb_w = $new_w;
			$thumb_h = $new_h;
		}
		if ($this->gd2){
			$this->dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
			imagecopyresampled($this->dst_img,$this->src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
		} else {
			$this->dst_img=ImageCreate($thumb_w,$thumb_h);
			imagecopyresized($this->dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
		}
	}
	
	// Save dst_img to file
	function save($filename){
		if ($this->fileextension == 'jpg' || $this->fileextension == 'jpeg'){
			imagejpeg($this->dst_img,$filename,75); 		
		} else if ($this->fileextension == 'png'){
			imagepng($this->dst_img,$filename); 
		} else if ($this->fileextension == 'gif'){
			imagegif($this->dst_img,$filename); 
		}
		
		// Sett permissions
		/*
		$old = umask(0);
		chmod($filename, 0666);
		umask($old);
		*/
	}
}
?>