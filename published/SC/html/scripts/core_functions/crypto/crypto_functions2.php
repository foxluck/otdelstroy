<?php
class DWord
{
	var $bitArray;

	function DWord()
	{
		$this->bitArray = array();
		for( $i=1; $i<=32;  $i++) 
			$this->bitArray[$i-1] = 0;
	}

	function _setByte( $byte, $displacement )
	{
		// 00000001 = 1
		$this->bitArray[$displacement + 0] = (($byte&1)   != 0)?1:0;
		// 00000010 = 2
		$this->bitArray[$displacement + 1] = (($byte&2)   != 0)?1:0;
		// 00000100 = 4
		$this->bitArray[$displacement + 2] = (($byte&4)   != 0)?1:0;
		// 00001000 = 8
		$this->bitArray[$displacement + 3] = (($byte&8)   != 0)?1:0;
		// 00010000 = 16
		$this->bitArray[$displacement + 4] = (($byte&16)  != 0)?1:0;
		// 00100000 = 32
		$this->bitArray[$displacement + 5] = (($byte&32)  != 0)?1:0;
		// 01000000 = 64
		$this->bitArray[$displacement + 6] = (($byte&64)  != 0)?1:0;
		// 10000000 = 128
		$this->bitArray[$displacement + 7] = (($byte&128) != 0)?1:0;
	}

	function _getByte( $displacement )
	{
		return $this->bitArray[$displacement + 0]*1  + 
					$this->bitArray[$displacement + 1]*2 + 
					$this->bitArray[$displacement + 2]*4 + 
					$this->bitArray[$displacement + 3]*8 + 
					$this->bitArray[$displacement + 4]*16 + 
					$this->bitArray[$displacement + 5]*32 + 
					$this->bitArray[$displacement + 6]*64 + 
					$this->bitArray[$displacement + 7]*128;
	}

	function SetValue( $byte1, $byte2, $byte3, $byte4  )
	{
		$this->_setByte( $byte1, 0  );
		$this->_setByte( $byte2, 8  );
		$this->_setByte( $byte3, 16 );
		$this->_setByte( $byte4, 24 );
	}

	function GetValue( &$byte1, &$byte2, &$byte3, &$byte4 )
	{
		$byte1 = $this->_getByte( 0  );
		$byte2 = $this->_getByte( 8  );
		$byte3 = $this->_getByte( 16 );
		$byte4 = $this->_getByte( 24 );
	}

	function GetCount()
	{
		$coeff = 1;
		$res = 0;
		for($i=1; $i<=32; $i++)
		{
			$res += $this->bitArray[$i-1]*$coeff;
			$coeff *= 2;
		}
		return $res;
	}

	function SetBit( $bitValue, $bitIndex  )
	{
		$this->bitArray[ $bitIndex ] = $bitValue;
	}

	function GetHTML_Representation()
	{
		$res = "";
		$res .= "<table border='1'>";

		// head row
		$res .= "	<tr>";
		for( $i=31; $i>=0; $i-- )
		{
			$res .= "		<td>";
			$res .= "			$i";
			$res .= "		</td>";
		}
		$res .= "	</tr>";

		// bit values 
		$res .= "	<tr>";
		for( $i=31; $i>=0; $i-- ) 
		{
			$res .= "		<td>";
			$res .= "			".$this->bitArray[$i];
			$res .= "		</td>";
		}
		$res .= "	</tr>";
		$res .= "</table>";

		return $res;
	}

	function ShiftToLeft( $countBit )
	{
		$resBitArray = $this->bitArray;

		for( $i=31; $i>=0; $i-- )
			if ( $i +  $countBit <= 31 )
				$resBitArray[$i + $countBit] = $resBitArray[$i];

		for( $i=1; $i<=$countBit; $i++ )
			$resBitArray[$i-1]=0;

		$res = new DWord();
		$res->bitArray = $resBitArray;
		return $res;
	}

	function ShiftToRight( $countBit )
	{
		$resBitArray = $this->bitArray;

		for( $i=0; $i<=31; $i++ )
			if ( $i -  $countBit >= 0 )
				$resBitArray[$i - $countBit] = $resBitArray[$i];

		for( $i=31; $i>=31-$countBit+1; $i-- )
			$resBitArray[$i]=0;

		$res = new DWord();
		$res->bitArray = $resBitArray;
		return $res;
	}

	function BitwiseOR( $dwordObject )
	{
		$res = new DWord();
		for( $i=0; $i<=31; $i++ )
		{
			if ( $this->bitArray[$i]+$dwordObject->bitArray[$i] != 0 )
				$res->SetBit( 1, $i );
			else
				$res->SetBit( 0, $i );
		}
		return $res;
	}

	function BitwiseAND( $dwordObject )
	{
		$res = new DWord();
		for( $i=0; $i<=31; $i++ )
			$res->SetBit( $this->bitArray[$i]*$dwordObject->bitArray[$i], 
						$i );
		return $res;
	}

	function BitwiseXOR( $dwordObject )
	{
		$res = new DWord();
		for( $i=0; $i<=31; $i++ )
		{
			if ($this->bitArray[$i] == $dwordObject->bitArray[$i])
				$res->SetBit( 1, $i );
			else
				$res->SetBit( 0, $i );
		}
		return $res;
	}

	function Plus( $dwordObject )
	{
		$res = new DWord();
		$cf = 0;
		for( $i=0; $i<=3; $i++ )
		{
			$byte1 = $this->_getByte( $i*8 );
			$byte2 = $dwordObject->_getByte( $i*8 );

			$res->_setByte( $byte1 + $byte2 + $cf, $i*8 );
			if ( $byte1 + $byte2 + $cf >= 256 )
				$cf = 1;
		}
		return $res;
	}

}

$substitutionTable = array(
				array( 14,  4, 13,  1,  2, 15, 11,  8,  3, 10,  6, 12,  5,  9,  0,  7 ), 
				array( 15,  1,  8, 14,  6, 11,  3,  4,  9,  7,  2, 13, 12,  0,  5, 10 ), 
				array( 10,  0,  9, 14,  6,  3, 15,  5,  1, 13, 12,  7, 11,  4,  2,  8 ),
				array(  7, 13, 14,  3,  0,  6,  9, 10,  1,  2,  8,  5, 11, 12,  4, 15 ),
				array(  2, 12,  4,  1,  7, 10, 11,  6,  8,  5,  3, 15, 13,  0, 14,  9 ),
				array( 12,  1, 10, 15,  9,  2,  6,  8,  0, 13,  3,  4, 14,  7,  5, 11 ),
				array(  4, 11,  2, 14, 15,  0,  8, 13,  3, 12,  9,  7,  5, 10,  6,  1 ),
				array( 13,  2,  8,  4,  6, 15, 11,  1, 10,  9,  3, 14,  5,  0, 12,  7 )
		);


function _getByteValue( $bit0, $bit1, $bit2, $bit3 )
{
	return $bit0 + 2*$bit1 + 4*$bit2 + 8*$bit3;
}

function _getBitBy4_Value( $value, &$bit0, &$bit1, &$bit2, &$bit3 )
{
	$temp = $value;

	// 1 division
	$bit0 = $temp%2;
	$temp = $temp/2;

	// 2 division
	$bit1 = $temp%2;
	$temp = $temp/2;

	// 3 division
	$bit2 = $temp%2;
	$temp = $temp/2;

	$bit3 = $temp;
}

function _getBitBy32_Value( $_32value )
{
	$res = array();
	$temp = $_32value;
	for( $i=0; $i<=31; $i++ )
	{
		$res[] = $temp%2;
		$temp = $temp/2;
	}
	$res[] = $temp;
	return $res;
}


// *****************************************************************************
// Purpose	basic step 
// Inputs   
//				$dWordArray	- 64 bit value ( 2 DWord object )
//				$key		- 32 bit of key
// Remarks	
// Returns	
function _basicStep( $dWordArray, $key )
{
	global $substitutionTable;

	$N1 = $dWordArray[0];
	$N2 = $dWordArray[1];

	// step # 1 ( see picture 1 )
	$S = array();
	$carrying = 0;
	for( $i=0; $i<32; $i++ )
	{
		$value = $N1->bitArray[$i] + $key[$i] + $carrying;
		$carrying = 0;
		if ( $value == 0 )
			$S[] = 0;
		if ( $value == 1 )
			$S[] = 1;
		if ( $value == 2 )
		{
			$S[] = 0;
			$carrying = 1;
		}
		if ( $value == 3 )
		{
			$S[] = 1;
			$carrying = 1;
		}
	}

	// step # 2
	for( $i=1; $i<=8; $i++ )
	{
		$Si = _getByteValue( 
					$S[ ($i - 1)*4 + 0 ], 
					$S[ ($i - 1)*4 + 1 ], 
					$S[ ($i - 1)*4 + 2 ], 
					$S[ ($i - 1)*4 + 3 ] );

		_getBitBy4_Value( $substitutionTable[$i-1][ $Si ], 
				$S[ ($i - 1)*4 + 0 ], 
				$S[ ($i - 1)*4 + 1 ], 
				$S[ ($i - 1)*4 + 2 ], 
				$S[ ($i - 1)*4 + 3 ] );
	}

	// step # 3
	for( $j=1; $j<=11; $j++ )
	{
		$lastBit = $S[31];
		for( $i=0; $i<=30; $i++ )
			$S[$i+1] = $S[$i];
		$S[0] = $lastBit;
	}

	// step # 4
	for( $i=0; $i<32; $i++ )
		 $S[$i] = (int)( $N2->bitArray[$i] != $S[$i] );

	// step # 5
	for( $i=0; $i<32; $i++ )
		$N2->bitArray[$i] = $N1->bitArray[$i];

	for( $i=0; $i<32; $i++ )
		$N1->bitArray[$i] = $S[$i];

	return array( $N1, $N2 );
}


function _gostCryptSingle64Bit( $dWordArray, $key )
{
	for( $k=1; $k<=3; $k++ )
	{
		for( $j=0; $j<=7; $j++ )
		{
			$dWordArray = _basicStep( $dWordArray, 
					_getBitBy32_Value($key[$j]) );
		}
	}

	for( $j=7; $j>=0; $j-- )	
	{
		$dWordArray = _basicStep( $dWordArray, 
					_getBitBy32_Value($key[$j]) );
	}

	for( $i=0; $i<32; $i++ )
	{
		$temp = $dWordArray[0]->bitArray[$i];
		$dWordArray[0]->bitArray[$i] = $dWordArray[1]->bitArray[$i];
		$dWordArray[1]->bitArray[$i] = $temp;
	}
	return $dWordArray;
}

function _gostDeCryptSingle64Bit( $dWordArray, $key )
{
	for( $j=0; $j<=7; $j++ )	
	{
		$dWordArray = _basicStep( $dWordArray, 
					_getBitBy32_Value($key[$j]) );
	}

	for( $k=1; $k<=3; $k++ )
	{
		for( $j=7; $j>=0; $j-- )
		{
			$dWordArray = _basicStep( $dWordArray, 
					_getBitBy32_Value($key[$j]) );
		}
	}

	for( $i=0; $i<32; $i++ )
	{
		$temp = $dWordArray[0]->bitArray[$i];
		$dWordArray[0]->bitArray[$i] = $dWordArray[1]->bitArray[$i];
		$dWordArray[1]->bitArray[$i] = $temp;
	}
	return $dWordArray;
}

function _transformByteArray( 
				$byteArray, $key, $transformFunction )
{
	$byteCount = count($byteArray);
	$dWordArray = array();
	for( $i=1; $i<=$byteCount/4; $i++ )
	{
		$dWord = new DWord();
		$dWord->SetValue( 
			$byteArray[ ($i-1)*4 + 0 ],
			$byteArray[ ($i-1)*4 + 1 ],
			$byteArray[ ($i-1)*4 + 2 ],
			$byteArray[ ($i-1)*4 + 3 ]  );
		$dWordArray[] = $dWord;
	}

	$res = array();
	for( $i=1; $i<=count($dWordArray)/2; $i++ )
	{
		$twoWord = $transformFunction(
				array( $dWordArray[($i-1)*2 + 0], $dWordArray[($i-1)*2 + 1] ), 
				$key
			);
		$byte1 = 0;
		$byte2 = 0;
		$byte3 = 0;
		$byte4 = 0;

		$twoWord[0]->GetValue( $byte1, $byte2, $byte3, $byte4 );
		$res[] = $byte1;
		$res[] = $byte2;
		$res[] = $byte3;
		$res[] = $byte4;

		$twoWord[1]->GetValue( $byte1, $byte2, $byte3, $byte4 );
		$res[] = $byte1;
		$res[] = $byte2;
		$res[] = $byte3;
		$res[] = $byte4;
	}
	return $res;
}



function _cryptByteArray( $byteArray, $key )
{
	return _transformByteArray( 
				$byteArray, $key, "_gostCryptSingle64Bit" );
}

function _deCryptByteArray( $byteArray, $key )
{
	return _transformByteArray( 
				$byteArray, $key, "_gostDeCryptSingle64Bit" );
}


function _getRandomByte()
{
	$float = (float)microtime(true);
	$float = (string)$float;
	if ( strlen($float) >= 1 )
		$d1 = $float[ strlen($float)-1 ];
	else
		$d1 = "0";

	if ( strlen($float) >= 2 )
		$d2 = $float[ strlen($float)-2 ];
	else
		$d2 = "0";

	if ( strlen($float) >= 3 )
		$d3 = $float[ strlen($float)-3 ];
	else
		$d3 = "0";

	$int = (int)$d1.$d2.$d3;
	$int %= 256;
	return $int;
}


function _createByteArrayByStrWithExtened( $str, $size )
{
	$byteArray = array();
	$byteArray[] = strlen( $str );
	for( $i=1; $i<=strlen($str); $i++)
		$byteArray[] = ord($str[$i-1]);

	for( $i=1; $i<=$size-(strlen($str)+1); $i++ )
		$byteArray[] = _getRandomByte();
	return $byteArray;
}

function _createByteArrayByStr( $str )
{
	$byteArray = array();
	for( $i=0; $i<strlen($str); $i++ )
		$byteArray[] = ord($str[$i]);
	return $byteArray;
}

function _createStrByByteArrayWithTrancate( $byteArray )
{
	$res = "";
	$strlen = $byteArray[0];
	for( $i=1; $i<=$strlen; $i++ )
		$res .= chr($byteArray[$i]);
	return $res;
}

function _createStrByByteArray( $byteArray )
{
	$res = "";
	for( $i=0; $i < count($byteArray); $i++ )
		$res .= chr($byteArray[$i]);
	return $res;
}

// *****************************************************************************
// Purpose	 $cc_number
// Inputs   
//				
// Remarks	
// Returns	
function cryptCCNumberCrypt( $cc_number, $key )
{
	$byteArray = _createByteArrayByStrWithExtened( $cc_number, 64 );
	$byteArray = _cryptByteArray( $byteArray, $key );
	$res = _createStrByByteArray( $byteArray );
	debug( $res );
	return $res;
}

function cryptCCNumberDeCrypt( $cifer, $key )
{
	$byteArray = _createByteArrayByStr( $cifer );
	$byteArray = _deCryptByteArray( $byteArray, $key );
	return _createStrByByteArrayWithTrancate( $byteArray );
}


function cryptCCHoldernameCrypt( $cc_holdername, $key )
{

}

function cryptCCHoldernameDeCrypt( $cifer, $key )
{

}


function cryptCCExpiresCrypt( $cc_expires, $key )
{

}

function cryptCCExpiresDeCrypt( $cifer, $key )
{

}

function cryptPasswordCrypt( $password, $key )
{

}

function cryptPasswordDeCrypt( $cifer, $key )
{
}

function cryptFileParamCrypt( $getFileParam, $key )
{
}

function cryptFileParamDeCrypt( $cifer, $key )
{
}

?>