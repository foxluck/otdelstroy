<?php
class Poll extends ComponentModule  {
	function initInterfaces(){
		$this->__registerInterface('bpoll', 'Poll administration', INTCALLER, 'methodBPoll');
		$this->__registerComponent('survey', 'cpt_lbl_survey', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE), 'methodFShowPoll');
	}

	function methodBPoll(){

		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */

		if (isset($_GET["save_successful"])){ //show successful save confirmation message
			$smarty->assign("configuration_saved", 1);
		}

		if (isset($_POST["save_voting"]) && isset($_POST["question"]) && isset($_POST["answers"])){ // save new survey

			safeMode(true);

			checkPath(DIR_SURVEY);

			$f = fopen(DIR_SURVEY."/survey.inc.php","w");
			fputs($f,"<?php\n");
			//record question and answer options
			fputs($f,"\t\$survey_question = '".self::__cleanOutput($_POST["question"])."';\n\n");
			fputs($f,"\t\$survey_answers = array();\n");
			$answers = explode("\n",$_POST["answers"]);
			for ($i=0; $i<count($answers); $i++)
			fputs($f,"\t\$survey_answers[] = '".self::__cleanOutput($answers[$i])."';\n");

			//reset results to 0
			fputs($f,"\n\t\$survey_results = array();\n");
			for ($i=0; $i<count($answers); $i++)
			fputs($f,"\t\$survey_results[] = 0;\n");

			fputs($f,"?>");
			fclose($f);

			RedirectSQ('start_new_poll=&save_successful=yes');
		}

		if (isset($_GET['start_new_poll'])) //show new customer survey form
		{
			$smarty->assign('start_new_poll', 'yes');
		}
		elseif(file_exists(DIR_SURVEY.'/survey.inc.php')) //show existing survey results
		{
			include(DIR_SURVEY.'/survey.inc.php');
			$smarty->hassign('survey_question', $survey_question );
			$smarty->hassign('survey_answers', $survey_answers);
			$smarty->hassign('survey_results', $survey_results);
			//get total voters count
			$voters_count = 0;
			for ($i=0; $i<count($survey_results); $i++)	$voters_count += $survey_results[$i];
			$smarty->assign('voters_count', $voters_count);
		}

		set_query('safemode=&save_successful=&start_new_poll=','',true);
		//set sub-department template
		$smarty->assign('admin_sub_dpt', 'modules_survey.tpl.html');
	}

	function methodFShowPoll(){
		if(!file_exists(DIR_SURVEY.'/survey.inc.php'))return;
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
		include(DIR_SURVEY."/survey.inc.php");
		if(isset($_GET["save_voting_results"])){ //save survey results
			
			checkPath(DIR_SURVEY);
			set_query('save_voting_results=','',true);
			if(isset($_GET["answer"]) && isset($_SESSION["vote_completed"][0]) && (!$_SESSION["vote_completed"][0])){
				$f = fopen(DIR_SURVEY."/survey.inc.php","w");
				fputs($f,"<?php\n");
				//record question and answer options
				fputs($f,"\t\$survey_question = '".self::__cleanOutput($survey_question)."';\n\n");
				fputs($f,"\t\$survey_answers = array();\n");
				for ($i=0; $i<count($survey_answers); $i++)
				fputs($f,"\t\$survey_answers[] = '".self::__cleanOutput($survey_answers[$i])."';\n");

				//increase voters count for current option
				$answer = intval($_GET["answer"]);
				if(isset($survey_results[$answer])){
					$survey_results[$answer]++;
				}
				//reset results to 0
				fputs($f,"\n\t\$survey_results = array();\n");
				for ($i=0; $i<count($survey_answers); $i++)
				fputs($f,"\t\$survey_results[] = \"$survey_results[$i]\";\n");

				fputs($f,"?>");
				fclose($f);
				
				//don't allow user to vote more than 1 time
				$_SESSION["vote_completed"][0] = 1;
			}
		}
		if(!isset($_SESSION["vote_completed"][0])){
			$_SESSION["vote_completed"][0] = 0;
		}elseif($_SESSION["vote_completed"][0]){
			$smarty->assign("show_survey_results", 1);
		}

		//assign surbey info to Smarty
		$smarty->assign("survey_question", $survey_question);
		$smarty->assign("survey_answers", $survey_answers);
		$smarty->assign("survey_results", $survey_results);
		//get total voters count
		$voters_count = 0;
		for ($i=0; $i<count($survey_results); $i++)	$voters_count += $survey_results[$i];
		$smarty->assign("voters_count", $voters_count);
		$smarty->display('customer_survey.tpl.html');
	}

	static function __cleanOutput($string){
		return str_replace(array('\\',"'","\r","\n"),array('\\\\',"\'",'',''),$string);
	}
}
?>