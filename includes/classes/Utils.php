<?php
class Utils {

	/** 
	 * Converts bytes into human readable file size (e.g. 10 MB, 200.20 GB). 
	 * 
	 * @param int $bytes 
	 * @return string Filesize in human readable format 
	 */ 
	function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
	   
		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
	   
		$bytes /= pow(1024, $pow); 
	   
		return round($bytes, $precision) . ' ' . $units[$pow]; 
	} 


}
?>