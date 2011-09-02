/* This file is part of Xinha version 0.95 (WebAsyst edition) released Mon, 12 May 2008 17:33:15 +0200 */
/* The URL of the most recent version of this file is http://svn.xinha.webfactional.com/trunk/examples/XinhaConfig.js */
xinha_editors=null;
xinha_config=null;
xinha_plugins=null;
initEditor=null;
initEditor=initEditor?initEditor:function(){
xinha_editors=xinha_editors?xinha_editors:[_editor_textarea];
xinha_plugins=xinha_plugins?xinha_plugins:['UploadImage','InsertVariable']; // 'ClientsideSpellcheck','SpellChecker'];
if(!Xinha.loadPlugins(xinha_plugins,initEditor)){return}
xinha_config=xinha_config?xinha_config():new Xinha.Config();
xinha_config.toolbar=[
['separator','formatblock','fontname','fontsize','bold','italic','underline'],
['separator','forecolor','hilitecolor'],
['linebreak','separator','justifyleft','justifycenter','justifyright','justifyfull'],
['separator','insertorderedlist','insertunorderedlist','outdent','indent'],
['separator','createlink','uploadimage','insertvariable'],
['linebreak','separator','undo','redo'],
['separator','removeformat','htmlmode'],
];
xinha_config.pageStyleSheets=[_editor_url+'inside.css'];
xinha_config.colorPickerCellSize='14px';
xinha_config.colorPickerGranularity=6;
xinha_config.statusBar=false;
xinha_config.stripBaseHref=false;
xinha_editors=Xinha.makeEditors(xinha_editors,xinha_config,xinha_plugins);
Xinha.startEditors(xinha_editors);
editorWait();
};
function editorWait() {waiting = window.setInterval('editorReady()', 500)};
function editorReady()
{
	if(xinha_editors[_editor_textarea]._doc != null)
	{
		try{window.clearInterval(waiting)} catch(e){};
		fillEditorContent();
	}
}
Xinha.addOnloadHandler(initEditor);