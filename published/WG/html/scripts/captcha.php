<?
	require_once( "includes/captcha_pi.php" );

	
	$word = "";
	for ($i = 0; $i < 4; $i++) {
		$word .= rand(0,10000) % 10;
	}
	
	$vals = array(
					'word'		 => $word,
					'img_path'	 => '../../../../temp/',
					'img_url'	 => 'includes/captcha/',
					'font_path'	 => 'includes/ANTQUAB.TTF',
					'img_width'	 => '120',
					'img_height' => 40,
					'expiration' => 7200
				);

	global $cap;
	$cap = create_captcha($vals);
	session_cache_limiter('nocache');
	session_start();
	$_SESSION['captcha_word'] = $cap['word'];
	
	$filename = $vals['img_path'] . $cap["time"] . ".jpg";
	
	header("Content-type: image/jpeg");
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header('P3P: CP="CAO PSA OUR"');
	readfile($filename);
	unlink($filename);
?>