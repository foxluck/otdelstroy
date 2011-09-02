<?php 
	header ("Content-type: text/xml");
	print '<?xml version="1.0" encoding="UTF-8"?>'; 
?>
<Module>
<ModulePrefs description="Webasyst online folder description text." screenshot="http://img.yandex.net/i/yandex-big.png" title_url="http://www.webasyst.net" author="WebAsyst"  height='350' title="Webasyst Online Folder" />
<Content type="url" href='<? print html_entity_decode(base64_decode(rawurldecode($_GET["code"]))); ?>'></Content>
</Module>