<?php 

class LimitException extends Exception
{

    protected $back = false;
    
    public function setBack($back)
    {
        $this->back = $back;    
    }
    
	public function __toString()
	{
		$html = '<div style="font-family:Trebuchet MS; font-size: 14px; color:red; padding-top: 10px; overflow: hidden"><b>'._s("ACCOUNT LIMIT")."</b>: ".$this->getMessage();
		if (Wbs::isHosted() && User::hasAccess('AA')) {
			$html .= '<br /><br /><a target="_top" href="'.Url::get('/index.php').'?url='.Url::get('/AA/html/scripts/change_plan.php').'">'._s("Upgrade account").'</a>';
		} else {
			$html .= '<br /><br />'._s("Please refer to your account administrator.");
		}
		if ($this->back) {
		    $html .= '<br /><br /><input type="button" onclick="javascript:history.go(-1)" value="'._("Go back").'" />';
		}
		$html .= "</div>";
		return $html;		
	}
}

?>