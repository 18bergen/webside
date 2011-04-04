<?
class javaupload {
	
	var $jupload_dir = "jupload.jar";
	var $actionurl;
	var $completeurl;
	var $errorurl;
	var $maximagesize = 5242880;
	var $uploadform_template = '
		<applet
				title="JUpload"
				name="JUpload"
				code="com.smartwerkz.jupload.classic.JUpload"
				codebase="."
				archive=\'%jupload_dir%jupload.jar,
						%jupload_dir%commons-codec-1.3.jar,
						%jupload_dir%commons-httpclient-3.0-rc4.jar,
						%jupload_dir%commons-logging.jar,
						%jupload_dir%skinlf/skinlf-6.2.jar\'
				width="490"
				height="400"
				mayscript="mayscript"
				alt="You need to download or activate Java to use this applet!">
		
			<param name="Config" value=\'%jupload_dir%jupload.bergen.config\'>
			
			<param name="Upload.URL.Action" value="%actionurl%" />
			<param name="Upload.Complete.Target" value="_self" />
			<param name="Upload.Complete.URL" value="%completeurl%" />
			<param name="Upload.Complete.ErrorURL" value="%errorurl%" />
			<param name="Upload.Http.MaxRequestSize" value="%maximagesize%" />	
		
			Your browser does not support Java Applets or you disabled Java Applets in your browser-options.
			To use this applet, please install the newest version of Sun\'s Java Runtime Environment (JRE).
			You can get it from <a href="http://www.java.com/">java.com</a>
		
		</applet>
	';

	function printUploadForm(){
		$r1a[] = "%actionurl%";		$r2a[] = $this->actionurl;
		$r1a[] = "%completeurl%";	$r2a[] = $this->completeurl;
		$r1a[] = "%errorurl%";		$r2a[] = $this->errorurl;
		$r1a[] = "%maximagesize%";	$r2a[] = $this->maximagesize;
		$r1a[] = "%jupload_dir%";	$r2a[] = $this->jupload_dir;
		return str_replace($r1a, $r2a, $this->uploadform_template);
	}

}
?>