<?php
class Html2wiki{

	#
	#  HTML to Wiki Converter - tables
	#  converts the HTML table tags into their wiki equivalents,
	#  which were developed by Magnus Manske and are used in MediaWiki
	#
	#  Copyright (C) 2004 Borislav Manolov
	#
	#  This program is free software; you can redistribute it and/or
	#  modify it under the terms of the GNU General Public License
	#  as published by the Free Software Foundation; either version 2
	#  of the License, or (at your option) any later version.
	#
	#  This program is distributed in the hope that it will be useful,
	#  but WITHOUT ANY WARRANTY; without even the implied warranty of
	#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	#  GNU General Public License for more details.
	#
	#  Author: Borislav Manolov <b.manolov at gmail.com>
	#          http://purl.org/NET/borislav
	#############################################################################

	/**
	 *
	 * @param $str	the HTML markup
	 * @param $row_delim	number of dashes used for a row
	 * @param $oneline	use one-line markup for cells - ||
	 * @param $escape	Escape some symbols from the wiki table markup
	 */
	public static function convert($str, $row_delim = 1, $oneline = false, $escape = false)
	{

		$str = str_replace("\r", '', $str);
		if ($escape) {
			$str = strtr($str, array('!'=>'&#33;', '|'=>'&#124;'));
		}

		$my_nl = '=N=';
		$html_tags = array(
		# since PHP 5 str_ireplace() can be used for the end tags
		"/\n/",
		'/>\s+</',         # spaces between tags
		'/<\/table>/i',    # table end
		'/<\/caption>/i',  # caption end
		'/<\/tr>/i',       # rows end
		'/<\/th>/i',       # headers end
		'/<\/td>/i',       # cells end
		# e - replacement string gets evaluated before the replacement
		'/<table([^>]*)>/ie', # table start
		'/<caption>/i',    # caption start
		'/<tr(.*)>/Uie', # row start
		'/<th(.*)>/Uie', # header start
		'/<td(.*)>/Uie', # cell start
		"/\n$my_nl/",
		"/$my_nl/",
		"/\n */",          # spaces at beginning of a line
		);

		$wiki_tags = array(
		" \n",
		'><',        # remove spaces between tags
		"$my_nl|}",      # table end
		'', '', '', '',  # caption, rows, headers & cells end
		"'$my_nl{| '.trim(Html2wiki::strip_newlines('$1'))",     # table start
		"$my_nl|+",      # caption
		"'$my_nl|'.str_repeat('-', $row_delim).' '.trim(Html2wiki::strip_newlines('$1'))", # rows
		"'$my_nl! '.trim(Html2wiki::strip_newlines('$1')).' | '", # headers
		"'$my_nl| '.trim(Html2wiki::strip_newlines('$1')).' | '", # cells
		"\n",
		"\n",
		"\n",
		);

		# replace html tags with wiki equivalents
		$str = preg_replace($html_tags, $wiki_tags, $str);

		# remove table row after table start
		$str = preg_replace("/\{\|(.*)\n\|-+ *\n/", "{|$1\n", $str);

		# clear phase
		$s = array('!  |', '|  |', '\\"');
		$r = array('!'   , '|'   ,   '"');
		$str = str_replace($s, $r, $str);

		# use one-line markup for cells
		if ($oneline) {
			$prevcell = false; # the previous row is a table cell
			$prevhead = false; # the previous row is a table header
			$pos = -1;
			while ( ($pos = strpos($str, "\n", $pos+1)) !== false ) { #echo "\n$str\n";
				switch ($str{$pos+1}) {
					case '|': # cell start
						if ($prevcell && $str{$pos+2} == ' ') {
							$str = substr_replace($str, ' |', $pos, 1); # s/\n/ |/
						} else if ($str{$pos+2} == ' ') {
							$prevcell = true;
						} else {
							$prevcell = false;
						}
						$prevhead = false;
						break;
					case '!': # header cell start
						if ($prevhead) {
							$str = substr_replace($str, ' !', $pos, 1); # s/\n/ !/
						} else {
							$prevhead = true;
						}
						$prevcell = false;
						break;
					case '{': # possible table start
						if ($str{$pos+2} == '|') { # table start
							$prevcell = $prevhead = false;
						} else {
							$str{$pos} = ' ';
						}
						break;
					default: $str{$pos} = ' ';
				}
			}
		}
		return $str;
	}

	public static function strip_newlines($str) {
		return str_replace("\n", '', $str);
	}
}
?>