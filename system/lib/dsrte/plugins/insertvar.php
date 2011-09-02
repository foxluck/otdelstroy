<?php
/**
 * Damn Small Rich Text Editor v0.2.3 for jQuery
 * by Roi Avidan <roi@avidansoft.com>
 * Demo: http://www.avidansoft.com/dsrte/
 * Released under the GPL License
 *
 * Insert Variable Command class.
 */

class dsRTEInsertVarPlugin extends dsRTETextButton
{


    private static $scripted = false;
    
    /**
     * Prepare the Insert Variable command's special hidden div with a Target and URL fields.
     */
    public function getPanelHTML()
    {
    	$fields = ContactType::getAllFields(User::getLang(), ContactType::TYPE_FIELD);
        $html = '<div class="rte panel" id="'.$this->id.'-'.$this->arguments.'">';
        $html .= '<span style="cursor: pointer; position:absolute; right: 0; top: 0" onclick="$(\'#'.$this->id.'-'.$this->arguments.'\').slideUp()" /><img width="16" height="16" src="'.Url::get('/common/templates/img/close.gif').'" /></span>';
        $html .= '<span style="padding-right: 10px; font-size:80%">'._s("To add a variable to your message, select it from the list and click it. When the message is sent, variables will be substituted with their real values.").'</span>';
        $html .= '<div id="'.$this->id.'-'.$this->arguments.'-content" class="insertvar-content">';
        
        $userParamsBlock = '<a href="javascript:void(0)" class="insertvar"><span>{NAME}</span> '._s('Full name').'</a>';;
        $myParamsBlock = '<a href="javascript:void(0)" class="insertvar"><span>{MY_NAME}</span> '._s('Full name').'</a>';
    	foreach ($fields as $key=>$field){
    		if ($field['type'] == 'IMAGE') continue;
    		$fieldDbName = substr($field['dbname'], 2);
    		$userParamsBlock .= '<a href="javascript:void(0)" class="insertvar"><span>{'.$fieldDbName.'}</span> '.$field['name'].'</a>';
    		$myParamsBlock .='<a href="javascript:void(0)" class="insertvar"><span>{MY_'.$fieldDbName.'}</span> '.$field['name'].'</a>';
    	}
    	
    	$fields = Company::getFields();
    	$companyBlock = "";
    	foreach ($fields as $key => $info) {
    		$companyBlock .= '<a href="javascript:void(0)" class="insertvar"><span>{'.$key.'}</span> '.$info['name'].'</a>';
    	}
    	
        $html .= '<h5 class="insertvar">'._s("Recipient's parameters").'</h5>';
        $html .= $userParamsBlock;
        $html .= '<h5 class="insertvar">'._s('My personal parameters').'</h5>';
        $html .= $myParamsBlock;
        $html .= '<h5 class="insertvar">'._s("Company parameters").'</h5>';
        $html .= $companyBlock;
        $html .= '<h5 class="insertvar">'._s('Subscription management').'</h5>';
        $html .= '<a href="javascript:void(0)" class="insertvar"><span>{MANAGE_SUBSCRIPTION_URL}</span> '._s("Subscriber's personal page").'</a>';
    	$html .= '<a href="javascript:void(0)" class="insertvar"><span>{UNSUBSCRIBE}</span> '._s('Instant unsubscribing').'</a>';
	    $html .= '</div>';	    
        $html .= '</div>';

        return $html;
    }

    /**
     * This plugin requires additional JavaScript files to operate.
     * Return them for inclusion.
     */
    public function getScripts()
    {
        if ( self::$scripted )
            return '';
            
        self::$scripted = true;
        return implode( "\n", array(
            '<script type="text/javascript" src="../common/html/res/dsrte/plugins/insertvar.js"></script>',
        ) );
    }
	
}
?>