<?php
class IValidator
{
	var $Themes;
	var $Fonts;
	var $Width = 120;
	var $Height = 40;
	var $BorderWidth = 1;
	var $ImageType = 'jpeg';
	var $FontsDir;
	var $RndCodes = '0123456789';
	var $RndLength = 4;
	var $AppId;
	
	function IValidator(){
		
		$this->Themes = array (
			array(
				'background' => array(225, 60, 50),
				'border' => array(0, 0, 0),
				'font' => array(0, 0, 0),
			),
			array(
				'background' => array(205, 255, 204),
				'border' => array(0, 0, 0),
				'font' => array(0, 0, 0),
			),
			array(
				'background' => array(252,252,150),
				'border' => array(0, 0, 0),
				'font' => array(60,60,150),
			),
			array(
				'background' => array(160, 160, 227),
				'border' => array(0, 0, 0),
				'font' => array(32,69,38),
			),
		);
		$this->Fonts = array(
			'arial.ttf',
			'verdana.ttf',
		);

		$this->FontsDir = preg_replace('/\/published$/', '', $_SERVER['DOCUMENT_ROOT']).'/published/common/fonts';
	}
	
	function generateImage($_Code = ''){
		$this->__createCaptcha($_Code);
	}

	function rndCode(){
		
		$l_name='';
		$top = strlen($this->RndCodes)-1;
		srand((double) microtime()*1000000);
		for($j=0; $j<$this->RndLength; $j++) {
			$l_name .= $this->RndCodes{rand(0,$top)};
		}
		return $l_name;
	}
	
	function storeCode($_Code) {
		$_SESSION[$this->AppId.'_IVAL'] = $_Code;
	}
	
	function checkCode($_Code){
		
		if(!session_is_registered($this->AppId.'_IVAL'))return false;
		if(!$_Code)return false;
		if(strtolower($_SESSION[$this->AppId.'_IVAL']) == strtolower($_Code)){
			$_SESSION[$this->AppId.'_IVAL'] = '';
			return true;
		}else{
			
			$_SESSION[$this->AppId.'_IVAL'] = '';
			return false;
		}
	}

	function __generateImage($_Code = ''){
		
		
		if(!$_Code) {
			$_Code = $this->rndCode();
		}
		
		$this->storeCode($_Code);
		
		$Theme = mt_rand(0, count($this->Themes)-1);
		$Theme = $this->Themes[$Theme];
		$FontFile = mt_rand(0, count($this->Fonts)-1);
		$FontFile = $this->FontsDir.'/'.$this->Fonts[$FontFile];

		if(function_exists('imagecreatetruecolor'))
			$Image = imagecreatetruecolor($this->Width, $this->Height);
		else 
			$Image = imagecreate($this->Width, $this->Height);
		
		$Fill   = ImageColorAllocate($Image, $Theme['background'][0], $Theme['background'][1], $Theme['background'][2]);
		$Border = ImageColorAllocate($Image, $Theme['border'][0], $Theme['border'][1], $Theme['border'][2]);
		
		ImageFilledRectangle($Image, $this->BorderWidth, $this->BorderWidth, $this->Width-$this->BorderWidth-1, $this->Height-$this->BorderWidth-1, $Fill);
		ImageRectangle($Image, 0, 0, $this->Width-1, $this->Height-1, $Border);
		
	        $Font	= imagecolorallocate($Image, $Theme['font'][0], $Theme['font'][1], $Theme['font'][2]);
	
	        $TrFontSize = 14;
	        $_TC = strlen($_Code)-1;
	        $LettersStart = 5;
	        $LetterOffset = ceil(($this->Width-$LettersStart*2)/($_TC+1));
	        for(;$_TC>=0;$_TC--){
	        	
	        	$RSize = mt_rand(3, 5);
	        	imagestring($Image,$RSize,$LettersStart+($_TC)*$LetterOffset, $RSize*2+$RSize, $_Code{$_TC}, $Font);
	        }
	        
		if(0 && function_exists('imagecreatetruecolor')){
			
		        $TrFont 	= imagecolorallocatealpha($Image, $Theme['font'][0], $Theme['font'][1], $Theme['font'][2], 100);  
		        $TrFontSize = 20;
		        $_TC = strlen($_Code)-1;
		        $LetterOffset = ceil($this->Width/($_TC+1));
		        for(;$_TC>=0;$_TC--){
		        	
		        	$RSize = mt_rand(1, 5);
		        	imagettftext($Image, $TrFontSize+$RSize, 0, ($_TC)*$LetterOffset, 25+$RSize, $TrFont, $FontFile, $_Code{$_TC});
		        }
		}
		
	        if($this->ImageType == "jpeg"){
	        	
	            header("Content-type: image/jpeg");
	            imagejpeg($Image, false, 95);
	        }else{
	        	
	            header("Content-type: image/png");
	            imagepng($Image);
	        }
	
	        imagedestroy($Image);
	}
	
	function __createCaptcha($_Code){
		
		if(!$_Code)$_Code = $this->rndCode();
		
		$this->storeCode($_Code);
		
		$vals = array(
						'word'		 => $_Code,
						'img_path'	 => '', //DIR_PRODUCTS_PICTURES,
						'img_url'	 => '', //DIR_PRODUCTS_PICTURES,
						'font_path'	 => $this->FontsDir.'/ANTQUAB.TTF',
						'img_width'	 => $this->Width,
						'img_height' => $this->Height,
						'expiration' => 7200
					);
	
		$cap = $this->create_captcha($vals);
	}
	
	function create_captcha($data = '', $img_path = '', $img_url = '', $font_path = '')
	{		
		$defaults = array('word' => '', 'img_path' => '', 'img_url' => '', 'img_width' => '150', 'img_height' => '30', 'font_path' => '', 'expiration' => 7200);		
		
		foreach ($defaults as $key => $val)
		{
			if ( ! is_array($data))
			{
				if ( ! isset($$key) OR $$key == '')
				{
					$$key = $val;
				}
			}
			else
			{			
				$$key = ( ! isset($data[$key])) ? $val : $data[$key];
			}
		}
		if ( ! extension_loaded('gd'))
		{
			return FALSE;
		}		
		
		// -----------------------------------
		// Remove old images	
		// -----------------------------------
				
		list($usec, $sec) = explode(" ", microtime());
		$now = ((float)$usec + (float)$sec);

		// -----------------------------------
		// Do we have a "word" yet?
		// -----------------------------------
		
	   if ($word == '')
	   {
			$pool = '023456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
			$str = '';
			for ($i = 0; $i < 5; $i++) 
			{    
				$str .= strtolower(substr($pool, mt_rand(0, strlen($pool) -1), 1)); 
			}
			
			$word = $str;
	   } 
		
		// -----------------------------------
		// Determine angle and position	
		// -----------------------------------
		
		$length	= strlen($word);
		$angle	= ($length >= 6) ? rand(-($length-6), ($length-6)) : 0;
		$x_axis	= rand(6, (360/$length)-16);			
		$y_axis = ($angle >= 0 ) ? rand($img_height, $img_width) : rand(6, $img_height);
		
		// -----------------------------------
		// Create image
		// -----------------------------------
				
		$im = ImageCreate($img_width, $img_height);
				
		// -----------------------------------
		//  Assign colors
		// -----------------------------------
		
		$bg_color		= ImageColorAllocate($im, 255, 255, 255);
		$border_color	= ImageColorAllocate($im, 255, 255, 255);
		$text_color		= ImageColorAllocate($im, 254, 53, 53);
		$grid_color		= imagecolorallocate($im, 255, 182, 182);
		$shadow_color	= imagecolorallocate($im, 255, 240, 240);
	
		// -----------------------------------
		//  Create the rectangle
		// -----------------------------------
		
		ImageFilledRectangle($im, 0, 0, $img_width, $img_height, $bg_color);
		
		// -----------------------------------
		//  Create the spiral pattern
		// -----------------------------------
		
		$theta		= 1;
		$thetac		= 7;  
		$radius		= 16;  
		$circles	= 20;  
		$points		= 32;
	
		for ($i = 0; $i < ($circles * $points) - 1; $i++) 
		{
			$theta = $theta + $thetac;
			$rad = $radius * ($i / $points );
			$x = ($rad * cos($theta)) + $x_axis;
			$y = ($rad * sin($theta)) + $y_axis;
			$theta = $theta + $thetac;
			$rad1 = $radius * (($i + 1) / $points);
			$x1 = ($rad1 * cos($theta)) + $x_axis;
			$y1 = ($rad1 * sin($theta )) + $y_axis;
			imageline($im, $x, $y, $x1, $y1, $grid_color);
			$theta = $theta - $thetac;
		}
	
		// -----------------------------------
		//  Write the text
		// -----------------------------------
		
		$use_font = ($font_path != '' AND file_exists($font_path) AND function_exists('imagettftext')) ? TRUE : FALSE;

		if ($use_font == FALSE)
		{
			$font_size = 5;
			$x = rand(0, $img_width/($length/3));
			$y = 0;
		}
		else
		{
			$font_size	= 16;
			$x = rand(0, $img_width/($length/1.5));
			$y = $font_size+2;
		}

		for ($i = 0; $i < strlen($word); $i++)
		{
			if ($use_font == FALSE)
			{
				$y = rand(0 , $img_height/2);
				imagestring($im, $font_size, $x, $y, substr($word, $i, 1), $text_color);
				$x += ($font_size*2);
			}
			else
			{	
				$y = rand($img_height/2, $img_height-3);
				imagettftext($im, $font_size, $angle, $x, $y, $text_color, $font_path, substr($word, $i, 1));
				$x += $font_size;
			}
		}
		
	
		// -----------------------------------
		//  Create the border
		// -----------------------------------
	
		imagerectangle($im, 0, 0, $img_width-1, $img_height-1, $border_color);		
	
		// -----------------------------------
		//  Generate the image
		// -----------------------------------
		
		$img_name = $now.'.jpg';

        if($this->ImageType == "jpeg"){
        	
            header("Content-type: image/jpeg");
            imagejpeg($im, false, 95);
        }else{
        	
            header("Content-type: image/png");
            imagepng($im);
        }

		ImageDestroy($im);

		return array('word' => $word, 'time' => $now, 'image' => $img_name);
	}
}
?>