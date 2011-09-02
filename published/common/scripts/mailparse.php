<?php
//
// Mail Master e-mail address parsers
//

function parseAddressString($addr, $addName=true, $cutQuotes=false)
{
	if(!trim($addr))
		return false;

	if(is_array($addr)) $str = implode(', ', $addr);
	else $str = $addr;

	if($str == '') return false;
	$accept = $bounse = array();

	while($str != '') {

		$str = cut_it($str, $prs, $eml);
		if($prs.$eml == '') continue;
		// login@domain.zone or nobody@[10.10.10.1]
		if(!preg_match("/^[0-9a-z-_\.]+@([0-9a-z][0-9a-z-\.]+)\.[a-z]{2,4}$/i", $eml)
		&& !preg_match("/^[0-9a-z-_\.]+@\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\]$/i", $eml)) {
			$bounse[] = $eml;
		} else {
			if($addName) {
				$em = explode('@', $eml);
				// If name is absent => take 1-st part of e-mail
				if($prs == '') {
					$prs = $em[0]; // $prs = preg_replace("/^(.+)@.+$/", "$1", $eml);
				}
				$prs = str_replace(',', '', $prs); // deny separators
				$prs = str_replace(';', '', $prs);
			}
			if($cutQuotes || $addName) {
				$prs = preg_replace('/^\s*"\s*(.*?)\s*"\s*$/', "$1", $prs); // deny pair "
				$prs = preg_replace("/^\s*'\s*(.*?)\s*'\s*$/", "$1", $prs); // deny pair '
			}
			$accept[] = array('name'=>trim($prs), 'email'=>strtolower(trim($eml)));
		}
	}
	return array('accepted'=>$accept, 'bounced'=>$bounse);
}

// Internal subfunction for parseAddressString
function cut_it($str, &$pr, &$em)
{
	$p1 = strpos($str,',');	// find near'st separator
	$p2 = strpos($str,';');
	if($p1 === false) $dlm = $p2;
	elseif($p2 === false) $dlm = $p1;
	else $dlm = min($p1,$p2); // $dlm is the position of first separator

	$p3 = strpos($str, '"'); // find near'st "
	if($p3 !== false && $p3<$dlm)
	{ // " place before separator => take first part with e-mail
		$p4 = strpos($str, '"', $p3+1);
		$p1 = strpos($str, ',', $p4);
		$p2 = strpos($str, ';', $p4);
		if($p1 === false) $dlm = $p2;
		elseif($p2 === false) $dlm = $p1;
		else $dlm = min($p1,$p2);
	}
	// $dlm have separator !... or FALSE if no separators
	if($dlm!==false)
		$eml = substr($str, 0, $dlm);
	else
		$eml = $str;

	// breaks it for name and e-mail
	if(preg_match("/^(.*?)\s*<\s*(.*?)\s*>\s*$/", $eml, $matches))
	{
		$pr = $matches[1];
		$em = $matches[2];
	}else{
		$eml = trim($eml);
		if(!strpos($eml, '@') && !preg_match("/^[0-9a-z-_\.]+$/i",$eml))
		{
			$pr = $eml;
			$em = '';
		}else{
			$pr = '';
			$em = $eml;
		}
	}
	$str= substr($str, strlen($eml)+1);

	if(($pr=='') && ($em=='') && ($str!='')) // empty part is cutted
		$str = cut_it($str, $pr, $em); // call ones more!

	return $str;
}

function joinContactAddress($item, $htmlSafe = false)
{
	$lt = ($htmlSafe) ? '&lt;' : '<';
	$gt = ($htmlSafe) ? '&gt;' : '>';
	$str = array();
	if($item['C_FIRSTNAME']) $str[] = $item['C_FIRSTNAME'];
	if($item['C_LASTNAME']) $str[] = $item['C_LASTNAME'];
	if($item['C_EMAILADDRESS']) $str[] = $lt . $item['C_EMAILADDRESS'] . $gt;
	return join(' ', $str);
}

function html2plain($str)
{
	$str = html_decode($str);
	$str = cut_scripts($str);

	$str = preg_replace("/\r/", '', $str);

	// add space for text lines with LF
	$str = preg_replace("/([^>\n]{1})\n+([^\n<]{1})/", "\$1 \$2", $str);

	// delete all spaces between tags (inc. \r\n)
	$str = preg_replace("/(>)\s+(<)/", "\$1\$2", $str);

	$str = preg_replace("/[\r\n]/", '', $str); // delete all other r&n

	$str = preg_replace("/[ \f\t]+/", ' ', $str); // compress spaces

	// replace some tags to LF
	$str = preg_replace("/<(br|p|\/?div|table|tr|ul|li)[^>]*>\n*/i", "\n", $str);
	$str = preg_replace("/<hr[^>]*>\n*/i", "\n\n", $str);

	$str = strip_tags($str); // cut all other tags

	$str = preg_replace("/^\s+/", '', $str); // trim begin
	$str = preg_replace("/\s+$/", '', $str); // trim end
	$str = preg_replace("/(\n{3,})/", "\n\n", $str); // limit LF

	return $str;
}

function html_decode($str)
{
	$trans = get_html_translation_table(HTML_ENTITIES);
	$trans = array_flip($trans);
	return strtr($str, $trans);
}

function cut_scripts($str)
{
	$str = preg_replace('/<script(.*?)>(.*?)<\/script(.*?)>/is', '', $str);
	$str = preg_replace('/<object(.*?)>(.*?)<\/object(.*?)>/is', '', $str);
	return preg_replace('/<\?(.*?)\?'.'>/s', '', $str);
}

?>