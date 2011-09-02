<?php
	class SBSCSubtype extends WidgetSubtype {
		var $commonFields = array ();
		var $id;
		var $minHeight = 110;
		
		function SBSCSubtype  (&$type) {
			$this->commonFields = array ("WIDTH", "TITLE", "TITLE_bgcolor", "TITLE_color", "BGCOLOR", "SIGNUPTEXT", "SAVEBTN", "DOPTIN", "FOLDER", "EMAILTEXT");
			$this->embType = "inplace";
			$this->hidePlaces = array ("fields");
			$this->minFolderRights = 4;
			parent::WidgetSubtype ($type);
		}

		function getFieldsData ($widgetData = array()) {
			$fields = $this->getFields ();
			$result = array ();
			foreach ($this->type->fieldsData as $cKey => $cData) {
				if (!in_array ($cKey, $fields))
					continue;
				if (isset ($this->type->strings[strtolower($this->id) . "_sparam_" . strtolower($cKey) . "_default"])) {
						$cData["default"] = $this->type->strings[strtolower($this->id) . "_sparam_" . strtolower($cKey) . "_default"];
				}
				$this->fieldsData[$cKey] = $cData;
				$result[$cKey] = $cData;
			}
			return $result;
		}
		
		function prepareConstructorPage (&$preproc, &$pageState, $widgetData = array ()) {
			global $cm_groupClass;
			$access = null;
			$hierarchy = null;
			$deletable = null;
			global $cm_groupClass;
			global $currentUser;
			$folders = $cm_groupClass->listFolders( $currentUser, TREE_ROOT_FOLDER, $pageState->kernelStrings, 0, false,
																	$access, $hierarchy, $deletable, TREE_WRITEREAD );
			if ( PEAR::isError($folders))
				return $folders;
			$visibleFolders = array();
			foreach ( $folders as $fCF_ID=>$folderData ) {
				$encodedID = base64_encode($fCF_ID);
				$folderData->curID = $encodedID;
				$folderData->OFFSET_STR = str_replace( " ", "&nbsp;&nbsp;", $folderData->OFFSET_STR);

				$visibleFolders[$fCF_ID] = $folderData;
			}
			$folders = $visibleFolders;
			
			parent::prepareConstructorPage($preproc, $pageState, $widgetData);
			
			$preproc->assign( "folders", $folders );
			//$preproc->assign  ("typeFormFile", array("general" => $this->type->getHTMLPath() . "folder.htm"));
			//$preproc->assign  ("typeBeforeFormFile", array("contacts" => $this->type->getHTMLPath() . "folder.htm"));
		}
		
		function prepare (&$preproc, &$widgetData) {
			global $kernelStrings;
			global $language;
			
			$contentFilename = "signup_form.htm";
			$result = "";
			if ($this->pageState->getParam("action") == "signup") {
				
				do {
					if (pear::isError($res = $this->checkEmail($this->pageState->getParam("email")))) {
						$this->pageState->addError ($res);
						break;
					}
					$contactData = array ("C_EMAILADDRESS" => $this->pageState->getParam("email"));
					if ($this->id != "SIMPLE") {
						 $name = $this->pageState->getParam("C_FULLNAME");
						 if ($name) {
						     $name = explode(" ", $name);
							 if (count($name) >= 3) {
							 	$name = array_reverse($name);
							 	$contactData["C_LASTNAME"] = $name[0];
								$contact_type = new ContactType(1);
								$dbfields = $contact_type->getTypeDbFields();
								$i = 1;
								if (in_array('C_MIDDLENAME', $dbfields)) {
							    	$contactData["C_MIDDLENAME"] = $name[1];
							    	$i++;
								} 
								for ($j = 0; $j < $i; $j++) {
									unset($name[$j]);
								}
								$name = array_reverse($name);
								$contactData["C_FIRSTNAME"] = implode(" ", $name);
							 } elseif (count($name) == 2) {
							     $contactData["C_FIRSTNAME"] = $name[0];
							     $contactData["C_LASTNAME"] = $name[1];						     
							 } else {
							     $contactData['C_FIRSTNAME'] = $name[0];
							 }
						 }
					}
					$result = $this->addContact ($contactData, $language, $kernelStrings, $widgetData);
										
					if ($result !== true)
						break;
					
					$contentFilename = "after_signup.htm";
					$result = "success";
				} while (false);
			}
			$field_id = ContactType::getFieldId('C_FIRSTNAME');
			$field_info =  ContactType::getField($field_id, $widgetData['WG_LANG']);
			$preproc->assign ('nameLabel', $field_info['name']);
			$preproc->assign ("contentFilename", $contentFilename);
			$preproc->assign ("result", $result);
			$preproc->assign( "is_host", Wbs::isHosted());
			
			return array("tplFilename" => "sbsc_widget.htm");
		}
		
		function checkFieldsValues (&$params) {
			if (!$params["FOLDER"])
				return PEAR::raiseError ($this->type->strings["wg_emptyfolder_error"]);
		}
		
		function prepareFieldsValues (&$params) {
			if (!empty($params["FOLDER"]))
				$params["FOLDER"] = base64_decode($params["FOLDER"]);
		}
		
		function checkEmail ($email) {
			if (!$email)
				return PEAR::raiseError ($this->type->strings["swg_emptymail_message"]);
			if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,5})$", $email))
				return PEAR::raiseError ($this->type->strings["swg_wrongmailformat_message"]);
			return true;
		}
		
		function addContact ($contactData, $language, &$kernelStrings, $widgetData) {
		    $charset = Env::Post('encoding', Env::TYPE_STRING, 'utf-8');
		    $charset = strtolower($charset);
		    if ($charset && $charset != 'utf-8' && $charset != 'utf8') {
			    foreach ($contactData as &$v) {
			        $v = iconv($charset, "utf-8", $v);
			        $v = html_entity_decode($v, ENT_COMPAT, "UTF-8");
			    } 
		    }
			$lists = explode(",", $widgetData["params"]["LISTS"]);
			$folder = $widgetData["params"]["FOLDER"];
			
			$contactData['C_LANGUAGE'] = $widgetData['WG_LANG'];
			//if (!$folder) 
				//$folder = "1.";
							
			global $cmStrings;
			global $DB_KEY;
			global $userContactMode;
			
			try {
			    Contact::checkLimits(1);
			} catch (Exception $e) {
				$this->pageState->addError($e->__toString());
				return $e->__toString();
			}
			// Loading locale of the widget
			GetText::load($widgetData['WG_LANG'], SYSTEM_PATH . "/locale", 'system', false);
			if (!Contact::addSubscriber($folder ? $folder : null, $contactData, $lists, $widgetData["WG_ID"], $errors)) {
				$this->pageState->addError($errors[0]['text']);
				return $errors[0]['text'];
			}
			
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, '', 'CM', 'ADDCONTACT', 'WG-SIGNUP');
				
			return true;
		}
	}
	
	
	
	class SBSCSimpleSubtype extends SBSCSubtype {
		function SBSCSimpleSubtype (&$type) {
			$this->id = "SIMPLE";
			$this->fields = array ();
			parent::SBSCSubtype ($type);
		}
		
		function getEmbInfo ($widgetData) {
			return array("height" => $this->minHeight);
		}
	}
	
	class SBSCMainSubtype extends SBSCSubtype {
		function SBSCMainSubtype (&$type) {
			$this->id = "MAIN";
			$this->fields = array ();
			parent::SBSCSubtype ($type);
		}
		
		function getEmbInfo ($widgetData) {
			return array("height" => $this->minHeight+70);
		}
	}
	
	
	
	class SBSCCustomSubtype extends SBSCSubtype {
		function SBSCCustomSubtype (&$type) {
			$this->id = "CUSTOM";
			$this->fields = array ("CMFIELDS", "CMFIELDSLABELS");
			parent::SBSCSubtype ($type);
			$this->hidePlaces = array ();
		}
		
		/**
		 * 
		 * @return ContactType
		 */
		public function getContactType($type_id = 1)
		{
		    if (!$type_id) {
		        $type_id = 1;
		    }
			if (!$this->contact_type) {
				$this->contact_type = new ContactType($type_id);
			}
			return $this->contact_type;
		}
		
		function getEmbInfo ($widgetData) {
			
			$widgetParams = $this->getRealParams($widgetData);
			
			$height = 85;
			$cmfieldsIDs = array ();
			if ($widgetParams && !empty($widgetParams["CMFIELDS"]))
				$cmfieldsIDs = split (",", $widgetParams["CMFIELDS"]);
			if (!$widgetData)
				$cmfieldsIDs = split (",", $this->type->fieldsData["CMFIELDS"]["default"]);
				
			$type_fields = $this->getContactType($widgetParams['CT_ID'])->getTypeDbFields();
			foreach ($cmfieldsIDs as $cId) {
				$field = $this->getContactType($widgetParams['CT_ID'])->getFieldByDbName($cId, $widgetData['WG_LANG']);
				if (!isset($type_fields[$field['id']])) continue;
				if ($field["type"] == "IMAGE") 
					$cheight = 44.5;
				elseif ($field["type"] == "TEXT")
					$cheight = 75;
				else
					$cheight = 44.5;
				$height += $cheight;
			}
			if ($height < $this->minHeight)
				$height = $this->minHeight;			
				
			return array("height" => round($height));
		}
		
		function prepare (&$preproc, &$widgetData, $formFilename = "custom_form.htm") {
			global $kernelStrings;
			global $language;
			
			$contentFilename = $formFilename;
			$result = "";
					
			$cmfields = array ();
			$cmfieldsLabels = array ();
			$widgetParams = $this->getRealParams($widgetData);
			if ($widgetParams && !empty($widgetParams["CMFIELDS"])) {
				
				if (!empty($widgetParams["CMFIELDSLABELS"])) {
					$labelsVals = split (";", $widgetParams["CMFIELDSLABELS"]);
					$cmfieldsLabels = array ();
					foreach($labelsVals as $cLabelStr) {
						list ($field, $label) = split ("=", $cLabelStr, 2);
						$cmfieldsLabels[$field] = $label;
					}
				}
				
				$cmfieldsIDs = split (",", $widgetParams["CMFIELDS"]);
				$type = $this->getContactType($widgetParams['CT_ID'])->getType($widgetData['WG_LANG']);
                $main_fields = $this->getContactType($widgetParams['CT_ID'])->getMainFields();
                if (in_array('C_FULLNAME', $cmfieldsIDs)) {
                    $cmfields['C_FULLNAME'] = ContactType::getFieldByDbName('C_FULLNAME', $widgetData['WG_LANG']);
                    GetText::load($widgetData['WG_LANG'], SYSTEM_PATH . "/locale", 'system', false);
                    $cmfields['C_FULLNAME']['name'] = _s('Name');
                }
				foreach ($type['fields'] as $section) {
					foreach ($section["fields"] as $field) {
						if (!in_array($field["dbname"], $cmfieldsIDs))
							continue;
						if (in_array($field['id'], $main_fields)) {
						    $field['primary'] = 1;
						}
						$cmfields[$field["dbname"]] = $field;
					}
				}
			}
			
			
			if ($this->pageState->getParam("action") == "signup") {
				do {
					$contactData = array ();
					foreach ($cmfields as $field) {
						$contactData[$field["dbname"]] = $this->pageState->getParam($field["dbname"]);
					}
					$name = $this->pageState->getParam("C_FULLNAME");
					if ($name) {
						     $name = explode(" ", $name);
							 if (count($name) >= 3) {
							 	$name = array_reverse($name);
							 	$contactData["C_LASTNAME"] = $name[0];
								$contact_type = new ContactType(1);
								$dbfields = $contact_type->getTypeDbFields();
								$i = 1;
								if (in_array('C_MIDDLENAME', $dbfields)) {
							    	$contactData["C_MIDDLENAME"] = $name[1];
							    	$i++;
								} 
								for ($j = 0; $j < $i; $j++) {
									unset($name[$j]);
								}
								$name = array_reverse($name);
								$contactData["C_FIRSTNAME"] = implode(" ", $name);
							 } elseif(count($name) == 2) {
							     $contactData["C_FIRSTNAME"] = $name[0];
							     $contactData["C_LASTNAME"] = $name[1];						     
							 } else {
							     $contactData['C_FIRSTNAME'] = $name[0];
							 }
					}					
					foreach ($cmfields as $field) {
						if($field["type"] == "IMAGE") {
								
							try {
								$desc = Contact::uploadFile(false, $_FILES[$field["dbname"]], $field);
							} catch (Exception $e) {
								$this->pageState->addError ($e->getMessage());
								$hasError = true;
								break;
							}
													
							if (!$desc) {
							    unset($contactData[$field["dbname"]]);
							} else {
								$contactData[$field["dbname"]] = $desc;
							}
						}
						if ($field["type"] == "DATE") {
							$param = $contactData[$field["dbname"]];
							$m = intval($param["m"]);
							$d = intval($param["d"]);
							$y = intval($param["y"]);
							if (!$y) {
							    unset($contactData[$field["dbname"]]);
							} elseif ($y < 10) {
								$y = 2000 + intval($y);
							} elseif ($y < 100) {
								$y = 1900 + $y;
							}
							if ($m < 10) {
							    $m = "0".$m;
							}
							if ($d < 10) {
							    $d = "0".$d;
							}
							
                            if ($y) {	
								$val = DATE_DISPLAY_FORMAT;
								$val = str_replace("m", $m, $val);
								$val = str_replace("d", $d, $val);
								$val = str_replace("Y", $y, $val);
								$val = str_replace("y", $y, $val);
								$contactData[$field["dbname"]] = $val;
                            }
						}
					}
					$result = $this->addContact($contactData, $language, $kernelStrings, $widgetData);
					if ($result !== true) { 
						break;
					}
					$contentFilename = "after_signup.htm";
					$result = "success";
					
				} while (false);
			}
			
			$preproc->assign ("contentFilename", $contentFilename);
			$preproc->assign ("result", $result);
			$preproc->assign ("cmfields", $cmfields);
			$preproc->assign( "is_host", Wbs::isHosted());
			$preproc->assign ("labelsCMFields", $cmfieldsLabels);
			$preproc->assign ('countries', Wbs::getCountries($widgetData['WG_LANG']));
			
			return array("tplFilename" => "sbsc_widget.htm");
		}
		
		function saveFile ($dbname) {
			global $imgfiles;

			if ( strlen($imgfiles['name'][$imgFieldEdited]) ) {
				// Move image file to the temporary directory
				//
				$fileName = uniqid( TMP_FILES_PREFIX );
				$destPath = WBS_TEMP_DIR."/".$fileName;
				$srcPath =  $imgfiles['tmp_name'][$imgFieldEdited];
				if ( !move_uploaded_file($srcPath, $destPath) ) {
					$errorStr = $cmStrings['amc_erroruploadingfile_message'];
					break;
				}

				// Process image
				//
				$originalName = $imgfiles['name'][$imgFieldEdited];
				$fileType = $imgfiles['type'][$imgFieldEdited];

				$fieldDescription = $contactData[$imgFieldEdited];

				$thumbnailError = null;
				$res = processImageFieldFile( $destPath, $originalName, $fileType, $thumbnailError, $kernelStrings, $fieldDescription );
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					break;
				}

				if ( PEAR::isError($thumbnailError) ) {
					$errorStr = $thumbnailError->getMessage();
					$isWarning = 1;
				}
			
				return $fieldDescription;
			}
		}
		
		function prepareConstructorPage (&$preproc, &$pageState, $widgetData = array ()) {
			$type = $this->getContactType()->getType($pageState->language);
			$preproc->assign ("main_section", $this->getContactType()->getMainSection());
			$preproc->assign ("typeDesc", $type['fields']);
			
			$cmfields = array ();
			$widgetParams = $this->getRealParams($widgetData);
			if ($widgetParams && !empty($widgetParams["CMFIELDS"]))
				$cmfields = split (",", $widgetParams["CMFIELDS"]);
			if (!$widgetParams) {
				$cmfields = split(",", $this->type->fieldsData["CMFIELDS"]["default"]);
			}
			$preproc->assign ("incCMFields", $cmfields);
			
			
			if ($widgetData && !empty($widgetParams["CMFIELDSLABELS"])) {
				$labelsVals = split (";", $widgetParams["CMFIELDSLABELS"]);
				$cmfieldsLabels = array ();
				foreach($labelsVals as $cLabelStr) {
					list ($field, $label) = split ("=", $cLabelStr, 2);
					$cmfieldsLabels[$field] = $label;
				}
				$preproc->assign ("incCMFields", $cmfields);
				$preproc->assign ("labelsCMFields", $cmfieldsLabels);
			}
			
			parent::prepareConstructorPage ($preproc, $pageState, $widgetData);
			
			//$customFilename = $this->type->getHTMLPath() . "/custom.htm";
			//$preproc->assign ("subtypeAfterFormFile", array ("fields" => $customFilename));
		}
		
		function checkFieldsValues(&$params) {
			if (!$params["CMFIELDS"]["C_EMAILADDRESS"] && $params["DOPTIN"]) {
				return PEAR::raiseError($this->type->strings["custom_emaildoptin_error"]);
			}
			return parent::checkFieldsValues ($params);
		}
		
		function prepareFieldsValues (&$params) {
			if (isset($params["CMFIELDS"]) && is_array($params["CMFIELDS"]))
				$params["CMFIELDS"] = join (",", array_keys($params["CMFIELDS"]));
			if (isset($params["CMFIELDSLABELS"]) && is_array($params["CMFIELDSLABELS"])) {
				$labelStrs = array ();
				foreach ($params["CMFIELDSLABELS"] as $cId => $cLabel)
					$labelStrs[] = $cId . "=" . str_replace(";","",$cLabel);
				$labelsStr = join (";", $labelStrs);
				$params["CMFIELDSLABELS"] = $labelsStr;
			}
			
			parent::prepareFieldsValues ($params);
		}
	}
	
	class SBSCPhotoSubtype extends SBSCCustomSubtype {
		function SBSCPhotoSubtype (&$type) {
			$this->id = "PHOTO";
			$this->fields = array ("PHOTOFIELDS");
			parent::SBSCSubtype ($type);
		}		
		
		function prepare (&$preproc, &$widgetData) {
			global $language;
			$hasPhotoField = $this->getContactType()->getFieldId('C_PHOTO') ? true : false;
			$preproc->assign ("hasPhotoField", $hasPhotoField);
			
			if ($this->pageState->getParam("name")) {
				 $name = $this->pageState->getParam("C_FULLNAME");
				 if ($name) {
				     $name = explode(" ", $name, 3);
				 }
				 if (count($name) == 3) {
				     $this->pageState->params["C_FIRSTNAME"] = $name[0];
				     $this->pageState->params["C_MIDDLENAME"] = $name[1];
				     $this->pageState->params["C_LASTNAME"] = $name[2];
				 } elseif (count($name) == 2) {
				     $this->pageState->params["C_FIRSTNAME"] = $name[0];
				     $this->pageState->params["C_LASTNAME"] = $name[1];						     
				 } else {
				     $this->pageState->params['C_FIRSTNAME'] = $name[0];
				 }
			}
			$widgetData["params"]["CMFIELDS"] = "C_FIRSTNAME,C_LASTNAME,C_EMAILADDRESS,C_PHOTO";
			
			
			$field_id = ContactType::getFieldId('C_FIRSTNAME');
			$field_info =  ContactType::getField($field_id, $widgetData['WG_LANG']);
			$preproc->assign ('nameLabel', $field_info['name']);
			parent::prepare ($preproc, $widgetData, "photo_form.htm");	
			return array("tplFilename" => "sbsc_widget.htm");
		}
		
		function checkFieldsValues(&$params) {
			//if ($params["PHOTOFIELDS"] == "name" && $params["DOPTIN"]) {
			//	return PEAR::raiseError($this->type->strings["photo_emaildoptin_error"]);
			//}
			return SBSCSubtype::checkFieldsValues ($params);
		}
		
		
		function prepareConstructorPage (&$preproc, &$pageState, $widgetData = array ()) {
			$customFilename = $this->type->getHTMLPath() . "/photo.htm";
			//$preproc->assign ("subtypeAfterFormFile", array("contacts" => $customFilename));
			
			
			$hasPhotoField = $this->getContactType()->getFieldId('C_PHOTO') ? true : false;
			
			if (!$hasPhotoField)
				$pageState->addError($this->type->strings["wg_nophotofield_error"]);
			
			SBSCSubtype::prepareConstructorPage ($preproc, $pageState, $widgetData);
		}
		
		function getEmbInfo ($widgetData) {
			$height = 320;
			return array("height" => $height, "min_width" => 220);
		}
	}
?>