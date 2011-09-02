<?php
	class WbsImageGd {
		
		public $imgRes = null;
		private $info = null;
		private $path = null;
		
		public function __construct($path = null) {
		    if ( $path ) {
    			$this->openImage($path);
    			$this->path = $path;
		    }
		}
		
		public function getImageWidth()
	    {
	        return $this->info[0];
	    }
		public function getImageHeight()
	    {
	        return $this->info[1];
	    }
	    public function newImage($width, $height)
	    {
	        $canva = imagecreatetruecolor( $width, $height );
    	    $bgColor = imagecolorallocate($canva, 255,255,255);
    	    imagefill($canva, 0, 0, $bgColor);
    	    
	        $this->imgRes = $canva;
	    }

		public function openImage($path) {
			
			if (!extension_loaded("GD"))
				throw new RuntimeException ("Not include GD library");
				
			if (!file_exists($path) && file_exists(iconv("UTF-8", "WINDOWS-1251", $path)))
			    	$path = iconv("UTF-8", "WINDOWS-1251", $path);	
			
			if(!preg_match('/^http[s]?:\/\//',$path)){	
				$path = realpath($path);
			}
			
			$this->info = getimagesize( $path );
			switch ($this->info[2]) {
		        case 1:
		            // Create recource from gif image
					$this->imgRes = imagecreatefromgif( $path );
		            break;
		        case 2:
		            // Create recource from jpg image
					$this->imgRes = imagecreatefromjpeg( $path );
					
		            break;
		        case 3:
		            // Create resource from png image
					$this->imgRes = imagecreatefrompng( $path );
		            break;
		        case 6:
		            // Create recource from bmp image imagecreatefromwbmp
					$this->imgRes = imagecreatefromwbmp( $path );
		            break;
		        default:
		            break;
			}
			
			if ( $this->imgRes === false ) {
			    throw new RuntimeException ("Error opening file");
			}
			
			return $this;
		}
		
		
		public function compositeImage($lib,  $x, $y)
		{
		    $width = imagesx($lib->imgRes);
		    $height = imagesy($lib->imgRes);
		    
		    if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $this->imgRes, $lib->imgRes, $x, $y, 0, 0, $width, $height, $width, $height );
			else
				imagecopyresized ( $this->imgRes, $lib->imgRes, $x, $y, 0, 0, $width, $height, $width, $height );
						
			return $destImg;
		}

		public function getImageSize()
		{
			return getimagesize($this->path);
		}

		public function _resize($w, $h) {
			
			$destImg = imagecreatetruecolor( $w, $h );				
			$width = imagesx($this->imgRes);
			$height = imagesx($this->imgRes);
			
			if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, 0, 0, 0, 0, $w, $h, $width, $height );
			else
				imagecopyresized ( $destImg, $this->imgRes, 0, 0, 0, 0, $w, $h, $width, $height );			
			
			$this->imgRes = $destImg;
			return $this;
		}

		public function resize($width, $height) {
			
			$w_src = imagesx($this->imgRes);
			$h_src = imagesy($this->imgRes);
			
			
			if ( ($w_src * $height)/$h_src > $width ) {
				$h_dst = round(($h_src * $width)/$w_src);
				$w_dst = $width;
			}
			else {
				$w_dst = round(($w_src * $height)/$h_src);
				$h_dst = $height;
			}
				
			$destImg = imagecreatetruecolor( $w_dst, $h_dst );				
			
			if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, 0, 0, 0, 0, $w_dst, $h_dst, $w_src, $h_src );
			else
				imagecopyresized ( $destImg, $this->imgRes, 0, 0, 0, 0, $w_dst, $h_dst, $w_src, $h_src );			
			
			$this->imgRes = $destImg;
			return $this;
		}		
		
		public function resizeToFill($width, $height) {
			
			$w_src = imagesx($this->imgRes);
			$h_src = imagesy($this->imgRes);
			
			if ( ($w_src * $height)/$h_src > $width ) {
				$h_dst = round(($h_src * $width)/$w_src);
				$w_dst = $width;
				
				$w_margin = 0;
				$h_margin = round(($height - $h_dst) / 2);
			}
			else {
				$w_dst = round(($w_src * $height)/$h_src);
				$h_dst = $height;
				
				$w_margin = round(($width - $w_dst) / 2);
				$h_margin = 0;
			}
				
			$destImg = imagecreatetruecolor( $width, $width );		
			$color = imagecolorallocate($destImg, 255, 255, 255);
			imagefill($destImg, 0, 0, $color);
			
			if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, $w_margin, $h_margin, 0, 0, $w_dst, $h_dst, $w_src, $h_src );
			else
				imagecopyresized ( $destImg, $this->imgRes, $w_margin, $h_margin, 0, 0, $w_dst, $h_dst, $w_src, $h_src );			
			
			$this->imgRes = $destImg;
			return $this;
		}	

		public function rotateImage($angle) {			
			$this->imgRes = imagerotate($this->imgRes, $angle, 0);  
			return $this;
		}
		
		public function cropImage($width  , $height  , $x  , $y)
		{
		    $destImg = imagecreatetruecolor( $width, $height );
		    $w_old = imagesx($this->imgRes);
		    $h_old = imagesy($this->imgRes);
		    
		    if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, 0, 0, $x, $y, $width, $height, $width, $height );
			else
				imagecopyresized ( $destImg, $this->imgRes, 0, 0, $x, $y, $width, $height, $width, $height );
			
			$this->imgRes = $destImg;
			return $destImg;
		}

		public function thumbnailImage($width, $height, $isFit = false) {
			$size = ( !is_null($width) ) ? $width : $height;

			$width = $this->info[0];
			$height = $this->info[1];
			
			if ( $width > $height ) {
				if ( $width > $size ) {
					$ratio = $width/$height;
		
					$width = $size*$ratio;
					$height = $size;

					$x = ($width - $size)/2;
				}
			} else {
				if ( $height > $size ) {
					$ratio = $width/$height;
		
					$height = $size/$ratio;
					$width = $size;
					
					$x = ($height - $size)/2;
				}
			}
			
			$destImg = imagecreatetruecolor( $size, $size );
				
			if ( function_exists('imagecopyresampled') )
				imagecopyresampled ( $destImg, $this->imgRes, -$x, 0, 0, 0, $size+$x, $size, $this->info[0], $this->info[1] );
			else
				imagecopyresized ( $destImg, $this->imgRes, -$x, 0, 0, 0, $size+$x, $size, $this->info[0], $this->info[1] );			
			
			$this->imgRes = $destImg;
			return $this;
		}
		
		public function outputImage($quality = null) {
			// Set the content type header - in this case image/jpeg
			header('Content-type: '.$this->info['mime']);
			
			// Output the image
			if ($quality)
				imagejpeg($this->imgRes, null, $quality);
			else
				imagejpeg($this->imgRes);
			
			// Free up memory
			imagedestroy($this->imgRes);			
		}

		public function writeImage($path, $quality = null) {
			// Output the image			
			if ($quality)
				imagejpeg($this->imgRes, $path, $quality);
			else
				imagejpeg($this->imgRes, $path);
		
			return $this;
		}
		
		public function destroy()
		{
			imagedestroy($this->imgRes);
		}
		
		/**
		 * @see http://vikjavev.no/computing/ump.php?id=35
		 */
		public function UnsharpMask($amount, $radius, $threshold)    { 

            ////////////////////////////////////////////////////////////////////////////////////////////////  
            ////  
            ////                  Unsharp Mask for PHP - version 2.1.1  
            ////  
            ////    Unsharp mask algorithm by Torstein Hønsi 2003-07.  
            ////             thoensi_at_netcom_dot_no.  
            ////               Please leave this notice.  
            ////  
            ///////////////////////////////////////////////////////////////////////////////////////////////  
            
            $img = $this->imgRes;
            
                // $img is an image that is already created within php using 
                // imgcreatetruecolor. No url! $img must be a truecolor image. 
            
                // Attempt to calibrate the parameters to Photoshop: 
                if ($amount > 500)    $amount = 500; 
                $amount = $amount * 0.016; 
                if ($radius > 50)    $radius = 50; 
                $radius = $radius * 2; 
                if ($threshold > 255)    $threshold = 255; 
                 
                $radius = abs(round($radius));     // Only integers make sense. 
                if ($radius == 0) { 
                    return $img; imagedestroy($img); break;        } 
                $w = imagesx($img); $h = imagesy($img); 
                $imgCanvas = imagecreatetruecolor($w, $h); 
                $imgBlur = imagecreatetruecolor($w, $h); 
                 
            
                // Gaussian blur matrix: 
                //                         
                //    1    2    1         
                //    2    4    2         
                //    1    2    1         
                //                         
                ////////////////////////////////////////////////// 
                     
            
                if (function_exists('imageconvolution')) { // PHP >= 5.1  
                        $matrix = array(  
                        array( 1, 2, 1 ),  
                        array( 2, 4, 2 ),  
                        array( 1, 2, 1 )  
                    );  
                    imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h); 
                    imageconvolution($imgBlur, $matrix, 16, 0);  
                }  
                else {  
            
                // Move copies of the image around one pixel at the time and merge them with weight 
                // according to the matrix. The same matrix is simply repeated for higher radii. 
                    for ($i = 0; $i < $radius; $i++)    { 
                        imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left 
                        imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right 
                        imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center 
                        imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h); 
            
                        imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up 
                        imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down 
                    } 
                } 
                
                if($threshold>0){ 
                    // Calculate the difference between the blurred pixels and the original 
                    // and set the pixels 
                    for ($x = 0; $x < $w-1; $x++)    { // each row
                        for ($y = 0; $y < $h; $y++)    { // each pixel 
                                 
                            $rgbOrig = ImageColorAt($img, $x, $y); 
                            $rOrig = (($rgbOrig >> 16) & 0xFF); 
                            $gOrig = (($rgbOrig >> 8) & 0xFF); 
                            $bOrig = ($rgbOrig & 0xFF); 
                             
                            $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
                             
                            $rBlur = (($rgbBlur >> 16) & 0xFF); 
                            $gBlur = (($rgbBlur >> 8) & 0xFF); 
                            $bBlur = ($rgbBlur & 0xFF); 
                             
                            // When the masked pixels differ less from the original 
                            // than the threshold specifies, they are set to their original value. 
                            $rNew = (abs($rOrig - $rBlur) >= $threshold)  
                                ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))  
                                : $rOrig; 
                            $gNew = (abs($gOrig - $gBlur) >= $threshold)  
                                ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))  
                                : $gOrig; 
                            $bNew = (abs($bOrig - $bBlur) >= $threshold)  
                                ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))  
                                : $bOrig; 
                             
                             
                                         
                            if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) { 
                                    $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew); 
                                    ImageSetPixel($img, $x, $y, $pixCol); 
                                } 
                        } 
                    } 
                } 
                else{ 
                    for ($x = 0; $x < $w; $x++)    { // each row 
                        for ($y = 0; $y < $h; $y++)    { // each pixel 
                            $rgbOrig = ImageColorAt($img, $x, $y); 
                            $rOrig = (($rgbOrig >> 16) & 0xFF); 
                            $gOrig = (($rgbOrig >> 8) & 0xFF); 
                            $bOrig = ($rgbOrig & 0xFF); 
                             
                            $rgbBlur = ImageColorAt($imgBlur, $x, $y); 
                             
                            $rBlur = (($rgbBlur >> 16) & 0xFF); 
                            $gBlur = (($rgbBlur >> 8) & 0xFF); 
                            $bBlur = ($rgbBlur & 0xFF); 
                             
                            $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig; 
                                if($rNew>255){$rNew=255;} 
                                elseif($rNew<0){$rNew=0;} 
                            $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig; 
                                if($gNew>255){$gNew=255;} 
                                elseif($gNew<0){$gNew=0;} 
                            $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig; 
                                if($bNew>255){$bNew=255;} 
                                elseif($bNew<0){$bNew=0;} 
                            $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew; 
                                ImageSetPixel($img, $x, $y, $rgbNew); 
                        } 
                    } 
                } 
                imagedestroy($imgCanvas); 
                imagedestroy($imgBlur); 
                 
                $this->imgRes = $img;
                return $img; 
            
            } 
		
	}

?>