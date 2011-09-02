<?php
/**
 * Draft code
 * @author WebAsyst Team
 *
 */
class MValidator
{
	private $storage;
	private $expire = 3600;
	function __construct($expire = 3600)
	{
		$this->storage = Cache::getInstance(__CLASS__,Cache::SESSION);
		$this->expire = max(600,$expire);
	}

	function generate($name = null, $length = 6)
	{
		$code = '';
		mt_srand(microtime(true)*1000000);
		for($i = 0;$i<$length;$i++) {
			$code .= sprintf('%X',mt_rand(0,16));
		}

		if(!$name) {
			$name = 'code';
		}
		$this->storage->set($name, $code, $this->expire);
		return $code;
	}

	function sendCode($email, $name = null, $extra_template_vars = array(), $length = 6,$template = null)
	{
		$template_vars = array();
		$code = $this->getCode($name);

		$template_vars['code'] = $code?$code:$this->generate($name, $length);
		$template_vars['extra'] = $extra_template_vars;
		$template_vars['expire'] = $this->expire;
		$subject = translate('access_code');
		return xMailTxt($email,$subject, $template?$template:'access_code.txt',	$template_vars,true);
	}

	function sended($name = null)
	{
		return $this->getCode($name)?true:false;
	}
	
	function getCode($name = null)
	{
		if(!$name) {
			$name = 'code';
		}
		return $this->storage->get($name);
	}

	function check($code, $name = null)
	{
		if(!$name) {
			$name = 'code';
		}
		$result = ($code && ($this->storage->get($name) == $code))?true:false;
		if($result) {
			$this->storage->reset($name);	
		}
		return $result;
	}
}
//EOF