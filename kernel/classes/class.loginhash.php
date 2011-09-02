<?php
	class LoginHash{
		
		var $LOGIN;
		var $DBKEY;
		var $HASH;
				
		function generateNew($DBKEY, $LOGIN, $params){
			
			$this->LOGIN = $LOGIN;
			$this->DBKEY = $DBKEY;
			$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DBKEY) );
			$dom = @domxml_open_file( realpath($filePath) );
			if ( !$dom )return false;
			$xpath = xpath_new_context($dom);
			$hashesNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_LOGINHASHES );
			 
			
			if ( !$hashesNode || !count($hashesNode->nodeset) ){
				
				$databaseNode = &xpath_eval( $xpath, "/".HOST_DATABASE );
				$hashesNode = @create_addElement( $dom, $databaseNode->nodeset[0], HOST_LOGINHASHES );
			}else
				$hashesNode = &$hashesNode->nodeset[0];
			
			// Delete old hash entry
			//
			$nodeset = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_LOGINHASHES );
			if ( !$nodeset || !isset($nodeset->nodeset[0]) )
				return PEAR::raiseError( $kernelStrings[ERR_XML], SOAPROBOT_ERR_DBPROFILECREATE );
	
			$apps_node = $nodeset->nodeset[0];
			 
			// Delete all old entry
			// 
			if ( $hast_path = &xpath_eval($xpath, "/".HOST_DATABASE."/".HOST_LOGINHASHES."/".HOST_LOGINHASH ))
				{
				foreach ( $hast_path->nodeset as  $node )
					{	
					$hashesNode->remove_child($node);
					}
				}

			do{
				
				$flag = false;
				$this->HASH = generateUserPassword(8);
					
				$hashNode = &xpath_eval($xpath, sprintf("%s[@HASH='%s']", HOST_LOGINHASH, $this->HASH));
				$flag = ($hashNode && count($hashNode->nodeset));
			}while (@$max++<100 || $flag);
			
			$hashNode = @create_addElement( $dom, $hashesNode, HOST_LOGINHASH );
			
			if(is_array($params))foreach ($params as $k=>$v)
				$hashNode->set_attribute( $k, $v );
				
			$hashNode->set_attribute( "LOGIN", $this->LOGIN );
			$hashNode->set_attribute( "HASH", $this->HASH );
			$hashNode->set_attribute( "UNCONFIRMED", 1 );
			
			@$dom->dump_file($filePath, false, true);
		}
		
		function loadByHash($DBKEY, $HASH){
			
			$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DBKEY) );
			$dom = @domxml_open_file( realpath($filePath) );
			if ( !$dom )return false;

			$xpath = xpath_new_context($dom);
			$hashNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_LOGINHASHES.'/'.HOST_LOGINHASH."[@HASH='".htmlspecialchars($HASH)."']" );

			if ( !$hashNode || !count($hashNode->nodeset) ){
				return false;
			}else
				$hashNode = &$hashNode->nodeset[0];
			
			if ($hashNode->get_attribute ("UNCONFIRMED") == '1') {
				$this->DBKEY = $DBKEY;
				$this->HASH = $HASH;
				$this->LOGIN = $hashNode->get_attribute('LOGIN');
				$this->redirect = $hashNode->get_attribute('redirect');
			}
			 
			return true;
		}
		
		function loadFirst ($DBKEY, $login = null) {
			$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($DBKEY) );
			$dom = @domxml_open_file( realpath($filePath) );
			if ( !$dom )return false;

			$xpath = xpath_new_context($dom);
			$path = "/".HOST_DATABASE."/".HOST_LOGINHASHES.'/'.HOST_LOGINHASH;
			if ($login)
				$path .= "[@LOGIN='" . htmlspecialchars($login) . "']";
			$hashNode = &xpath_eval( $xpath,  $path);

			if ( !$hashNode || !count($hashNode->nodeset) ){
				return false;
			}else
				$hashNode = &$hashNode->nodeset[0];

			$this->DBKEY = $DBKEY;
			$this->HASH = $hashNode->get_attribute ("HASH");
			$this->LOGIN = $hashNode->get_attribute('LOGIN');
			$this->redirect = $hashNode->get_attribute('redirect');
			return true;
		}
		
		function deleteHash(){
			
			$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($this->DBKEY) );
			$dom = @domxml_open_file( realpath($filePath) );
			if ( !$dom )return false;

			$xpath = xpath_new_context($dom);
			$hashNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_LOGINHASHES.'/'.HOST_LOGINHASH."[@HASH='".htmlspecialchars($this->HASH)."']" );

			if ( !$hashNode || !count($hashNode->nodeset) ){
				return false;
			}else
				$hashNode = &$hashNode->nodeset[0];
				
			$hashNode->set_attribute( "UNCONFIRMED", 0 );
//			$hashNode->unlink_node();
			@$dom->dump_file($filePath, false, true);
		}
	}
?>