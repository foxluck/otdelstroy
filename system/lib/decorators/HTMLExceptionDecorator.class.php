<?php 

class HTMLExceptionDecorator
{
	/**
	 * @var Exception
	 */
	protected $e;
	
	public function __construct(Exception $e)
	{
		$this->e = $e;
	}
	
	public function __toString()
	{
	    $back = _s('Back');
		$title = _s("Error");
		return <<<HTML
<div style="border: 1px solid #D8DADC; background-color: #F3F6F8;">
	<h1 style="padding-left: 10px; border-bottom: dashed 1px rgb(255, 255, 255); color: rgb(0, 0, 102); font-size: 110%;">{$title}</h1>
	<div style="padding: 0 10px 10px">
		<b>{$this->e->getMessage()}</b>
		<br />
		<a href="javascript:history.back()">{$back}</a>
	</div>
</div>
HTML;
	}
}

?>