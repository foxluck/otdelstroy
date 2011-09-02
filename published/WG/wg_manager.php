<?php
	/****
	* Widgets data manager (all data functions)
	****/
	class WidgetManager extends DataManager {
		
		/****
		*	Construtor
		****/
		function WidgetManager (&$kernelStrings, &$wgStrings) {
			$fieldsList = array ("WT_ID", "WST_ID", "WG_FPRINT", "WG_CREATED_BY", "WG_USER", "WG_CREATED_DATETIME", "WG_MODIFIED_BY", "WG_MODIFIED_DATETIME", "WG_DESC", "WG_LANG", "WG_CREATED_FROM");
			parent::DataManager("WG_WIDGET", "WG_ID", $fieldsList, $kernelStrings, $wgStrings);
		}
		
		function checkParams ($params) {
			$factory = WidgetTypeFactory::getInstance ();
			$typeObj = $factory->getWidgetType($params["WT_ID"]);
			$subtypeObj = $typeObj->subtypes[$params["WST_ID"]];
			if(!$subtypeObj)
				return PEAR::raiseError ("Cannot get subtype obj for setWidgetParams");	
			
			$res = $subtypeObj->checkFieldsValues ($params);
			return $res;
		}
		
		function update($itemKey, $params, $setParams = true) {
			if ($setParams && PEAR::isError($res = $this->checkParams($params)))
				return $res;
			
			unset($params["WG_ID"]);
			unset($params["WT_ID"]);
			$params["WG_MODIFIED_BY"] = $this->getUsername ();
			$params["WG_MODIFIED_DATETIME"] = convertToSqlDateTime( time() );
			
			$res = parent::update ($itemKey, $params);
			if (PEAR::isError($res))
				return $res;
			if ($setParams)
				$res = $this->setWidgetParams ($itemKey, $params);
			return $res;
		}
		
		function createWidget($params) {
			if (PEAR::isError($res = $this->checkParams($params)))
				return $res;
			
			$factory = WidgetTypeFactory::getInstance ();
			$typeObj = $factory->getWidgetType($params["WT_ID"]);
			$subtypeObj = $typeObj->subtypes[$params["WST_ID"]];
			
			$params = $subtypeObj->convertCreateParams($params);
			return $this->add($params);
		}
		
		function add($params) {
			if (PEAR::isError($res = $this->checkParams($params)))
				return $res;
			
			global $currentUser;
			$params["WG_CREATED_BY"] = $this->getUsername ();
			$params["WG_CREATED_DATETIME"] = convertToSqlDateTime( time() );
			$params["WG_FPRINT"] = substr(md5(rand()),0,8);
			if (empty($params["WG_USER"]))
				$params["WG_USER"] = $currentUser;
			
			$id = parent::add ($params);
			if (PEAR::isError($id))
				return $id;
			$res = $this->setWidgetParams ($id, $params);
			if (PEAR::isError($res))
				return $res;
			return $id;
		}
		
		function setWidgetParams ($id, $params, $onlyChange = false) {
			$widgetData = $this->get($id);
			$factory = WidgetTypeFactory::getInstance ();
			$typeObj = $factory->getWidgetType($widgetData["WT_ID"]);
			$subtypeObj = $typeObj->subtypes[$widgetData["WST_ID"]];
			if(!$subtypeObj)
				return PEAR::raiseError ("Cannot get subtype obj for setWidgetParams");	
			
			$subtypeObj->prepareFieldsValues($params);
			
			// Delete old param values
			$dsql = new CDeleteSqlQuery ("WG_PARAM");
			$dsql->addConditions ("WG_ID", $id);
			if ($onlyChange) {
				$strParams = array ();
				foreach (array_keys($params) as $cParam) 
					$strParams[] = "'" . $cParam . "'";
				if ($strParams)
					$dsql->addConditions("WGP_NAME IN (" . join(",", $strParams). ")");
			}
			$res = db_query( $dsql->getQuery());
			if ( PEAR::isError($res) )
				return $res;
			
			$fields = $subtypeObj->getFields ();
			
			// Insert new param values
			foreach ($params as $cKey => $cValue) {
				$inSubfields = false;
				if (strpos($cKey, "_")) {
					list($fname, $fsubname) = split("_", $cKey);
					if (!empty($typeObj->fieldsData[$fname]["subfields"]) && is_array($typeObj->fieldsData[$fname]["subfields"]))
						$inSubfields = in_array ($fsubname, @array_keys($typeObj->fieldsData[$fname]["subfields"]));
				}
				if (!$inSubfields && !in_array($cKey, $fields))
					continue;
				
				if (in_array($cKey, $fields)) {
					$fieldRow = $typeObj->fieldsData[$cKey];
					if (!empty($fieldRow["min"]) && $fieldRow["min"] && $cValue < $fieldRow["min"])
						$cValue = $fieldRow["min"];
					if (!empty($fieldRow["max"]) && $fieldRow["max"] && $cValue > $fieldRow["max"])
						$cValue = $fieldRow["max"];					
				}
				
				$isql = new CInsertSqlQuery ("WG_PARAM");
				$isql->addFields (array("WG_ID" => $id, "WGP_NAME" => $cKey, "WGP_VALUE" => $cValue), array ("WG_ID", "WGP_NAME", "WGP_VALUE"));
				
				
				$res = db_query( $isql->getQuery());
				if ( PEAR::isError($res) ) {
					return $res;
				}
			}
		}
		
		function setWidgetParam ($id, $name, $value) {
			if (!$id || !$name)
				return false;
			
			// Delete old param values
			$dsql = new CDeleteSqlQuery ("WG_PARAM");
			$dsql->addConditions ("WG_ID", $id);
			$dsql->addConditions ("WGP_NAME", $name);
			$res = db_query( $dsql->getQuery());
			if ( PEAR::isError($res) )
				return $res;
				
			$isql = new CInsertSqlQuery ("WG_PARAM");
			$isql->addFields (array("WG_ID" => $id, "WGP_NAME" => $name, "WGP_VALUE" => $value), array ("WG_ID", "WGP_NAME", "WGP_VALUE"));				
				
			$res = db_query( $isql->getQuery());
			if ( PEAR::isError($res) )
				return $res;
			return $res;
		}
		
		
		function get ($id) {
			if (pear::isError($itemData = parent::get ($id)))
				return $itemData;
			$this->fillItem ($itemData);
			return $itemData;
		}
		
		function getByFprint ($fprint) {
			$sql = new CSelectSqlQuery ($this->tableName);
			$sql->addConditions ("WG_FPRINT", $fprint);
			if (pear::isError($itemData = db_query_result ($sql->getQuery(), DB_ARRAY)))
				return $itemData;
			$this->fillItem ($itemData);
			return $itemData;
		}
		
		function changeWidgetFprint ($wgId, $newFprint, $kernelStrings) {
			global $wgStrings;
			if (ereg("[^A-Za-z0-9_]", $newFprint))
				return PEAR::raiseError($kernelStrings["shurl_wrongsymbols_error"]);
			
			$otherWidget = $this->getByFprint ($newFprint);
			if ($otherWidget && ($otherWidget["WG_ID"] != $wgId))
				return PEAR::raiseError($kernelStrings["shurl_existedname_error"]);
			
			//"shurl_reserved_word_error"
			if ($newFprint == 'album')
				return PEAR::raiseError('"album" is a reserved name. Please enter any other text.');
				
			$params = array("WG_FPRINT" => $newFprint);
			return $this->update ($wgId, $params, false);
		}
		
		function fillItem (&$itemData) {
			if ($itemData) {
				$itemData["params"] = $this->getWidgetParams($itemData["WG_ID"]);
				$itemData["createdDatetime"] = convertToDisplayDateTime($itemData["WG_CREATED_DATETIME"], false, true, true);
				$itemData["modifiedDatetime"] = convertToDisplayDateTime($itemData["WG_MODIFIED_DATETIME"], false, true, true);
			}
		}
		
		function getWidgetParams ($id) {
			$sql = new CSelectSqlQuery ("WG_PARAM");
			$sql->addConditions ("WG_ID", $id);
			
			$paramsDataList = $this->getListFromQuery ($sql->getQuery (), "WGP_NAME");
			$params = array ();
			foreach ($paramsDataList as $cName => $cRow)
				$params[$cName] = $cRow["WGP_VALUE"];
			return $params;			
		}
		
		function getWidgets ($typeId, $subtypeId = "", $orderBy = "") {
			$sql = new CSelectSqlQuery ("WG_WIDGET", "wg");
			$sql->setSelectFields ("wg.*");
			$sql->addConditions ("wg.WT_ID", $typeId);
			if ($subtypeId)
				$sql->addConditions ("wg.WST_ID", $subtypeId);
			if ($orderBy)
				$sql->setOrderBy($orderBy);
			
			$sql->setGroupBy("wg.WG_ID");
			$data = $this->getListFromQuery($sql->getQuery (), "WG_ID");
			if (pear::isError($data))
				return $data;
			return $data;
		}
		
		function getWidgetsForSubtypes($subtypes, $orderBy) {
			$orConditions = array ();
			foreach($subtypes as $cst)
				$orConditions[] = "(WT_ID='" . $cst->type->id ."' AND WST_ID='" . $cst->id . "')";
			$orStr = join (" OR ", $orConditions);
			
			$sql = new CSelectSqlQuery ("WG_WIDGET", "wg");
			if ($orStr) {
				$sql->addConditions ($orStr);
			}
			if ($orderBy)
				$sql->setOrderBy($orderBy);
			
			$data = $this->getListFromQuery($sql->getQuery (), "WG_ID");
			if (pear::isError($data))
				return $data;
			return $data;
		}
		
		function getUserWidgets ($UID, $typeId, $subtypeId = "", $orderBy = "") {
			$sql = new CSelectSqlQuery ("WG_WIDGET", "wg");
			$sql->setSelectFields ("wg.*");
			//$sql->leftJoin ("WG_PARAM", "wgp", "wg.WG_ID=wgp.WG_ID");
			$sql->addConditions ("wg.WT_ID", $typeId);
			if ($subtypeId)
				$sql->addConditions ("wg.WST_ID", $subtypeId);
			//$sql->addConditions ("wgp.WGP_NAME", "UID");
			//$sql->addConditions ("wgp.WGP_VALUE", $UID);
			$sql->addConditions("wg.WG_USER", $UID);
			if ($orderBy)
				$sql->setOrderBy($orderBy);
			
			$sql->setGroupBy("wg.WG_ID");
			$data = $this->getListFromQuery($sql->getQuery (), "WG_ID");
			if (pear::isError($data))
				return $data;
			return $data;
		}
		
		function getUsername () {
			global $currentUser;
			$systemUsers = listSystemUsers( array (), $this->kernelStrings );
			return getArrUserName($systemUsers[$currentUser]);
		}
	}
	
	
	class WidgetTypeManager extends DataManager {
		var $factory;
		
		/****
		*	Construtor
		****/
		function WidgetTypeManager (&$kernelStrings, &$wgStrings) {
			$fieldsList = array ("WT_ID", "WT_NAME");
			parent::DataManager("WG_TYPE", "WT_ID", $fieldsList, $kernelStrings, $wgStrings);
			
			$this->factory = WidgetTypeFactory::getInstance ();
		}
		
		function getList () {
			$d = dir(PATH_WG_WIDGETS);
			
			$result = array ();	
			// Added because we are not delete old files
			$registredWidgetTypes = array ("DDList", "DDUploader", "PDList", "SBSC");			
			if (!$d)
				return PEAR::raiseError ("Cannot find widgets directory");
			while (false !== ($entry = $d->read())) {
    		if (substr($entry,0,1) == "." || substr($entry,0,1) == "_")
    			continue;
    		if (!in_array($entry, $registredWidgetTypes))
    			continue;
    		$result[] = $entry;
			}
			$d->close();
			return $result;
		}
		
		function get ($id) {
			$types = $this->getList ();
			$type = $types[$id];
			if (!$type)
				return PEAR::raiseError("Cannot get type: " . $id);
		}
		
		function getObjsList () {
			$result = array ();
			$typesId = $this->getList ();
			foreach ($typesId as $cTypeId) {
				$type = $this->factory->getWidgetType ($cTypeId);
				if (PEAR::isError($type))
					return $type;
				$result[$cTypeId] = $type;
			}
			return $result;
		}
		
		function getObj ($id) {
			return $this->factory->getWidgetType ($id);
		}
	}
	
?>