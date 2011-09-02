<?php
// customer survey processing
if(!file_exists(DIR_SURVEY.'/survey.inc.php'))return;
include(DIR_SURVEY."/survey.inc.php");

if(isset($_GET["save_voting_results"])){ //save survey results

	checkPath(DIR_SURVEY);
	set_query('save_voting_results=','',true);
	$f = fopen(DIR_SURVEY."/survey.inc.php","w");
	fputs($f,"<?php\n");
	//record question and answer options
	fputs($f,"\t\$survey_question = '".str_replace(array('\\',"'"),array('\\\\',"\'"),$survey_question)."';\n\n");
	fputs($f,"\t\$survey_answers = array();\n");
	for ($i=0; $i<count($survey_answers); $i++)
		fputs($f,"\t\$survey_answers[] = '".str_replace(array('\\',"'"),array('\\\\',"\'"),$survey_answers[$i])."';\n");

	//increase voters count for current option
	if ((!isset($_SESSION["vote_completed"][0]) || $_SESSION["vote_completed"][0] != 1)
		&& isset($_GET["answer"]) && isset($survey_results[$_GET["answer"]]))
		$survey_results[$_GET["answer"]]++;

	//reset results to 0
	fputs($f,"\n\t\$survey_results = array();\n");
	for ($i=0; $i<count($survey_answers); $i++)
		fputs($f,"\t\$survey_results[] = \"$survey_results[$i]\";\n");

	fputs($f,"?>");
	fclose($f);

	//don't allow user to vote more than 1 time
	$_SESSION["vote_completed"][0] = 1;

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
?>