<?php

	include_once 'contacts.const.php';

	class WbsContact 
	{
		
		private $C_ID;
		private $data = array();
		private $type = CONTACT_BASIC_TYPE; 
		private $type_desc;
		
		
		public function __construct($C_ID, $data = array(), $type = CONTACT_BASIC_TYPE)
		{
			$this->C_ID = $C_ID;
			$this->type = $type;
			$this->type_desc = ContactDescriptor::get($type);
			$this->data = $data;			
		}
		
		public function getDescriptor()
		{
			return $this->type_desc;
		}
		
		/**
		 * Returns contact's data for user
		 *
		 * @return array
		 */
		public function getData()
		{
			if (!$this->data) {
				$sql = new CSelectSqlQuery("CONTACT", "C");
				$sql->leftJoin("WBS_USER", "U", "C.C_ID = U.C_ID");
				$sql->addConditions("C.C_ID", $this->C_ID);
				$this->data = Wdb::getRow($sql);
			} 
            $user = new WbsUser($this->data['U_ID']);
            $timeZoneId = $user->getSetting("TIME_ZONE_ID");
            $timeZoneDst = $user->getSetting("TIME_ZONE_DST");
            $timeZone = new CTimeZone($timeZoneId, $timeZoneDst);
            WbsDateTime::setTimeZone($timeZone);
			return $this->data;
		}

		/**
		 * Save data for contact
		 *
		 * @param array $data
		 */
		public function setData($data) 
		{
			if (!is_array($data)) {
				return false;
			}
			$sql = new CUpdateSqlQuery("CONTACT");
			$sql->addConditions("C_ID", $this->C_ID);
			$sql->addFields($data, array_keys($data));
			Wdb::runQuery($sql);
		}
		
		public function getDisplayData()
		{
			$result = array();
			
			foreach ($this->type_desc as $g) {
				foreach ($g[CONTACT_FIELDS] as $f) {
					$result[$f[CONTACT_FIELDID]] = $this->getFieldValue($f);	
				}
			}
			return $result;
		}
		
		/**
		 * Returns contact field value
		 *
		 * @param array $field_desc
		 * @return scalar value
		 */
		public function getFieldValue($field_desc)
		{
			if (!$this->data) {
				$this->getData();
			}
			$type = $field_desc[CONTACT_FIELD_TYPE];

			switch ( $type ) {
					case CONTACT_FT_DATE :
						$dbfield = $field_desc[CONTACT_DBFIELD];
						if ($this->data[$dbfield]) {
						    $result = WbsDateTime::getDate(strtotime($this->data[$dbfield]));
						} else {
						    $result = $this->data[$dbfield];
						}
						break;
					case CONTACT_FT_NUMERIC :
						$dbfield = $field_desc[CONTACT_DBFIELD];
						$decplaces = $field_desc[CONTACT_DECPLACES];

						if ( strlen($this->data[$dbfield]) ) {
							$result = round($this->data[$dbfield], $field_desc[CONTACT_DECPLACES]);
						} else {
							$result = null;
						}

						break;
					case CONTACT_FT_IMAGE :
						$dbfield = $field_desc[CONTACT_DBFIELD];
						$result = self::getDisplayImage(self::getImageFieldPropertieis( $this->data[$dbfield] ));
						$result = $result['THUMB_URL'];
						break;
					default:
						if ( isset($field_desc[CONTACT_DBFIELD]) ) {
							$dbfield = $field_desc[CONTACT_DBFIELD];
							$result = $this->data[$dbfield];
						} else {
							$result = $this->data[$field_desc[CONTACT_FIELDID]];
						}
			}
	
			return $result;
		}
		
		/**
		 * Returns image's properties 
		 *
		 * @param array $properties
		 * @return array
		 */
		public static function getDisplayImage($properties) 
		{
			if ( $properties[CONTACT_IMGF_FILENAME] ) {
				if ( isset($properties[CONTACT_IMGF_MODIFIED]) && $properties[CONTACT_IMGF_MODIFIED]) {
					$thumbPath = base64_decode($properties[CONTACT_IMGF_DISKFILENAME]);
					
				} else {
					$thumbPath = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", "contacts".DIRECTORY_SEPARATOR.base64_decode($properties[CONTACT_IMGF_DISKFILENAME]));
				}
				
				$thumbPerms[] = $thumbPath;

				$thumbParams = array();
				$srcExt = null;
				
				$thumbParams['nocache'] = self::getThumbnailModifyDate( $thumbPath, 'win', $srcExt );

				if ( isset($properties[CONTACT_IMGF_MODIFIED]) && $properties[CONTACT_IMGF_MODIFIED])
					$thumbParams['basefile'] = $properties[CONTACT_IMGF_DISKFILENAME];
				else {
					$thumbParams['basefile'] = base64_encode(Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", "contacts".DIRECTORY_SEPARATOR.base64_decode($properties[CONTACT_IMGF_DISKFILENAME])));
				}
				
				$thumbParams['ext'] = base64_encode( $properties[CONTACT_IMGF_TYPE] );

				$properties['THUMB_URL'] = WebQuery::getPublishedUrl("common/html/scripts/thumb.php", $thumbParams);
				
			}
			return $properties;
		}
		
		/**
		 * Returns path to the thumbnail file, if it exists, or null
		 *
		 * @param string $filePath - path to the original document
		 * @param string $ext - thumbnail extension
		 * @return null or string
		 */		
		public static function findThumbnailFile( $filePath, &$ext )
		{
			$ext = 'gif';
			$gifFilePath = $filePath.".$ext";
			if ( @file_exists($gifFilePath) ) {
				return $gifFilePath;
			}
			
			$ext = 'jpg';
			$jpgFilePath = $filePath.".$ext";
			if ( @file_exists($jpgFilePath) ) {
				return $jpgFilePath;
			}
			return null;
		}
		
		/**
		 * Returns thumbnail modification date
		 *
		 * @param string $filePath - path to the original file
		 * @param string $os - thumbnail operation system style
		 * @param string $srcExt - file extension
		 * @return  
		 */
		public static function getThumbnailModifyDate( $filePath, $os, $srcExt = null )
		{
			$thumbPath = self::findThumbnailFile( $filePath, $ext );
	
			if ( !is_null($thumbPath) ) {
				return filemtime($thumbPath);
	
			} else {
				$fileNameData = pathinfo( $filePath );
	
				$filePath = Wbs::getSystemObj()->files()->getPublishedPath("common/html/thumbnails")."/".$srcExt.".".$os.".32.gif";

				if ( !file_exists($filePath) )
					$filePath = Wbs::getSystemObj()->files()->getPublishedPath("common/html/thumbnails")."/common.".$os.".32.gif";
	
				return filemtime($filePath);
			}
			return null;
		}
		
		
		
		/**
		 * Returns array representation of the contact image field
		 *
		 * @param string $fieldValue - field XML description string
		 * @return array
		 */
		public static function getImageFieldPropertieis( $fieldValue )
		{
			$result = array();
	
			$result[CONTACT_IMGF_FILENAME] = null;
			$result[CONTACT_IMGF_SIZE] = null;
			$result[CONTACT_IMGF_DISKFILENAME] = null;
			$result[CONTACT_IMGF_TYPE] = null;
			$result[CONTACT_IMGF_DATETIME] = null;
			$result[CONTACT_IMGF_MIMETYPE] = null;
			$result[CONTACT_IMGF_MODIFIED] = false;
			$result[CONTACT_IMGF_PREVFILENAME] = null;
	
			if ( !strlen($fieldValue) ) {
				return $result;
			}
			$dom = new DOMDocument("1.0", "UTF-8");
			$dom->loadXML($fieldValue); 
			//@domxml_open_mem( $fieldValue );
			if ( !$dom )
				return $result;
	
			$root = $dom->documentElement;
		
			if ( !$root ) {
				return $result;
			}
	
			$result[CONTACT_IMGF_FILENAME] = base64_decode( @$root->getAttribute(CONTACT_IMGF_FILENAME) );
			$result[CONTACT_IMGF_SIZE] = @$root->getAttribute(CONTACT_IMGF_SIZE);
			$result[CONTACT_IMGF_DISKFILENAME] = @$root->getAttribute(CONTACT_IMGF_DISKFILENAME);
			$result[CONTACT_IMGF_TYPE] = @$root->getAttribute(CONTACT_IMGF_TYPE);
			$result[CONTACT_IMGF_DATETIME] = @$root->getAttribute(CONTACT_IMGF_DATETIME);
			$result[CONTACT_IMGF_MIMETYPE] = @$root->getAttribute(CONTACT_IMGF_MIMETYPE);
			$result[CONTACT_IMGF_PREVFILENAME] = @$root->getAttribute(CONTACT_IMGF_DISKFILENAME);
	
			return $result;
		}


		/**
		 * Moves image file from temporary directory to the attachemtns directory and updates image description parameters
		 * Returns new field's info
		 *
		 * @param array $field - Field's info
		 * @param DiskQuotaManager $QuotaManager - pointer to class DiskQuotaManager
		 * @return array
		 */
		function moveUpdateImageFieldFile($field, &$QuotaManager )
		{
			// Delete previous files
			if ( strlen($field[CONTACT_IMGF_PREVFILENAME]) ) {
				$srcPath = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", "contacts/".base64_decode( $field[CONTACT_IMGF_PREVFILENAME]));
	
				$ext = null;
				$srcThumbFile = self::findThumbnailFile( $srcPath, $ext );
				if ( $srcThumbFile ) {
					if ( file_exists($srcThumbFile) ) {
						$QuotaManager->AddDiskUsageRecord( CurrentUser::getId(), 'CM', -1*filesize($srcThumbFile) );
						@unlink($srcThumbFile);
					}
				}
	
				if ( file_exists($srcPath) ) {
					$QuotaManager->AddDiskUsageRecord( CurrentUser::getId(), 'CM', -1*filesize($srcPath) );
					@unlink($srcPath);
				}
			}
	
			// Move image file from temp directory
			if ( strlen($field[CONTACT_IMGF_DISKFILENAME]) ) {
				$srcPath = base64_decode( $field[CONTACT_IMGF_DISKFILENAME] );
				$destPath = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", "contacts/".uniqid(CONTACT_IMG_FILEPREFIX));
	
				if ( !@copy($srcPath, $destPath) ) {
					return false;
				}
				$QuotaManager->AddDiskUsageRecord( CurrentUser::getId(), 'CM', filesize($destPath) );
	
				// Move thumbnail
				//
				$ext = null;
				$srcThumbFile = self::findThumbnailFile( $srcPath, $ext );
				if ( $srcThumbFile ) {
					$destThumbFile = $destPath.".$ext";
	
					if ( !@copy( $srcThumbFile, $destThumbFile ) ) {
						return false;
					}
	
					$QuotaManager->AddDiskUsageRecord( CurrentUser::getId(), 'CM', filesize($destThumbFile) );
	
					@unlink($srcThumbFile);
				}
	
				@unlink($srcPath);
	
				$field[CONTACT_IMGF_DISKFILENAME] = base64_encode($destFileName);
			}
	
			return $field;
		}
			
	}
?>