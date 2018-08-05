<?php
class imageeditor {

	var $gd;		// gd on server?
	var $gd2;		// gd2 on server?
	var $filename;
	var $fileextension;
	var $src_img;
	var $dst_img;
	
	
	/* Constructor (checks for gd on server) */
	function __construct(){
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
