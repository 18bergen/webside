<?php
class JqueryUpload {
    
    var $actionurl;
    var $completeurl;
    // var $errorurl;
    // var $maximagesize = 5242880;
    var $template_dir        = "../includes/templates/";
    var $uploadform_template = 'imagearchive_uploadform.html';

    function printUploadForm(){
        $template = file_get_contents($this->template_dir . $this->uploadform_template);
        $r1a[] = "%actionurl%";     $r2a[] = $this->actionurl;
        $r1a[] = "%completeurl%";   $r2a[] = $this->completeurl;
        $r1a[] = "%upload_max_filesize%";   $r2a[] = ini_get('upload_max_filesize');

        // $r1a[] = "%errorurl%";      $r2a[] = $this->errorurl;
        // $r1a[] = "%maximagesize%";  $r2a[] = $this->maximagesize;
        // $r1a[] = "%jupload_dir%";   $r2a[] = $this->jupload_dir;
        return str_replace($r1a, $r2a, $template);
    }

}

