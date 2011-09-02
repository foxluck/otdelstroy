<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     getuseragent
 * Purpose:  return information about user browser agent and OS version as array
 * -------------------------------------------------------------
 */

function smarty_function_getuseragent( $params, &$ths )
{
	extract($params);

	$curos=strtolower($_SERVER['HTTP_USER_AGENT']);
	$uip=$_SERVER['REMOTE_ADDR'];
	$uht=gethostbyaddr($_SERVER['REMOTE_ADDR']);

	if (strstr($curos,"mac")) {
		$uos="MacOS";
	} else
		if (strstr($curos,"linux")) {
			$uos="Linux";
		} else
			if (strstr($curos,"win")) {
				$uos="Windows";
			} else
				if (strstr($curos,"bsd")) {
					$uos="BSD";
				} else
					if (strstr($curos,"qnx")) {
						$uos="QNX";
					} else
						if (strstr($curos,"sun")) {
							$uos="SunOS";
						} else
							if (strstr($curos,"solaris")) {
								$uos="Solaris";
							} else
								if (strstr($curos,"irix")) {
									$uos="IRIX";
								} else
									if (strstr($curos,"aix")) {
										$uos="AIX";
									} else
										if (strstr($curos,"unix")) {
											$uos="Unix";
										} else
											if (strstr($curos,"amiga")) {
												$uos="Amiga";
											} else
												if (strstr($curos,"os/2")) {
													$uos="OS/2";
												} else
													if (strstr($curos,"beos")) {
														$uos="BeOS";
													} else {
														$uos="[?]EgzoticalOS";
													}

	if (strstr($curos,"gecko")) {
		if (strstr($curos,"safari")) {
			$bos="Safari";
		} else
			if (strstr($curos,"camino")) {
				$bos="Camino";
			} else
				if (strstr($curos,"firefox")) {
					$bos="Firefox";
				} else
					if (strstr($curos,"netscape")) {
						$bos="Netscape";
					} else {
						$bos="Mozilla";
					}
	} else
		if (strstr($curos,"opera")) {
			$bos="Opera";
		} else
			if (strstr($curos,"msie")) {
				$bos="Internet Exploder";
			} else
				if (strstr($curos,"voyager")) {
					$bos="Voyager";
				} else
					if (strstr($curos,"lynx")) {
						$bos="Lynx";
					} else {
						$bos="[?]EgzoticalBrowser";
					}

	$result = array( "OS"=>$uos, "Agent"=>$bos );
	$ths->assign( $assign, $result );
}

?>
