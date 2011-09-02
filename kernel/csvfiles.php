<?php

	//
	// WebAsyst CSV files support classes
	//

	class CSVFile
	{
		var $_fp = null;
		var $_addUIDCol = false;
		var $_importScheme = null;

		function CreateCSVFile( $importScheme, &$kernelStrings, $addUIDColumn = true )
		//
		// Creates a new CSV file and writes a header to it
		//
		//		Parameters:
		//			$importScheme - CSV file import scheme
		//			$kernelStrings - Kernel localization strings
		//			$addUIDColumn - add U_ID column into result
		//
		//		Returns path to new file or PEAR_Error
		//
		{
			$this->_importScheme = $importScheme;

			// Create/open the file
			//
			$tmpFileName = uniqid( TMP_FILES_PREFIX );
			$destPath = WBS_TEMP_DIR."/".$tmpFileName;

			$this->_fp = @fopen( $destPath, 'wt' );
			if ( !$this->_fp )
				return PEAR::raiseError( $kernelStrings['eul_tmpfilerr_message'] );

			// Write column headers
			//
			$exportItem = array();
			$this->_addUIDCol = false;
			if ( !$importScheme[CSV_IMPORTFIRSLN] ) {
				// Add ID field
				//
				if ( !array_key_exists( 'U_ID', $importScheme[CSV_LINKS] ) && $addUIDColumn ) {
					$this->_addUIDCol = true;
					$exportItem[] = $kernelStrings['app_treeid_title'];
				}

				// Add other scheme fields
				//
				foreach( $importScheme[CSV_LINKS] as $schemeLink )
					$exportItem[] = $schemeLink[CSV_FILEFIELD];

				// Write headers to file
				//
				writeCSVLine( $exportItem, $importScheme[CSV_DELIMITER], $this->_fp );
			}

			return $destPath;
		}

		function WriteLine( $recordData )
		//
		// Writes a line to the CSV file
		//
		//		Parameters:
		//			$recordData - line data as array
		//
		//		Returns null
		//
		{
			if ( !$this->_fp )
				return null;

			$exportItem = array();

			// Add ID field
			//
			if ( !$this->_importScheme[CSV_IMPORTFIRSLN] )
				if ( $this->_addUIDCol )
					$exportItem[] = $recordData['U_ID'];

			// Build file line
			//
			foreach( $this->_importScheme[CSV_LINKS] as $schemeLink )
				$exportItem[] = $recordData[$schemeLink[CSV_DBFIELD]];

			// Write line to file
			//
			writeCSVLine( $exportItem, $this->_importScheme[CSV_DELIMITER], $this->_fp );
		}

		function CloseFile()
		//
		// Closes the CSV file
		//
		//		Returns null
		//
		{
			if ( !$this->_fp )
				return null;

			@fclose( $this->_fp );

			return null;
		}
	}

?>