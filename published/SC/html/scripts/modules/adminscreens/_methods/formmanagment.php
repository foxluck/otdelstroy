<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */

class FormManagment extends ActionsController
{
	function main()
	{
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */
		$print_forms = Forms::listModules();
		Forms::filterModules($print_forms,array('language'=>sc_getSessionData('LANGUAGE_ISO3')));
		$smarty->assign('print_forms',$print_forms);
		//$smarty->assign('print_forms',Forms::listModules());
		$smarty->assign('sub_template', DIR_TPLS.'/backend/printforms.html');
		return ;
		//DIR_FORMS));
		//DIR_USER_FORMS));
	}
	
	function display()
	{
		$form_id = $this->getData('form_id');
		$form_class = $this->getData('form_class');
		$print_form = Forms::getInstance($form_id,$form_class,true);
		/*@var $print_form Forms*/
		if(is_object($print_form)){
			$print_form->display(false);
		}else{
			print translate('print_form_not_found');exit;
		}
	}

	function preview()
	{
		$form_id = $this->getData('form_id');
		$form_class = $this->getData('form_class');
		$print_form = Forms::getInstance($form_id,$form_id?false:$form_class);
		/*@var $print_form Forms*/
		if(is_object($print_form)){
			$print_form->display(false);
		}else{
			print translate('print_form_not_found');exit;
		}
	}

	function configure()
	{
		$form_id = $this->getData('form_id');
		$print_form = Forms::getInstance($form_id);
		/*@var $print_form Forms*/
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */
		if(is_object($print_form)){
			$smarty->assign('settings', $print_form->getControls() );
			$smarty->assign('properties',$print_form->getProperties());
		}
		//		$smarty->assign('info',$print_form->getInfo());
		$smarty->assign('form_id',$form_id);
		$smarty->assign('sub_template', DIR_TPLS.'/backend/printforms_configure.html');
	}

	function save()
	{
		$form_id = $this->getData('form_id');
		$print_form = Forms::getInstance($form_id);
		/*@var $print_form Forms*/
		if(is_object($print_form)){
			$print_form->setup();
			$print_form->save();
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'form_id=&form_class=', 'msg_information_save');
	}

	function install()
	{
		$form_class = $this->getData('form_class');
		$print_form = Forms::getInstance(0,$form_class);
		$form_id = 0;
		/*@var $print_form Forms*/
		if(is_object($print_form)){
			$form_id = $print_form->save();
		}

		if($form_id){
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'action=configure&form_id='.$form_id.'&form_class=', 'msg_information_save');
		}else{
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'form_id=&form_class=', 'printforms_add_failed');
		}
	}

	function uninstall()
	{
		$form_id = $this->getData('form_id');
		$print_form = Forms::getInstance($form_id);
		/*@var $print_form Forms*/
		if(is_object($print_form)){
			$form_id = $print_form->uninstall();
		}
		if($form_id){
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'action=configure&form_id='.$form_id, 'printforms_uninstall_failed');			
		}else{
			
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'form_id=&form_class=', 'msg_information_save');
		}
	}

	private function getInstalledForms()
	{

	}

}

ActionsController::exec('FormManagment');
?>