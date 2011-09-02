<?php
	function dr($Data, $Print = true, $HTML = true){
		
		static $DCounter;
		if(!isset($DCounter))$DCounter=1;
		
		ob_start();
		print_r($Data);
		$Dump = (ob_get_contents());
		ob_end_clean();
		
		if($HTML){
			$Output = '<pre><span onclick="var t = document.getElementById(\'ch_\'+this.id);t.style.display = t.style.display==\'block\'?\'none\':\'block\';" id="'.$DCounter.'" style="font-weight:bold; color:blue;">
			'."\n".$DCounter."\n".'</span><div id="ch_'.$DCounter.'" style="display:none;padding-left:30px;">'.$Dump.'</div></pre>';
		}else 
			$Output = $Dump;
		if($Print){
			print $Output;
		}
		$DCounter++;
		return $Output;
	}
	
	function dump2str($Data){
		
		return dr($Data,false,false);
	}
	
	function dump2pe($Data){
		
		PEAR::raiseError(dump2str($Data));
	}
	
	function dump2file($Data, $file){
		
		$fp = fopen($file, 'w+');
		fwrite($fp, dump2str($Data)."\n\n\n");
		fclose($fp);
	}
?>