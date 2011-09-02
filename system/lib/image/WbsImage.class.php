<?php
/**
 * Class uses syntax Imagick lib for working with images.
 * @see http://ru2.php.net/imagick
 * @see http://ru2.php.net/gd
 * @method WbsImage clone() clone imager lib object
 */
class WbsImage 
{
    public $lib = null;
    protected  $isImagick = false;
    protected $isGd = false;
    protected $filePath = null;
    
    const LIB_GD = "gd"; 
    const LIB_IMAGICK = "imagick";
    
    public function __construct($filePath = null, $lib = "imagick") 
    {
        $this->lib = $this->openImage($filePath, $lib);
        $this->filePath = $filePath;
    }
    /**
     * @param string $filePath
     * @return WbsImage
     */
    public function openImage($file = null, $lib = "imagick")
    {    	
    	if ($lib == self::LIB_IMAGICK) {
	        if ( extension_loaded( "imagick" ) ) {
	            $this->isImagick = true;
	            
	            return (is_null($file)) ? new Imagick() : new Imagick($file);
	        }
	        else if( extension_loaded( "gd" ) ) {
	            $this->isGd = true;
	            return new WbsImageGd($file);
	        }
    	}
    	if ($lib == self::LIB_GD) {
    		if( extension_loaded( "gd" ) ) {
	            $this->isGd = true;
	            return new WbsImageGd($file);
	        }
	        else if ( extension_loaded( "imagick" ) ) {
	            $this->isImagick = true;
	            return new Imagick($file);
	        }
    	}
    	    	
    	return $this;
    }
    /**
     * @return Boolen
     */
    public function isImagick()
    {
        return $this->isImagick;
    }
    
    public function getImageSize()
    {
		return $this->lib->getImageSize();    	
    }
    
    /**
     * @return Boolen
     */
    public function isGd()
    {
        return $this->isGd;
    }
    /**
     * @return mixed - Imagick or Gd lib
     * @see isImagick() and isGd()
     */
    public function getLib()
    {
        $this->lib;
    }
    /**
     * @param mixed $lib - Imagick or Gd lib
     */
    public function setLib($lib)
    {
        $this->lib = $lib;
    }
    public function setLibType($type)
    {
    	if ( $type == self::LIB_GD )
    		$this->isGd = true;
    	else if ( $type == self::LIB_IMAGICK )
    		$this->isImagick = true;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @param Boolean $isFit
     * @return WbsImage
     */
    public function thumbnailImage($width, $height, $isFit = false) 
    {
    	if ( $this->isImagick() ) {
    		
    		$info = $this->lib->getImageGeometry();
    		$w_src = $info['width'];
			$h_src = $info['height'];
			
			if ( $isFit || ($width <= $w_src && $height <= $h_src) ) {
	    		if ( ($w_src * $height)/$h_src > $width ) {
					$h_dst = round(($h_src * $width)/$w_src);
					$w_dst = $width;
				}
				else {
					$w_dst = round(($w_src * $height)/$h_src);
					$h_dst = $height;
				}
	    		$this->lib->thumbnailImage($w_dst, $h_dst);
	    		$info = $this->lib->getImageGeometry();
			}
    		  
    		$canva = new Imagick();
    		$canva->newImage( $info['width'], $info['height'], "#FFFFFF", "jpg" );
    		$canva->compositeImage( $this->lib, imagick::COMPOSITE_OVER, 0, 0 );
    		$this->lib = $canva;
    	}
    	else {
    		$this->lib->resize($width, $height);
    	}
        return $this;
    }
    
    /**
     * @param int $width
     * @param int $height
     * @return WbsImage
     */
    public function cropThumbnailImage($width, $height)
    {
    	if ( $this->isImagick() )
        	$this->lib->cropThumbnailImage($width, $height);
        else 	
			$this->lib->thumbnailImage($width, $height);
        return $this;        
    }
    
    public function cropImage($width  , $height  , $x  , $y)
    {
    	if ( $this->isImagick() )
        	$this->lib->cropImage($width  , $height  , $x  , $y);
        else if ($this->isGd()) {
            $this->lib->cropImage($width  , $height  , $x  , $y);
        }
			
        return $this; 
    }
    
	public function resizeToFill($width, $height) {
		if ( $this->isImagick() ) {
			$this->thumbnailImage($width, $height, true);
			
			$info = $this->lib->getImageGeometry();
			$x =  round( ($width - $info['width']) / 2);
			$y =  round( ($height - $info['height']) / 2);
			
			$fill = new Imagick();
			$fill->newImage( $width, $height, "#FFFFFF" );
			$fill->compositeImage( $this->lib, imagick::COMPOSITE_OVER, $x, $y );
			$this->lib = $fill;
		}
		else if ($this->isGd()) {
    		$this->lib->resizeToFill($width, $height);
		}
        return $this; 
	}
    
    /**
     * @param int $degrees
     * @return WbsImage
	 */
    public function rotateImage($degrees)
    {
        if( $this->isImagick )
        	$this->lib->rotateImage(new ImagickPixel('white'), $degrees);
        else
        	$this->lib->rotateImage($degrees);
        return $this;
    }

    /**
     * @return int
     */
    public function getImageWidth()
    {
        return $this->lib->getImageWidth();
    }
   
    
	function __call($m, $a) {
	    if ( $m == 'clone' ) {
	        $image = new self();
	        if ( $this->isImagick() ) {
	        	$image->setLib( $this->lib->clone() );
	        	$image->setLibType( self::LIB_IMAGICK );
	        }
	        else if ( $this->isGd() ) {
	        	$image->setLib( new WbsImageGd( $this->filePath ) );
	        	$image->setLibType( self::LIB_GD );
	        }
	        return $image;
	    }
  	}    
    
    /**
     * @return int
     */
    public function getImageHeight()
    { 
        return $this->lib->getImageHeight(); 
    }

    /**
     * @param string $filename
     * @return WbsImage
     **/
    public function writeImage($filename, $quality = null) 
    {
    	if( $this->isImagick() ) {
    		if ($quality)
				$this->lib->setImageCompressionQuality($quality);
			$this->lib->writeImage( $filename );
    	}
    	else {
            $this->lib->writeImage($filename, $quality);
    	} 
        return $this;
    }
    
    

    public function destroy()
    {
    	$this->lib->destroy();
    }
    
    /**
     * @param WbsImage $im
     * @param int $type
     * @param int $x
     * @param int $y
     * @return WbsImage
     */
    public function compositeImage( $im, $type, $x, $y )
    {
        if( $this->isImagick() )
    		$this->lib->compositeImage( $im->lib, $type, $x, $y );
    	else if ( $this->isGd() ) {
    	    $this->lib->compositeImage( $im,  $x, $y );
    	}
    	return $this;
    }
    
    public function newImage( $width, $height, $background, $format )
    {
		if( $this->isImagick() )
    		$this->lib->newImage( $width, $height, new ImagickPixel('white') );
    	else if ( $this->isGd() ) {
            $this->lib->newImage( $width, $height );
    	}
    	
    	return $this;    	
    }
    
}

?>