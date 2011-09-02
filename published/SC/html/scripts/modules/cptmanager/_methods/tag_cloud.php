<?php
	$local_settings = &$Args[0]['local_settings'];
	$tags_num = isset($local_settings['tags_num'])&&intval($local_settings['tags_num'])?intval($local_settings['tags_num']):50;

	$TagManager = &ClassManager::getInstance('tagmanager');
	/* @var $TagManager TagManager */
	$languageEntry = &LanguagesManager::getCurrentLanguage();
	
	$all_tags_names = array();
	$all_Tags = $TagManager->getTags('product', $languageEntry->id, null, $tags_num, true);

	if(!count($all_Tags))return ;
	
	$max_size = 140;
	$min_size = 60;

	$min_tag_weight = $TagManager->MinTagWeight()?$TagManager->MinTagWeight():1;
	$max_tag_weight = $TagManager->MaxTagWeight()?$TagManager->MaxTagWeight():1;
	$min_tag_weight = $min_tag_weight>0?$min_tag_weight:1;
	$tag_size_step = $all_Tags?($max_tag_weight-($min_tag_weight>0?$min_tag_weight:1))/count($all_Tags):0;
	$tag_size_step = (($max_tag_weight-$min_tag_weight)>0)?($max_size-$min_size)/($max_tag_weight-$min_tag_weight):0;
	
	print '<style type="text/css">.cpt_tag_cloud{padding:10px;}</style><div style="text-align:center;" class="block_tag_cloud">';

	foreach ($all_Tags as $Tag ){
		
		$font_size = ($min_size+($Tag->weight-$min_tag_weight)*$tag_size_step);
		print '&nbsp; <a style="font-size:'.$font_size.'%;" href="'.xHtmlSetQuery('?ukey=search&tag='.urlencode($Tag->name)).'">'.xHtmlSpecialChars($Tag->name).'</a> &nbsp;';
	}
	
	print '</div>';
?>