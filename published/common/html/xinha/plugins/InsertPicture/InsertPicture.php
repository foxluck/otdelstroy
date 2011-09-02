<?php
	session_start();
	require_once '../../../scripts/tmp_functions.php';

	$limitedext = array(".gif",".jpg",".png",".jpeg");
	$errorStr = '';

	$fileName = '';
	if(isset($_FILES['file']))
	{
		$file = $_FILES['file'];
		$size = getUploadedFilesSize();

		$ext = strrchr($file['name'], '.');
		if (!in_array($ext, $limitedext))
			$errorStr = "The file doesn't have the correct extension.";
		else if ($size + $file['size'] > $_size_limit)
			$errorStr = 'The file is to big. The max Filesize is</span><span class="errorStr"> '.formatSize($_size_limit).'.';
		else {
			$file['body'] = file_get_contents($file['tmp_name']);
			addUploadedFile($file, 'img');
		}
		$fileName = $file['name'];
	}
	elseif(isset($_REQUEST['fileName'])) $fileName = $_REQUEST['fileName'];

	if(isset($_REQUEST['url'])) $url = $_REQUEST['url'];
	else $url = '';

	function formatSize($size)
	{
		if($size < 1024)
			return $size.' bytes';
		else if($size >= 1024 && $size < 1024*1024)
			return sprintf('%01.2f',$size/1024.0).' Kb';
		else
			return sprintf('%01.2f',$size/(1024.0*1024)).' Mb';
	}
?>
<html>
<head>
	<title>Insert Image</title>
<link rel="stylesheet" type="text/css" href="../../popups/popup.css">
<script type="text/javascript" src="../../popups/popup.js"></script>

<script type="text/javascript">
	window.resizeTo(620, 525);
	var Xinha = window.opener.Xinha;
	function i18n(str) {
		return (Xinha._lc(str, 'Xinha'));
	}

	function Init() {
		__dlg_translate('InsertPicture');
		__dlg_init();

		// Make sure the translated string appears in the drop down. (for gecko)
		document.getElementById('f_align').selectedIndex = 0;
		document.getElementById('f_align').selectedIndex = document.getElementById('f_align').selectedIndex;
		var param = window.dialogArguments;

		if(param) {
			document.getElementById('f_url').value   = param['f_url'];
			document.getElementById('f_alt').value   = (typeof(param['f_alt'])!='undefined') ? param['f_alt'] : '';
			document.getElementById('f_border').value= (typeof(param['f_border'])!='undefined') ? param['f_border'] : '';
			document.getElementById('f_align').value = (typeof(param['f_align'])!='undefined') ? param['f_align'] : '';
			document.getElementById('f_vert').value  = (typeof(param['f_vert'])!='undefined') ? param['f_vert'] : '';
			document.getElementById('f_horiz').value = (typeof(param['f_horiz'])!='undefined') ? param['f_horiz'] : '';
			document.getElementById('f_height').value= (typeof(param['f_height'])!='undefined') ? param['f_height'] : '';
			document.getElementById('f_width').value = (typeof(param['f_width'])!='undefined') ? param['f_width'] : '';
		}
		<?php	if(!$errorStr && ($fileName || $url)) echo "onPreview('$fileName', '$url');" ?>
	}

	function onOK() {
		fileName = document.getElementById('f_name');
		url = document.getElementById('f_url');
		if(!fileName.value && !url.value)
		{
			alert(i18n("You must uload the file or enter the URL"));
			return false;
		}
		if(fileName.value)
			url.value = '<?php echo PAGE_PREVIEW.'?file=' ?>' + fileName.value;

		// pass data back to the calling window
		var fields = ['f_url', 'f_alt', 'f_align', 'f_border', 'f_horiz', 'f_vert', 'f_width', 'f_height'];
		var param = new Object();
		for (var i in fields) {
			var id = fields[i];
			var el = document.getElementById(id);
			param[id] = el.value;
		}
		__dlg_close(param);
		return false;
	}

	function onUpload() {
		var required = {
			'file': i18n('Please select a file to upload.')
		};
		for (var i in required) {
			var el = document.getElementById(i);
			if (!el.value) {
				alert(required[i]);
				el.focus();
				return false;
			}
		}
		document.uploadFileForm.submit();
		return true;
	}

	function onCancel() {
		__dlg_close(null);
		return false;
	}

	function onPreview(fileName, url) {
		if(fileName)
		{
			url = '<?php echo PAGE_PREVIEW.'?file=' ?>' + fileName;
			document.getElementById('f_url').value = '';
			document.getElementById('f_name').value = fileName;
		} else {
			selectRow(false);
			document.getElementById('f_name').value = '';
		}
		if(!url)
		{
			alert(i18n('You must enter the URL'));
			f_url.focus();
			return false;
		}
		if(document.all) {
			window.ipreview.location.replace('viewpicture.html?'+url);
		} else {
			window.ipreview.location.replace(url);
		}
		img.src = url;
		img.onLoad = imgWait();
		return false;
	}

	var img = new Image();
	function imgWait() {
		waiting = window.setInterval('imgIsLoaded()', 1000)
	}
	function imgIsLoaded() {
		if(img.width > 0) {
			window.clearInterval(waiting)
			document.getElementById('f_width').value = img.width;
			document.getElementById('f_height').value = img.height;
		}
	}

	function selectRow(obj) {
		var list = document.getElementById('filesList').getElementsByTagName('span');
		for (var i=0; i<list.length; i++)
			list[i].style.fontWeight = 'normal';
		if(obj)
			obj.style.fontWeight = 'bold';
	}

	function openFile() {
		window.open(document.getElementById('f_url').value,'','');
	}
</script>

<style>
	div.fileInputContainer {
		position: relative;
		overflow:hidden;
		height: 1.5em;
	}
	div.visibleFileInput {
		position: absolute;
		top: 0px;
		left: 0px;
		z-index: 1;
		text-decoration:underline;
		font-weight:bold;
		color:#1352D2;
		cursor:pointer !important;
	}
	input.file {
		position: relative;
		right: 11em;
		text-align: right;
		font-size:2em;
		-moz-opacity:0 ;
		filter:alpha(opacity: 0);
		opacity: 0;
	 	z-index: 2;
	}
	.errorStr {color: indianred; font-weight: bold}
	.link {cursor: pointer}
</style>
</head>

<body class="dialog" onload="Init()">
<div class="title">Insert Image</div>
<table border="0" width="100%" style="padding: 0px; margin: 0px">
	<tr>
		<td style="vertical-align: top">
			<span>Uploaded images:</span>
			<div id="filesList" style="width: 20em; height: 10em; border: 1px solid gray; background: white; padding: 2px 5px">
<?php
	$list = getUploadedFilesList('img');
	foreach($list as $entry) {
		if ($entry['name'] == $fileName) $style = 'style="font-weight: bold"';
		else $style = '';
		echo "<span class=\"link\" $style onClick=\"onPreview('{$entry['name']}', false); selectRow(this)\">{$entry['name']} ({$entry['size']})</span><br>";
	}
?>
			</div>

			<div style="width: 20em">
			<form method="post" action="" enctype="multipart/form-data" name="uploadFileForm">
				<div class="fileInputContainer">
					<input type="file" class="file" name="file" id="file" onChange="onUpload()">
					<div class="visibleFileInput">Upload file</div>
				</div>
				<input type="hidden" name="fileName" id="f_name" value="">
				<span class="errorStr"><?php echo $errorStr ?></span>
			</form>
			</div>

		</td>
		<td style="text-align: center; vertical-align: middle" width="100%">
		<span>Image Preview:</span><br>
		<iframe name="ipreview" id="ipreview" frameborder="0" style="border: 1px solid gray; background: white"
			height="200" width="200" src=""></iframe>
		</td>
	</tr>
</table>

<form action="" method="get">

<table border="0" width="100%" style="padding: 0px; margin: 0px">
	<tr>
		<td nowrap>Image URL:</td>
		<td width="100%" nowrap><input type="text" name="url" id="f_url" style="width:75%"
			title="Enter the image URL here"	value="">
			<button name="preview" onclick="return onPreview(false, document.getElementById('f_url').value)"
			title="Preview the image in a new window">Preview</button>
		</td>
	</tr>
	<tr>
		<td nowrap>Alternate text:</td>
		<td><input type="text" name="alt" id="f_alt" size="30"
			title="For browsers that don't support images"></td>
	</tr>

</table>

<p />

<fieldset style="float: left; margin-left: 5px"><legend>Size</legend>
	<table>
		<tr>
			<td>Width:</td>
			<td><input type="text" name="width" id="f_width" size="5" title="Leave empty for not defined"></td>
		</tr>
		<tr>
			<td>Height:</td>
			<td><input type="text" name="height" id="f_height" size="5" title="Leave empty for not defined"></td>
		</tr>
	</table>
</fieldset>

<fieldset style="float:left; margin-left: 5px"><legend>Spacing</legend>
	<table>
		<tr>
			<td>Horizontal:</td>
			<td><input type="text" name="horiz" id="f_horiz" size="5" title="Horizontal padding"></td>
		</tr>
		<tr>
			<td>Vertical:</td>
			<td><input type="text" name="vert" id="f_vert" size="5" title="Vertical padding"><td>
		</tr>
	</table>
</fieldset>

<fieldset style="float: left; margin-left: 5px;"><legend>Layout</legend>
	<table>
		<tr>
			<td>Alignment:</td>
			<td><img src="../../images/space.gif" width="100%" height="2" border=0><select
				size="1" name="align" id="f_align" title="Positioning of this image">
				<option value=""         >Not set</option>
				<option value="left"     >Left</option>
				<option value="right"    >Right</option>
				<option value="texttop"  >Texttop</option>
				<option value="absmiddle">Absmiddle</option>
				<option value="baseline" >Baseline</option>
				<option value="absbottom">Absbottom</option>
				<option value="bottom"   >Bottom</option>
				<option value="middle"   >Middle</option>
				<option value="top"      >Top</option>
			</select></td>
		</tr>
		<tr>
			<td nowrap>Border thickness:</td>
			<td><input type="text" name="border" id="f_border" size="5" title="Leave empty for no border"></td>
		</tr>
	</table>
</fieldset>

<br clear="all">
<div class="space"></div>

<div id="buttons">
	<button type="submit" name="ok" onclick="return onOK();">OK</button>
	<button type="button" name="cancel" onclick="return onCancel();">Cancel</button>
</div>

</form>
</body>
</html>