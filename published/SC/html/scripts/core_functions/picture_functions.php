<?php
// *****************************************************************************
// Purpose	gets pictures by product
// Inputs   $productID - product ID
// Remarks
// Returns	array of item
//				each item consits of
//				"photoID"			- photo ID
//				"productID"			- product ID
//				"filename"			- conventional photo filename
//				"thumbnail"			- thumbnail photo filename
//				"enlarged"			- enlarged photo filename
//				"default_picture"	- 1 if default picture, otherwise 0
function GetPictures( $productID )
{
	$sql = "select photoID, productID, filename, thumbnail, enlarged from "
		.PRODUCT_PICTURES." where productID = ".$productID.' ORDER BY priority';
	$q=db_query( $sql );
	$q2 = db_query("select default_picture from ".PRODUCTS_TABLE.
					" where productID = ".$productID );
	$product = db_fetch_row($q2);
	$default_picture = $product[0];
	$res = array();
	while( $row = db_fetch_row($q) )
	{
		if ( (string)$row["photoID"] == (string)$default_picture )
				$row["default_picture"] = 1;
		else
			$row["default_picture"] = 0;
		$res[] = $row;
	}
	return $res;
}

/**
 * Delete three pictures (filename, thumbnail, enlarged) for product
 *
 * @param int $photoID - identifier is corresponded three pictures ( see PRODUCT_PICTURES table in database_structure.xml )
 * @return PEAR_Error | null
 */
function DeleteThreePictures( $photoID ){

	$q=db_query("select filename, thumbnail, enlarged, productID from ".
			PRODUCT_PICTURES." where photoID=".$photoID );
	if ( $picture=db_fetch_row($q) )
	{
		if ( $picture["filename"]!="" && $picture["filename"]!=null )
			if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["filename"]) )
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$picture["filename"]));

		if ( $picture["thumbnail"]!="" && $picture["thumbnail"]!=null )
			if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["thumbnail"]) )
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$picture["thumbnail"]));

		if ( $picture["enlarged"]!="" && $picture["enlarged"]!=null )
			if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["enlarged"]) )
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$picture["enlarged"]));

		$q1 = db_query("select default_picture from ".PRODUCTS_TABLE." where productID=".$picture["productID"]);
		if ( $product = db_fetch_row($q1) )
		{
			if ( $product["default_picture"] == $photoID )
				db_query("update ".PRODUCTS_TABLE." set default_picture=NULL  where productID=".$picture["productID"] );
		}
		db_query("delete from ".PRODUCT_PICTURES." where photoID=".$photoID );
	}
}



// *****************************************************************************
// Purpose	deletes main picture for product
// Inputs   $photoID - picture ID ( see PRODUCT_PICTURES table )
// Remarks	$photoID identifier is corresponded three pictures ( see PRODUCT_PICTURES
//				table in database_structure.xml ), but this function delelete only thumbnail
//					picture from server and set thumbnail column value to ''
// Returns	nothing
function DeleteFilenamePicture( $photoID )
{
	$q=db_query("select filename from ".PRODUCT_PICTURES." where photoID=".
				$photoID );
	if ( $filename = db_fetch_row($q) )
	{
		if ( file_exists(DIR_PRODUCTS_PICTURES."/".$filename["filename"]) )
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$filename["filename"]));
		db_query("update ".PRODUCT_PICTURES." set filename=''".
				" where photoID=".$photoID );
	}
}

/**
 * Deletes thumbnail picture for product, but this function delelete only thumbnail picture from server and set thumbnail column value to ''
 *
 * @param int $photoID - identifier is corresponded three pictures ( see PRODUCT_PICTURES table in database_structure.xml )
 * @return PEAR_Error | null
 */
function DeleteThumbnailPicture( $photoID ){

	$photoID = intval($photoID);

	$q=db_query("select thumbnail from ".PRODUCT_PICTURES." where photoID=".$photoID );

	if ( $thumbnail=db_fetch_row($q) ){

		if ( file_exists(DIR_PRODUCTS_PICTURES."/".$thumbnail["thumbnail"]) ){
			$res = Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$thumbnail["thumbnail"]));
			if(PEAR::isError($res))return $res;
		}

		db_query("update ".PRODUCT_PICTURES." set thumbnail='' where photoID=".$photoID );
	}
}

/**
 * Delete enlarged picture for product
 *
 * @param int $photoID
 * @return PEAR_Error | null
 */
function DeleteEnlargedPicture( $photoID ){

	$photoID = intval($photoID);
	$q=db_query("select enlarged from ".PRODUCT_PICTURES." where photoID={$photoID}" );
	if ( $enlarged=db_fetch_row($q) ){

		if ( file_exists(DIR_PRODUCTS_PICTURES."/".$enlarged["enlarged"]) ){

			$res = Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$enlarged["enlarged"]));
			if(PEAR::isError($res))return $res;
		}

		db_query("update ".PRODUCT_PICTURES." set enlarged='' where photoID={$photoID}");
	}
}


// *****************************************************************************
// Purpose	updates filenames
// Inputs   $fileNames array of	items
//				each item consits of
//					"filename"		- normal picture
//					"thumbnail"		- thumbnail picture
//					"enlarged"		- enlarged picture
//				key is picture ID ( see PRODUCT_PICTURES  )
// Remarks
//				if $default_picture == -1 then default picture is not set
// Returns	nothing
function UpdatePictures( $productID, $fileNames, $default_picture )
{
	foreach( $fileNames as $key => $value ){

		db_phquery('UPDATE ?#PRODUCT_PICTURES SET filename=?,thumbnail=?,enlarged=? WHERE photoID=?',$value['filename'],$value['thumbnail'],$value['enlarged'],$key);
	}
	if ( $default_picture != -1 )db_phquery('UPDATE ?#PRODUCTS_TABLE SET default_picture=? WHERE productID=?',$default_picture,$productID);
}



// *****************************************************************************
// Purpose	adds new picture
// Inputs	$filename, $thumbnail, $enlarged - keys of item in $_FILES
//				corresponded to these file names
//			$productID - product ID
//			$default_picture - default picture ID
// Remarks
//			if $new_filename == "" then function does not something
//			if $default_picture == -1 then default picture is set to new inserted
//					item to PRODUCT_PICTURES
// Returns	nothing
function AddNewPictures( $productID, $filename, $thumbnail, $enlarged, $default_picture ){

	if ( !trim($_FILES[$filename]["name"]) )return ;

	$new_filename="";
	$new_thumbnail="";
	$new_enlarged="";

	if ( $_FILES[$filename]["size"]!=0 && is_image($_FILES[$filename]["name"]) ){

		if(PEAR::isError($res = File::checkUpload($_FILES[$filename]))||
		PEAR::isError($res = Functions::exec('file_move_uploaded', array($_FILES[$filename]["tmp_name"], DIR_PRODUCTS_PICTURES."/".$_FILES[$filename]["name"])))
		){
			return $res;
		}

		$new_filename = $_FILES[$filename]["name"];
		SetRightsToUploadedFile( DIR_PRODUCTS_PICTURES."/".$new_filename );
	}

	if ( $_FILES[$thumbnail]["size"]!=0  && is_image($_FILES[$thumbnail]["name"])){

		$res = Functions::exec('file_move_uploaded', array($_FILES[$thumbnail]["tmp_name"], DIR_PRODUCTS_PICTURES."/".$_FILES[$thumbnail]["name"]));
		if (PEAR::isError($res))return $res;

		$new_thumbnail=$_FILES[$thumbnail]["name"];
		SetRightsToUploadedFile( DIR_PRODUCTS_PICTURES."/".$new_thumbnail );
	}

	if ( $_FILES[$enlarged]["size"]!=0  && is_image($_FILES[$enlarged]["name"])){

		$res = Functions::exec('file_move_uploaded', array($_FILES[$enlarged]["tmp_name"], DIR_PRODUCTS_PICTURES."/".$_FILES[$enlarged]["name"]));
		if (PEAR::isError($res))return $res;

		$new_enlarged=$_FILES[$enlarged]["name"];
		SetRightsToUploadedFile( DIR_PRODUCTS_PICTURES."/".$new_enlarged );
	}

	if ( $new_filename!="" ){

		db_phquery("
			INSERT ?#PRODUCT_PICTURES (productID, filename, thumbnail, enlarged)
			VALUES( ?, ?, ?, ?)", $productID, $new_filename, $new_thumbnail, $new_enlarged);

		if ( $default_picture == -1 ){

			db_phquery("UPDATE ?#PRODUCTS_TABLE SET default_picture=? WHERE productID=?", db_insert_id(), $productID);
		}
	}
}


// *****************************************************************************
// Purpose	gets thumbnail file name
// Inputs	$productID - product ID
// Remarks
// Returns	file name, it is not full path
function GetThumbnail($productID)
{
	$q=db_query( "select default_picture from ".PRODUCTS_TABLE.
			" where productID=".$productID );
	if ( $product = db_fetch_row($q) )
	{
		$q2 = db_query("select filename, thumbnail, enlarged from ".PRODUCT_PICTURES.
			" where photoID='".$product["default_picture"]."' and productID=".$productID);
		if ( $picture=db_fetch_row($q2) )
		{
			if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["thumbnail"]) && strlen($picture["thumbnail"])>0 )
				return $picture["thumbnail"];
			else if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["filename"]) && strlen($picture["filename"])>0 )
				return $picture["filename"];
		}
		else //default picture is not defined - get one of the pics if there are any
		{

			$q2 = db_query( "select filename, thumbnail, enlarged from ".PRODUCT_PICTURES." where productID=".$productID );
			if ( $picture=db_fetch_row($q2) )
			{
				if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["thumbnail"]) && strlen($picture["thumbnail"])>0 )
					return $picture["thumbnail"];
				if ( file_exists(DIR_PRODUCTS_PICTURES."/".$picture["filename"]) && strlen($picture["filename"])>0 )
					return $picture["filename"];
			}

		}
	}
	return "";
}

function shrink_size($src_width, $src_height, $width, $height ){

	if ( $src_width > $src_height && $src_width > $width ){

		$ratio = $src_width/$src_height;
		$height /= $ratio;
	}elseif( $src_height > $height ) {

		$ratio = $src_height/$src_width;
		$width /= $ratio;
	}
	if($src_height < $height || $src_width < $width){
		$width = $src_width;
		$height = $src_height;
	}
	return array(round($width, 0), round($height, 0));
}

class ns_image{

	function read($fileName, &$info){

		if(!file_exists($fileName)){
			return PEAR::raiseError('Error upload file', 1);;
		}
	  $info = @getimagesize($fileName);
	  if (!$info) return PEAR::raiseError('Error getimagesize', 1);;
	  switch ($info[2]) {
	      case 1:
	          // Create recource from gif image
			$srcIm = @imagecreatefromgif( $fileName );
	          break;
	      case 2:
	          // Create recource from jpg image
			$srcIm = @imagecreatefromjpeg( $fileName );
	          break;
	      case 3:
	          // Create resource from png image
			$srcIm = @imagecreatefrompng( $fileName );
	          break;
	      case 5:
	          // Create resource from psd image
	          break;
	      case 6:
	          // Create recource from bmp image imagecreatefromwbmp
			$srcIm = @imagecreatefromwbmp( $fileName );
	          break;
	      case 7:
	          // Create resource from tiff image
	          break;
	      case 8:
	          // Create resource from tiff image
	          break;
	      case 9:
	          // Create resource from jpc image
	          break;
	      case 10:
	          // Create resource from jp2 image
	          break;
	      default:
	          break;
	  }

	  if (!$srcIm) return FALSE;
	  else return $srcIm;
	}

	function resize($file, $width, $height, $destination_file = null,$watermark_file = null,$position = 'right', $alpha_level = 50){
		$width = intval($width);
		$height = intval($height);
		if ( !function_exists('gd_info') )
			return PEAR::raiseError('PHP extension gd not loaded', 1);
//			return PEAR::raiseError(1, 1);

		$src_img = $this->read($file, $info);
		if(PEAR::isError($src_img)){
			return $src_img;
		}

		if(!$src_img)
			return PEAR::raiseError('Error read image', 1);
//			return PEAR::raiseError(2, 1);

		if ( !function_exists('imagecreatetruecolor') )
			return PEAR::raiseError('function "imagecreatetruecolor" dosn\'t exists', 1);
//			return PEAR::raiseError(3, 1);

		if ( !function_exists('imagecopyresized') )
			return PEAR::raiseError('function "imagecopyresized" dosn\'t exists', 1);
//			return PEAR::raiseError(4, 1);

		if ( !function_exists('getimagesize') )
			return PEAR::raiseError('function "getimagesize" dosn\'t exists', 1);

		$src_width = imagesx($src_img);
		if(!$width) $width = $src_width;
		$src_height = imagesy($src_img);
		if(!$height) $height = $src_height;
  		if ( $src_width > $src_height && $src_width > $width ){

			$ratio = $src_width/$src_height;
			$height /= $ratio;
		}elseif( $src_height > $height ) {

			$ratio = $src_height/$src_width;
			$width /= $ratio;
		}
		
		if($src_height < $height || $src_width < $width){
			$width = $src_width;
			$height = $src_height;
		}
		if($width == $src_width){//skip image resize
			if(($file!=$destination_file)&&!copy($file, $destination_file)){
				return PEAR::raiseError('Error write image', 1);
			}
			return null;
		}

		$dst_img = imagecreatetruecolor( $width, $height );

		if ( !$dst_img ) {
			@imagedestroy( $src_img );
			return PEAR::raiseError( "Error creating true color image {$width}&times;{$height}", 1 );
//			return PEAR::raiseError( 6, 1 );
		}

		if ( function_exists('imagecopyresampled') )
			$res = @imagecopyresampled ( $dst_img, $src_img, 0, 0, 0, 0, $width, $height, $src_width, $src_height );
		else
			$res = @imagecopyresized ( $dst_img, $src_img, 0, 0, 0, 0, $width, $height, $src_width, $src_height );

		if ( !$res ) {
			@imagedestroy( $src_img );
			@imagedestroy( $dst_img );

			return PEAR::raiseError( 'Error copy resized image', 1 );
//			return PEAR::raiseError( 7, 1 );
		}
		
		if(defined('CONF_PICTRESIZE_QUALITY')){
			$quality = intval(constant('CONF_PICTRESIZE_QUALITY'));
			$quality = ($quality>100)?100:(($quality<0)?0:$quality);
		}else{
			$quality = 80;
		}
		//Future add watermark
		$watermark_file = DIR_IMG.'/watermark.png';
		if(false&&$watermark_file && file_exists($watermark_file)){
			$dst_img = $this->addWatermark($dst_img,$watermark_file,$position, $alpha_level);
		}

		$res = @imagejpeg( $dst_img, !is_null($destination_file)?$destination_file:$file, $quality);
		if(!$res)
			return PEAR::raiseError('Error write image', 1);
//			return PEAR::raiseError(8, 1);

		@imagedestroy( $dst_img );
		@imagedestroy( $src_img );
	}
	
	function addWatermark($image,$watermark_file = null,$position = 'right', $alpha_level = 100)
	{
		static $watermark = false;
		static $width;
		static $height;
		if(!$watermark&&$watermark_file){
			if(!$watermark = imagecreatefrompng($watermark_file)){
				return $image;
			}
			$width = imagesx($watermark);
			$height = imagesy($watermark);
		}

		if ( $position == 'right' ) {
			$dest_x = imagesx($image) - $width - 5;
			$dest_y = imagesy($image) - $height - 5;
		}else{
			$dest_x = intval(imagesx($image)*0.5) - intval($width*0.5);
			$dest_y = intval(imagesy($image)*0.5) - intval($height*0.5);
		}
		imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $width, $height, $alpha_level);
		return $image;
	}
}

Functions::register(new ns_image(), 'img_resize', 'resize');
?>