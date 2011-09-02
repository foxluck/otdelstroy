<?php
function smarty_function_html_xhina($params, &$smarty)
{
	
	return @"
<textarea id='".$params['name']."' name='".$params['name']."' rows='".$params['rows']."' cols='50' style='".$params['style']."'>".$params['value']."</textarea>
  <script type='text/javascript'>
    _editor_url  = 'modules/xhina/'  // (preferably absolute) URL (including trailing slash) where Xinha is installed
    _editor_lang = 'en';      // And the language we need to use in the editor.
  </script>
  <script type='text/javascript' src='modules/xhina/htmlarea.js'></script>
  <script type='text/javascript'>
    xinha_editors = null;
    xinha_init    = null;
    xinha_config  = null;
    xinha_plugins = null;

    // This contains the names of textareas we will make into Xinha editors
    xinha_init = xinha_init ? xinha_init : function()
    {

      xinha_plugins = xinha_plugins ? xinha_plugins :
      [
       'ContextMenu',
       'FullScreen',
       'ListType',
       'TableOperations',
       'ImageManager'
      ];
             if(!HTMLArea.loadPlugins(xinha_plugins, xinha_init)) return;

      xinha_editors = xinha_editors ? xinha_editors :
      [
        '".$params['name']."'
      ];

       xinha_config = xinha_config ? xinha_config() : new HTMLArea.Config();

       xinha_editors   = HTMLArea.makeEditors(xinha_editors, xinha_config, xinha_plugins);

      HTMLArea.startEditors(xinha_editors);
    }

    xinha_init();
    </script>
     ";
}
?>