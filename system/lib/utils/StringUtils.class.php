<?php
	class StringUtils 
	{
		static function translit($string, $space = '-')
		{
		    $table = array(
		                'А' => 'A',
		                'Б' => 'B',
		                'В' => 'V',
		                'Г' => 'G',
		                'Д' => 'D',
		                'Е' => 'E',
		                'Ё' => 'YO',
		                'Ж' => 'ZH',
		                'З' => 'Z',
		                'И' => 'I',
		                'Й' => 'J',
		                'К' => 'K',
		                'Л' => 'L',
		                'М' => 'M',
		                'Н' => 'N',
		                'О' => 'O',
		                'П' => 'P',
		                'Р' => 'R',
		                'С' => 'S',
		                'Т' => 'T',
		                'У' => 'U',
		                'Ф' => 'F',
		                'Х' => 'H',
		                'Ц' => 'C',
		                'Ч' => 'CH',
		                'Ш' => 'SH',
		                'Щ' => 'CSH',
		                'Ь' => '',
		                'Ы' => 'Y',
		                'Ъ' => '',
		                'Э' => 'E',
		                'Ю' => 'YU',
		                'Я' => 'YA',
		
		                'а' => 'a',
		                'б' => 'b',
		                'в' => 'v',
		                'г' => 'g',
		                'д' => 'd',
		                'е' => 'e',
		                'ё' => 'yo',
		                'ж' => 'zh',
		                'з' => 'z',
		                'и' => 'i',
		                'й' => 'j',
		                'к' => 'k',
		                'л' => 'l',
		                'м' => 'm',
		                'н' => 'n',
		                'о' => 'o',
		                'п' => 'p',
		                'р' => 'r',
		                'с' => 's',
		                'т' => 't',
		                'у' => 'u',
		                'ф' => 'f',
		                'х' => 'h',
		                'ц' => 'c',
		                'ч' => 'ch',
		                'ш' => 'sh',
		                'щ' => 'csh',
		                'ь' => '',
		                'ы' => 'y',
		                'ъ' => '',
		                'э' => 'e',
		                'ю' => 'yu',
		                'я' => 'ya',
		    			' ' => $space
		    );
		
		    $output = str_replace(
		        array_keys($table),
		        array_values($table),$string
		    );
		
		    return $output;
		}

		static function minimize($string, $maxLength=60, $left=15, $rigth=30) {
			$str = $string;
			if ( mb_strlen( $string ) > $maxLength ) {
				$str = mb_substr( $string, 0, $left ) . '...' . mb_substr( $string, -$rigth );
			}
			return $str;
		}
		
		static function truncate($string, $maxLength=20) {
		    $str = $string;
		    if ( mb_strlen( $string ) > $maxLength ) {
				$str = mb_substr( $string, 0, $maxLength ) . '...';
			}
			return $str;
		}
	}
?>
