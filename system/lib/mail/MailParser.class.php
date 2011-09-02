<?php

class MailParser 
{
	public static function address($addr, &$errors = false)
	{
		if (!trim($addr)) {
			return false;
		}

		if (is_array($addr)) {
			$str = implode(', ', $addr);		
		} else {
			$str = $addr;	
		}

		if ($str == '') {
			return false;	
		}
		
		$accept = $bounse = array();
		$result = array();		
		while ($str != '') {
			$str = self::addressPart($str, $prs, $eml);
			if ($prs.$eml == '') {
				continue;	
			}
			if (!filter_var($eml, FILTER_VALIDATE_EMAIL)) {
				$errors[] = $eml;
			} else {
				$result[] = array(
					'name' => trim($prs, '" '), 
					'email'=>strtolower(trim($eml))
				);
			}
		}
		return $result;
	}
	
	protected static function addressPart($str, &$pr, &$em)
	{
		$p1 = strpos($str,',');	// find near'st separator
		$p2 = strpos($str,';');
		if ($p1 === false) {
			$dlm = $p2;	
		} elseif($p2 === false) {
			$dlm = $p1;	
		} else {
			$dlm = min($p1, $p2); // $dlm is the position of first separator	
		}

		$p3 = strpos($str, '"'); // find near'st "
		if($p3 !== false && $p3 < $dlm)
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
		if (preg_match("/^(.*?)\s*<\s*(.*?)\s*>\s*$/", $eml, $match)) {
			$pr = $match[1];
			$em = $match[2];
		} else {
			$eml = trim($eml);
			if (!strpos($eml, '@') && !preg_match("/^[0-9a-z-_\.]+$/i",$eml)) {
				$pr = $eml;
				$em = '';
			} else {
				$pr = '';
				$em = $eml;
			}
		}
		$str = substr($str, strlen($eml) + 1);

		if (($pr=='') && ($em=='') && ($str!='')) {
			$str = self::addressPart($str, $pr, $em); 
		}
		return $str;
	}
	
	public static function body($text, $attachments = array(), $key = false) 
	{
		if ($attachments) {
			foreach ($attachments as $n => $file) {
				if (isset($file['content-id'])) {
					$url = "?m=requests&act=attachment&".$key."&n=".$n."&file=".$file['file'];
					$text = str_replace("cid:" . $file['content-id'], $url, $text);
				}
			}
		}	
		return $text;
	}
	
}