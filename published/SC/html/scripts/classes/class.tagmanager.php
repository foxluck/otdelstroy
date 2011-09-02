<?php
	define('TAGGEDOBJECT_PRODUCT', 'product');

	ClassManager::includeClass('tag');
	
	class TagManager{
		
		/**
		 * @param string $name
		 * @return Tag: or null
		 */
		function findTagByName($name){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$dbq = '
				SELECT * FROM ?#TAGS_TBL WHERE `name`=? LIMIT 1
			';
			$DBRes = $DBHandler->ph_query($dbq, $name);
			if(!$DBRes->getNumRows()){
				return null;
			}
			
			$Tag = ClassManager::getInstance('tag');
			/* @var $Tag Tag */
			$Tag->loadFromArray($DBRes->fetchAssoc());
			return $Tag;
		}
		
		function getAllTags($tags_num = null){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$dbq = '
				SELECT t_tag.* FROM ?#TAGS_TBL t_tag ORDER BY t_tag.name ASC
				'.(is_null($tags_num)?'':' LIMIT 0,'.intval($tags_num)).'
			';
			
			$r_Tag = array();
			$DBRes = $DBHandler->ph_query($dbq, array('OBJECT_TYPE' => $object_type, 'OBJECT_ID' => $object_id, 'LANGUAGE_ID' => $language_id));
			
			$min_weight = 0;
			$max_weight = 0;
			
			if(is_null($tags_num)){
				
				while ($row = $DBRes->fetchAssoc()) {
					$tag = new Tag();
					$tag->loadFromArray($row);
					$r_Tag[] = &$tag;
					unset($tag);
				}
			}else{
				$rows = $DBRes->fetchArrayAssoc();
				$sort_func_name = create_function('$a,$b', 'return strcmp($a["name"], $b["name"]);');
				usort($rows, $sort_func_name);
				foreach ($rows as $row) {
					
					$tag = new Tag();
					$tag->loadFromArray($row);
					$r_Tag[] = &$tag;
					unset($tag);
				}
			}
			
			return $r_Tag;
		}
		
		/**
		 * @param string $object_type - product
		 * @param int $language_id - language id or null
		 * @param int $object_id - if set will returned only for this object tags
		 * @param int $tags_num - if not null will returned exact tags number
		 * @param bool $only_with_weight - if true will returned only tags with weight else all
		 * 
		 * @return array: of tags
		 */
		function getTags($object_type = null, $language_id = null, $object_id = null, $tags_num = null, $only_with_weight = false){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			$dbq = '
				SELECT t_tag.*, COUNT(t_tagcon.tag_id) as `weight` FROM ?#TAGS_TBL t_tag LEFT JOIN ?#TAGGED_OBJECTS_TBL t_tagcon ON t_tag.id=t_tagcon.tag_id
				WHERE 1
				'.(is_null($object_type)?'':'AND t_tagcon.object_type=?OBJECT_TYPE').'
				'.(is_null($language_id)?'':'AND t_tagcon.language_id=?LANGUAGE_ID').'
				'.(is_null($object_id)?'':'AND t_tagcon.object_id=?OBJECT_ID').'
				GROUP BY t_tagcon.tag_id 
				'.(!$only_with_weight?'':'HAVING weight>0').'
				'.(is_null($tags_num)?'ORDER BY t_tag.name ASC':'ORDER BY weight DESC LIMIT 0,'.intval($tags_num)).'
			';
			
			$r_Tag = array();
			$DBRes = $DBHandler->ph_query($dbq, array('OBJECT_TYPE' => $object_type, 'OBJECT_ID' => $object_id, 'LANGUAGE_ID' => $language_id));
			
			$min_weight = 0;
			$max_weight = 0;
			
			if(is_null($tags_num)){
				
				while ($row = $DBRes->fetchAssoc()) {
					
					$min_weight = $row['weight']>$min_weight&&$min_weight?$min_weight:$row['weight'];
					$max_weight = $row['weight']>$max_weight?$row['weight']:$max_weight;
					$r_Tag[] = new Tag();
					$r_Tag[count($r_Tag)-1]->loadFromArray($row);
				}
			}else{
				$rows = $DBRes->fetchArrayAssoc();
				$sort_func_name = create_function('$a,$b', 'return strcmp($a["name"], $b["name"]);');
				usort($rows, $sort_func_name);
				foreach ($rows as $row) {
					
					$min_weight = $row['weight']>$min_weight&&$min_weight?$min_weight:$row['weight'];
					$max_weight = $row['weight']>$max_weight?$row['weight']:$max_weight;
					$r_Tag[] = new Tag();
					$r_Tag[count($r_Tag)-1]->loadFromArray($row);
				}
			}
			TagManager::MinTagWeight($min_weight);
			TagManager::MaxTagWeight($max_weight);
			
			return $r_Tag;
		}
		
		function getObjectTagsStrings($object_type, $object_id, $field_name){
			
			$object_tags = array();
			ClassManager::includeClass('languagesmanager');
			$r_languageEntry = LanguagesManager::getLanguages();
			
			foreach ($r_languageEntry as $languageEntry){
				/*@var $languageEntry Language*/
				
				$object_tags[$field_name.'_'.$languageEntry->iso2] = '';
				$lang_tags = TagManager::getTags($object_type, $languageEntry->id, $object_id);
				foreach ($lang_tags as $tag){
					$object_tags[$field_name.'_'.$languageEntry->iso2] .= ', '.$tag->name;
				}
				
				$object_tags[$field_name.'_'.$languageEntry->iso2] = substr($object_tags[$field_name.'_'.$languageEntry->iso2], 2);
			}	
			
			return $object_tags;
		}
		
		function saveTags($object_type, $object_id, $field_name, $data){
			
			$TagManager = &ClassManager::getInstance('tagmanager');
			/* @var $TagManager TagManager */
			$TagManager->removeObjectTags($object_type, $object_id);
			
			ClassManager::includeClass('languagesmanager');
			$r_languageEntry = LanguagesManager::getLanguages();
			foreach ($r_languageEntry as $languageEntry){
				/*@var $languageEntry Language*/

				$field_name_lang = $field_name.'_'.$languageEntry->iso2;
				if(!isset($data[$field_name_lang]) || !$data[$field_name_lang])continue;
				
				$tag_names = explode(",",$data[$field_name_lang]);
				foreach ($tag_names as $tag_name){
					
					$tag_name = trim($tag_name);
					if(!$tag_name)continue;
					$Tag = $TagManager->findTagByName($tag_name);
					if(!Tag::isTag($Tag)){
						
						$Tag = new Tag();
						$Tag->create($tag_name);
					}
					
					$Tag->tagObject($object_type, $languageEntry->id, $object_id);
				}
			}
		}
		
		function MinTagWeight($min = null){
			
			static $min_weight;
			
			if(is_null($min)){
				
				return $min_weight;
			}else{
				
				$prev = $min_weight;
				$min_weight = $min;
				return $prev;
			}
		}
		
		function MaxTagWeight($max = null){
			
			static $max_weight;
			
			if(is_null($max)){
				
				return $max_weight;
			}else{
				
				$prev = $max_weight;
				$max_weight = $max;
				return $prev;
			}
		}
		
		/**
		 * Remove all or one object tags
		 *
		 * @param string $object_type - product
		 * @param int $object_id
		 * @param int $tag_id
		 */
		function removeObjectTags($object_type, $object_id, $tag_id = null){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			
			if(is_null($tag_id)){
				$dbq = '
					DELETE FROM ?#TAGGED_OBJECTS_TBL WHERE object_type=?OBJECT_TYPE AND object_id=?OBJECT_ID 
				';
			}else{
				$dbq = '
					DELETE FROM ?#TAGGED_OBJECTS_TBL WHERE object_type=?OBJECT_TYPE AND object_id=?OBJECT_ID AND tag_id=?TAG_ID
				';
			}
			
			$DBHandler->ph_query($dbq, array('OBJECT_TYPE' => $object_type, 'OBJECT_ID' => $object_id, 'TAG_ID' => $tag_id));
		}
		
		function removeTags(){
			
			$Register = &Register::getInstance();
			$DBHandler = &$Register->get(VAR_DBHANDLER);
			/* @var $DBHandler DataBase */
			$dbq = '
				DELETE FROM ?#TAGGED_OBJECTS_TBL 
			';
			$DBHandler->ph_query($dbq);
			
			$dbq = '
				DELETE FROM ?#TAGS_TBL 
			';
			$DBHandler->ph_query($dbq);
		}
		
		function cleanUpTags(){
			
		}
		
		
		
		/**
		 * Generate tags cloud for setting tags for object
		 * 
		 * @param string $field_id - form field id, with that field will interact cloud
		 * @param string $object_type - product 
		 */
		function getTagsCloud($field_id, $object_type){
			
			$TagManager = &ClassManager::getInstance('tagmanager');
			/* @var $TagManager TagManager */
			
			$all_tags_names = array();
			//$all_Tags = $TagManager->getTags($object_type);
			$all_Tags = $TagManager->getAllTags();
			$min_tag_weight = $TagManager->MinTagWeight()?$TagManager->MinTagWeight():1;
			$max_tag_weight = $TagManager->MaxTagWeight()?$TagManager->MaxTagWeight():1;
			$tag_weight_step = ($max_tag_weight-($min_tag_weight>0?$min_tag_weight:1))/10;
			$tag_weight_step = $tag_weight_step?$tag_weight_step:1;
			
			ClassManager::includeClass('languagesmanager');
			$r_languageEntry = LanguagesManager::getLanguages();
			$r_iso2 = array();
			foreach ($r_languageEntry as $languageEntry){
				/*@var $languageEntry Language*/
				$r_iso2[] = $languageEntry->iso2;
			}
			
			ob_start();
			
			if(count($all_Tags)){
				?>
				<a href="javascript:void(0);" id="showtags_hndl" onclick="getLayer('all-tags').style.display='block'; this.style.display='none'; getLayer('hidetags_hndl').style.display='inline'; return false;"><?php echo translate('btn_show_tags');?></a>
				<a style="display:none;" href="javascript:void(0)" id="hidetags_hndl" onclick="getLayer('all-tags').style.display='none'; this.style.display='none'; getLayer('showtags_hndl').style.display='inline'; return false;"><?php echo translate('btn_hide_tags');?></a>
			<?php } ?>
				<div id="all-tags" style="max-height:200px;overflow:auto;display: none;">
				<?php foreach ($all_Tags as $Tag ){
					
					$all_tags_names[] = str_replace('"', '\"', $Tag->name);
					$font_size = (80+ceil($Tag->weight/$tag_weight_step));
					$rgb = 90 - ceil($Tag->weight/$tag_weight_step);
					$rgb = $rgb<0?0:$rgb;
					$font_weight = 600+(($Tag->weight/$tag_weight_step)*(30/$tag_weight_step));
					print '&nbsp; <a style="font-weight:'.$font_weight.';color:rgb('.$rgb.','.$rgb.','.$rgb.');font-size:'.$font_size.'%;" href="javascript:void(0)" tagName="'.xHtmlSpecialChars($Tag->name).'" onclick="addTag(this);return false;">'.xHtmlSpecialChars($Tag->name).'</a> &nbsp;';
				}?>
				</div>
		
				<script type="text/javascript" src="<?php echo URL_JS;?>/_dropmenu.js"></script>
				<script type="text/javascript" src="<?php echo URL_JS;?>/input_autocomplete.js"></script>
				<script type="text/javascript">
					
					function tag_Add(tagList,name){
						
						var reg = new RegExp(",?"+name+",?", "gm");
						var capit=tagList.match(reg);
						
						if(capit){
							tagList = tagList.replace(reg, ",");
							tagList = tagList.replace(/^\s?,\s?|\s?,\s?$|,\s*,/g,"");
							tagList = tagList.replace(/,\s*,/g, ",");
						}else{
							tagList += (tagList.length?",":"")+name;
						}
						
						return tagList;
					}
					
					function tag_Variants(){
					
						if (timeout) clearTimeout(timeout); 
						timeout = setTimeout(function(){
						
						var tagList = getLayer("tagList");
						if(!tagList.value.length)return;
						
					//	alert(tagList.value);
						
						}, 100); 
					}
					
					function addTag(objA){
						
						var objInput = null;
						for(var k in fields_ids){
							
							if(is_null(focused_tags_field) || focused_tags_field.id == fields_ids[k]){
								objInput = getLayer(fields_ids[k]);break;
							}
						}
						
						if(objInput)
							objInput.value = tag_Add(objInput.value, objA.getAttribute('tagName'));
					}
					
					focused_tags_field = null;
					fields_ids = [<?php echo "'{$field_id}_".implode("', '{$field_id}_", $r_iso2)."'";?>];
					all_tags = [<?php echo '"'.implode('", "', $all_tags_names).'"';?>];
				<?php foreach ($r_iso2 as $iso_2){?>
				
					inp_autocomp = new InputAutocompete();
					inp_autocomp.all_variants = all_tags;
					inp_autocomp.init(getLayer("<?php echo xHtmlSpecialChars($field_id.'_'.$iso_2);?>"));
				<?php } ?>
				</script>
				
			<?php
			
			$tags_cloud = ob_get_contents();
			ob_end_clean();
			
			return $tags_cloud;
		}
	}
?>