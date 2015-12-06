<?php
class uimage extends base {
	
	var $uimage_dir;
	var $filename;

	function initialize(){
		$this->initialize_base();
		$this->filename = $this->coolUrlSplitted[0];
	}

	function run(){
		$this->initialize();
		$this->fetch();
	}

	function file_extension($f){
		$t = explode(".",$f);
		return array_pop($t);
	}

	function fetch(){
		if (count($this->coolUrlSplitted) != 1){
			$this->fatalError("Illegal filename");
		}
		if (preg_match("/[^a-z0-9\._]/",$this->filename)){
			$this->fatalError("Illegal filename");
		}
		
		if (file_exists($this->uimage_dir.$this->filename)){
			if ($this->file_extension($this->filename) == "jpg"){
				header("Content-type: image/jpeg");
				readfile($this->uimage_dir.$this->filename);
			} else if ($this->file_extension($this->filename) == "gif"){
				header("Content-type: image/gif");
				readfile($this->uimage_dir.$this->filename);
			} else if ($this->file_extension($this->filename) == "png"){
				header("Content-type: image/png");
				readfile($this->uimage_dir.$this->filename);
			}
		} else {
			//header("Content-type: image/png"); 
			header("Location: ".$this->image_dir."imgnotfound.png");
			/*
			$img_handle = imagecreate(100,100);
			$background_color = imagecolorallocate($img_handle, 255,255,255);
			$text_color = imagecolorallocate($img_handle, 255, 0, 0);
			$line_color = imagecolorallocate($img_handle, 255,200,200);
			$ip = $_SERVER['REMOTE_ADDR'];
			imageline($img_handle,0,0,100,100,$line_color);
			imageline($img_handle,100,0,0,100,$line_color);
			imagestring ($img_handle, 3, 40, 28,  "Fant", $text_color); 
			imagestring ($img_handle, 3, 40, 40,  "ikke", $text_color); 
			imagestring ($img_handle, 3, 30, 52,  "bildet!", $text_color); 
			imagepng ($img_handle); 
			imagedestroy ($img_handle);
			//header("Location: ".$this->image_dir."nothumb.jpg");
			*/
			
			exit();
		}
	}

}
?>