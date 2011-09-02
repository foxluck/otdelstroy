<?php

	function addUploadedFile($file, $type='attachments') {
		$file['name'] = preg_replace("/[^a-z0-9а-я._\-]+/iu", '_', $file['name']);
		return @move_uploaded_file($file['tmp_name'], getPath().'/'.session_id().'_'.$type.'_'.$file['name']);
	}

	function getUploadedFilesSize() {
		$size = 0;
		$path = getPath();
		if ($handle = @opendir($path)) {
			while (false !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..' && stripos($filename, session_id()) !== false) {
					$size += filesize($path.'/'.$filename);
				}
			}
			closedir($handle);
		}
		return $size;
	}

	function getUploadedFilesList($type='attachments', $format_size=true) {
		$list = array();
		$i = 0;
		$path = getPath();
		if ($handle = @opendir($path)) {
			while (false !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..') {
					$fname = explode('_', $filename, 3);
					if ($fname[0] != session_id()) {
						continue;
					}
					if ($fname[1] != $type) {
						continue;
					}
					$list[$i]['name'] = !empty($fname[2]) ? $fname[2] : $fname[0];
					$list[$i]['size'] = $format_size ? formatFileSize(filesize($path.'/'.$filename)) : filesize($path.'/'.$filename);
					$list[$i]['type'] = get_mime_type($list[$i]['name']);
					$i++;
				}
			}
			closedir($handle);
		}
		return $list;
	}

	function getUploads($type='attachments') {
		$list = getUploadedFilesList($type, false);
		$uploads = array();
		foreach ($list as $file) {
			$uploads[$file['name']] = $file;
		}
		return $uploads;
	}

	function clearUploadedFiles() {
		$path = getPath();
		if ($handle = @opendir($path)) {
			while (false !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..' && stripos($filename, session_id()) !== false) {
					@unlink($path.'/'.$filename);
				}
			}
			closedir($handle);
		}
		return true;
	}

	function deleteUploadedFile($file_name, $type='attachments') {
		@unlink(getPath().'/'.session_id().'_'.$type.'_'.$file_name);
		return getUploadedfilesList($type);
	}

	function getUploadedFile($file_name, $type='attachments', $subpath='') {
		$path = $subpath.getPath();
		$filename = session_id().'_'.$type.'_'.$file_name;
		return array(
			'name' => $file_name,
			'size' => filesize($path.'/'.$filename),
			'type' => get_mime_type($file_name),
			'body' => file_get_contents($path.'/'.$filename)
		);
	}

	function getUploadedFileBody($file_name, $type='attachments') {
		$file = getUploadedFile($file_name, $type);
		return $file['body'];
	}

	function getUploadsInfo($type='attachments') {
		return getUploadedFilesList($type, false);
	}

	function reloadFile($file, $type='attachments') {
		if(is_array($file)) {
			@file_put_contents(getPath().'/'.session_id().'_'.$type.'_'.$file['name'], $file['body']);
		}
	}

	function formatFileSize($fileSize) {
		if(!strlen($fileSize))
			return null;

		global $kernelStrings;
		if(count($kernelStrings))
		{
			if ( !$fileSize )
				return sprintf( $kernelStrings['aa_kb_style'], "0.00");
			if ( $fileSize < 1024 )
				$fileSize = 1024;
			if ( $fileSize >= GIGABYTE_SIZE_RELATIVE )
				return sprintf( $kernelStrings['aa_gb_style'], round(ceil($fileSize)/GIGABYTE_SIZE_RELATIVE, 2) );
			elseif ( $fileSize >= MEGABYTE_SIZE )
				return sprintf( $kernelStrings['aa_mb_style'], round(ceil($fileSize)/MEGABYTE_SIZE, 2) );
			else
				return sprintf( $kernelStrings['aa_kb_style'], round(ceil($fileSize)/1024, 2) );
		} else {
			if($fileSize < 1024)
				return $fileSize.' b';
			else if($fileSize >= 1024 && $fileSize < 1024*1024)
				return sprintf('%01.2f', $fileSize/1024.0).' Kb';
			else
				return sprintf('%01.2f',$fileSize/(1024.0*1024)).' Mb';
		}
	}

	function getPath()
	{
		$path = '../../../temp/mail';
		if (!file_exists($path)) {
			@mkdir($path, 0775, true);
		}
		return $path;
	}

	function get_mime_type($filename)
	{
		preg_match('/\.(.*?)$/', $filename, $match);
		switch (strtolower($match[1])) {
			case 'jpg': case 'jpeg': case 'jpe': return 'image/jpg';
			case 'png': case 'gif': case 'bmp': case 'tiff' : return 'image/'.strtolower($m[1]);

			case 'doc': case 'docx': return 'application/msword';
			case 'xls': case 'xlt': case 'xlm': case 'xld': case 'xla': case 'xlc': case 'xlw': case 'xll': return 'application/vnd.ms-excel';
			case 'ppt': case 'pps': return 'application/vnd.ms-powerpoint';
			case 'rtf': return 'application/rtf';
			case 'txt': return 'text/plain';
			case 'pdf': return 'application/pdf';
			case 'html': case 'htm': case 'php': return 'text/html';
			case 'zip': return 'application/zip';
			case 'tar': return 'application/x-tar';
			case 'js': return 'application/x-javascript';
			case 'json': return 'application/json';
			case 'css': return 'text/css';
			case 'xml': return 'application/xml';
			case 'mpeg': case 'mpg': case 'mpe': return 'video/mpeg';
			case 'mp3': return 'audio/mpeg3';
			case 'wav': return 'audio/wav';
			case 'aiff': case 'aif': return 'audio/aiff';
			case 'avi': return 'video/msvideo';
			case 'wmv': return 'video/x-ms-wmv';
			case 'mov': return 'video/quicktime';
			case 'zip': return 'application/zip';
			case 'tar': return 'application/x-tar';
			case 'swf': return 'application/x-shockwave-flash';

			default: return 'application/octet-stream';
		}
	}

?>