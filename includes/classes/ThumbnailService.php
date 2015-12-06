<?php
class ThumbnailService {

    /**
     * Create thumbnail
     *
     * @param string $sourceFile
     * @param string $targetFile
     * @param int $maxWidth
     * @param int $maxHeight
     * @param boolean $preserverAspectRatio
     * @param boolean $bmpSupported
     * @return boolean
     * @static
     * @access public
     */
    public static function createThumb($sourceFile, $targetFile, $maxWidth, $maxHeight, $quality = 80, $preserverAspectRatio = true) {
    	if (!is_file($sourceFile)) {
    		return false;
    	}

        $sourceImageAttr = @getimagesize($sourceFile);
        if ($sourceImageAttr === false) {
            return false;
        }

        $sourceImageWidth = isset($sourceImageAttr[0]) ? $sourceImageAttr[0] : 0;
        $sourceImageHeight = isset($sourceImageAttr[1]) ? $sourceImageAttr[1] : 0;
        $sourceImageMime = isset($sourceImageAttr["mime"]) ? $sourceImageAttr["mime"] : "";
        $sourceImageBits = isset($sourceImageAttr["bits"]) ? $sourceImageAttr["bits"] : 8;
        $sourceImageChannels = isset($sourceImageAttr["channels"]) ? $sourceImageAttr["channels"] : 3;

        if (!$sourceImageWidth || !$sourceImageHeight || !$sourceImageMime) {
            return false;
        }

        $path_parts = pathinfo($targetFile);
        $targetDir = $path_parts['dirname'];
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
            if (!file_exists($targetDir)) {
                die("Could not create thumb target dir " . $targetDir);
            }
        }

        $iFinalWidth = $maxWidth == 0 ? $sourceImageWidth : $maxWidth;
        $iFinalHeight = $maxHeight == 0 ? $sourceImageHeight : $maxHeight;

        if ($sourceImageWidth <= $iFinalWidth && $sourceImageHeight <= $iFinalHeight) {
            if ($sourceFile != $targetFile) {
                copy($sourceFile, $targetFile);
            }
            return true;
        }

        if ($preserverAspectRatio)
        {
            // Gets the best size for aspect ratio resampling
            $oSize = ThumbnailService::GetAspectRatioSize($iFinalWidth, $iFinalHeight, $sourceImageWidth, $sourceImageHeight );
        }
        else {
            $oSize = array('Width' => $iFinalWidth, 'Height' => $iFinalHeight);
        }

        ThumbnailService::setMemoryForImage($sourceImageWidth, $sourceImageHeight, $sourceImageBits, $sourceImageChannels);

        switch ($sourceImageAttr['mime'])
        {
            case 'image/gif':
                {
                    if (@imagetypes() & IMG_GIF) {
                        $oImage = @imagecreatefromgif($sourceFile);
                    } else {
                        $ermsg = 'GIF images are not supported';
                    }
                }
                break;
            case 'image/jpeg':
                {
                    if (@imagetypes() & IMG_JPG) {
                        $oImage = @imagecreatefromjpeg($sourceFile) ;
                    } else {
                        $ermsg = 'JPEG images are not supported';
                    }
                }
                break;
            case 'image/png':
                {
                    if (@imagetypes() & IMG_PNG) {
                        $oImage = @imagecreatefrompng($sourceFile) ;
                    } else {
                        $ermsg = 'PNG images are not supported';
                    }
                }
                break;
            case 'image/wbmp':
                {
                    if (@imagetypes() & IMG_WBMP) {
                        $oImage = @imagecreatefromwbmp($sourceFile);
                    } else {
                        $ermsg = 'WBMP images are not supported';
                    }
                }
                break;
            case 'image/bmp':
                {
                    /*
                    * This is sad that PHP doesn't support bitmaps.
                    * We'll not resize images this way (if $sourceFile === $targetFile),
                    * because user defined imagecreatefrombmp and imagecreatebmp are horribly slow
                    */
                    $ermsg = 'BMP/JPG images are not supported';
                }
                break;
            default:
                $ermsg = $sourceImageAttr['mime'].' images are not supported';
                break;
        }

        if (isset($ermsg) || false === $oImage) {
            return false;
        }


        $oThumbImage = imagecreatetruecolor($oSize["Width"], $oSize["Height"]);
        //imagecopyresampled($oThumbImage, $oImage, 0, 0, 0, 0, $oSize["Width"], $oSize["Height"], $sourceImageWidth, $sourceImageHeight);
        ThumbnailService::fastImageCopyResampled($oThumbImage, $oImage, 0, 0, 0, 0, $oSize["Width"], $oSize["Height"], $sourceImageWidth, $sourceImageHeight, (int)max(floor($quality/20), 1));

        switch ($sourceImageAttr['mime'])
        {
            case 'image/gif':
                imagegif($oThumbImage, $targetFile);
                break;
            case 'image/jpeg':
            case 'image/bmp':
                imagejpeg($oThumbImage, $targetFile, $quality);
                break;
            case 'image/png':
                imagepng($oThumbImage, $targetFile);
                break;
            case 'image/wbmp':
                imagewbmp($oThumbImage, $targetFile);
                break;
        }

        imageDestroy($oImage);
        imageDestroy($oThumbImage);

        return true;
    }



    /**
     * Return aspect ratio size, returns associative array:
     * <pre>
     * Array
     * (
     *      [Width] => 80
     *      [Heigth] => 120
     * )
     * </pre>
     *
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $actualWidth
     * @param int $actualHeight
     * @return array
     * @static
     * @access public
     */
    public static function getAspectRatioSize($maxWidth, $maxHeight, $actualWidth, $actualHeight)
    {
        $oSize = array("Width"=>$maxWidth, "Height"=>$maxHeight);

        // Calculates the X and Y resize factors
        $iFactorX = (float)$maxWidth / (float)$actualWidth;
        $iFactorY = (float)$maxHeight / (float)$actualHeight;

        // If some dimension have to be scaled
        if ($iFactorX != 1 || $iFactorY != 1)
        {
            // Uses the lower Factor to scale the oposite size
            if ($iFactorX < $iFactorY) {
                $oSize["Height"] = (int)round($actualHeight * $iFactorX);
            }
            else if ($iFactorX > $iFactorY) {
                $oSize["Width"] = (int)round($actualWidth * $iFactorY);
            }
        }

        if ($oSize["Height"] <= 0) {
            $oSize["Height"] = 1;
        }
        if ($oSize["Width"] <= 0) {
            $oSize["Width"] = 1;
        }

        // Returns the Size
        return $oSize;
    }
    
    
    /**
     * @link http://pl.php.net/manual/pl/function.imagecreatefromjpeg.php
     * function posted by e dot a dot schultz at gmail dot com
     *
     * @static
     * @access public
     * @param string $filename
     * @return boolean
     */
    public static function setMemoryForImage($imageWidth, $imageHeight, $imageBits, $imageChannels)
    {
        $MB = 1048576;  // number of bytes in 1M
        $K64 = 65536;    // number of bytes in 64K
        $TWEAKFACTOR = 2.4;  // Or whatever works for you
        $memoryNeeded = round( ( $imageWidth * $imageHeight
        * $imageBits
        * $imageChannels / 8
        + $K64
        ) * $TWEAKFACTOR
        ) + 3*$MB;

        //ini_get('memory_limit') only works if compiled with "--enable-memory-limit" also
        //Default memory limit is 8MB so well stick with that.
        //To find out what yours is, view your php.ini file.
        $memoryLimit = ThumbnailService::returnBytes(@ini_get('memory_limit'))/$MB;
        if (!$memoryLimit) {
            $memoryLimit = 8;
        }

        $memoryLimitMB = $memoryLimit * $MB;
        if (function_exists('memory_get_usage')) {
            if (memory_get_usage() + $memoryNeeded > $memoryLimitMB) {
                $newLimit = $memoryLimit + ceil( ( memory_get_usage()
                + $memoryNeeded
                - $memoryLimitMB
                ) / $MB
                );
                if (@ini_set( 'memory_limit', $newLimit . 'M' ) === false) {
                    return false;
                }
            }
        } else {
            if ($memoryNeeded + 3*$MB > $memoryLimitMB) {
                $newLimit = $memoryLimit + ceil(( 3*$MB
                + $memoryNeeded
                - $memoryLimitMB
                ) / $MB
                );
                if (false === @ini_set( 'memory_limit', $newLimit . 'M' )) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * convert shorthand php.ini notation into bytes, much like how the PHP source does it
     * @link http://pl.php.net/manual/en/function.ini-get.php
     *
     * @static
     * @access public
     * @param string $val
     * @return int
     */
    public static function returnBytes($val) {
        $val = trim($val);
        if (!$val) {
            return 0;
        }
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }
    
    
    /**
     * @link http://pl.php.net/manual/en/function.imagecopyresampled.php
     * replacement to imagecopyresampled that will deliver results that are almost identical except MUCH faster (very typically 30 times faster)
     *
     * @static
     * @access public
     * @param string $dst_image
     * @param string $src_image
     * @param int $dst_x
     * @param int $dst_y
     * @param int $src_x
     * @param int $src_y
     * @param int $dst_w
     * @param int $dst_h
     * @param int $src_w
     * @param int $src_h
     * @param int $quality
     * @return boolean
     */
    public static function fastImageCopyResampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3)
    {
        if (empty($src_image) || empty($dst_image)) {
            return false;
        }

        if ($quality <= 1) {
            $temp = imagecreatetruecolor ($dst_w + 1, $dst_h + 1);
            imagecopyresized ($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
            imagecopyresized ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
            imagedestroy ($temp);

        } elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
            $tmp_w = $dst_w * $quality;
            $tmp_h = $dst_h * $quality;
            $temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
            imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
            imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
            imagedestroy ($temp);

        } else {
            imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
        }

        return true;
    }
    
}
?>