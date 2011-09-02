<?php

/*
* WebAsyst Smarty Plugin
* -------------------------------------------------------------
* Type:     block
* Name:     wbs_wbsAdminPage
* Purpose:  Wrap the page content for wbsAdmin
* -------------------------------------------------------------
*/

function smarty_block_wbs_wbsHeader( $params, $content, &$smarty, &$repeat )
{
	$result = null;

	if (isset($content))
	{
		//extract($params);
		//list of accepted params
		//$systemConfiguration array('info'=>string,'link'=>string,'status'=>string)
		//$companyInfo array('name'=>string,'license'=>string)
		//$systemInfo array('version'=>string,'updateDate'=>string,'newVersion'=>string)

		//$kernelStrings = $smarty->get_template_vars('kernelStrings');
		//

		$systemConfiguration = $smarty->get_template_vars('systemConfiguration');
		$companyInfo = $smarty->get_template_vars('companyInfo');
		$systemConfiguration = $smarty->get_template_vars('systemConfiguration');
		$systemInfo = $smarty->get_template_vars('systemInfo');
		$waStrings = $smarty->get_template_vars('waStrings');
		//var_dump($waStrings);exit;
		$mainPageLink='wbsadmin.php';

		$result.='
<!--i-header-->';
		//
		//system info
		if(isset($systemConfiguration)&&is_array($systemConfiguration)){
			$result.='
<p class="i-aright i-grey status">'.$systemConfiguration['info'].' - <a href="'.$systemConfiguration['link'].'" ';
			if($systemConfiguration['status']){
				$result.='class="i-greencolor">'.$waStrings['upd_m_sys_req_ok'].'</a></p>';
			}else{
				$result.='class="i-redcolor">'.$waStrings['upd_m_sys_req_bad'].'</a></p>';
			}
			//var_dump($systemConfiguration);exit;
		}
		//
		//
		$result.='
	<div class="i-header">
		<div class="i-rel">
			<div class="i-absleft">';
		if(isset($companyInfo)&&is_array($companyInfo)){
			if(!strlen($companyInfo['LICENSE'])){
		$companyInfo['LICENSE']='<a href="commonsettings.php">Not registered</a>';
	}
			$result.='<h2>'.strip_tags($companyInfo['COMPANY']).'</h2>';
			$result.='<p class="i-small i-grey">'.$waStrings['upd_m_wa_license'].': '.$companyInfo['LICENSE'].'</p>';
		}
		$result.='
			</div>
			<div class="i-absright i-aright">';
		if(isset($systemInfo)&&is_array($systemInfo)){
			$result.='<p>'.$waStrings['upd_m_wa_ver'].'&nbsp;<b>'.$systemInfo['localVersion'].'</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
			$waStrings['upd_m_wa_date'].'&nbsp;'.$systemInfo['installDate'].'</p>';
			if((isset($systemInfo['webVersion'])||isset($systemInfo['downloadedVersion']))&&$systemInfo['updateAvailable']){
				$result.='<p><a href="'.$systemInfo['link'].'">'.$waStrings['upd_m_upd_gen'].'&nbsp;('.$waStrings['upd_m_upd_ver_web'].'&nbsp;'.($systemInfo['webVersion']?$systemInfo['webVersion']:$systemInfo['downloadedVersion']).($systemInfo['webVersionDate']?('&nbsp;'.$waStrings['upd_m_wa_upd_date'].'&nbsp;'.$systemInfo['webVersionDate']):'').')</a></p>';
			}elseif($systemInfo['webVersion']){
				$result.='<p class="i-greencolor">'.$waStrings['upd_m_upd_no'].'</p>';
			}elseif($systemInfo['updateAvailable']){
				$result.='<p><a href="'.$systemInfo['link'].'">'.$waStrings['upd_m_upd_gen'].'&nbsp;</a></p>';
			}
			/*else{
				$result.='<p class="i-greencolor">'.$waStrings['upd_m_upd_err'].'</p>';
			}*/
		}
		$result.='
			</div>
		</div><h1>
		'.($mainPageLink?('<a href="'.$mainPageLink.'">'):'').
		'<img src="../classic/images/logo.gif?r=150" width="238" height="65" alt="WebAsyst Installer" title="WebAsyst Installer" />'.
		($mainPageLink?('</a>'):'').'</h1>
	</div>
	<!--/i-header-->';
		//if(!isset($pageTitle))$pageTitle='';
		//$result.="<h1>{$pageTitle}</h1>";


		$result.=$content;
	}

	return $result;
}

?>