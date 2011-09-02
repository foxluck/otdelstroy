<?php

	header("Content-type: text/css; charset: charset=utf-8");
	header("Cache-Control: must-revalidate");

	$Toolbar = isset( $_GET['toolbar'] ) && $_GET['toolbar'];
	$FullScreen = isset( $_GET['fullscreen'] ) && $_GET['fullscreen'];
	$DirectAccess = isset( $_GET['da'] ) && $_GET['da'];
	$SplitterMode = isset( $_GET['splitter'] ) && $_GET['splitter'];
	$noScroll = isset( $_GET['ns'] ) && $_GET['ns'];
	
	$Inplace = isset( $_GET['inplace'] ) && $_GET['inplace'];

	if ( $Toolbar ) {
?>
	#Toolbar
	{
		display: block;
	}

	#TContentWrapper
	{
		padding-bottom: 26px;
	}

	* html
	{
		padding: 0 0 116px 0;
	}
<?php }

	if ( $DirectAccess ) {
?>

	#Header, #HeaderContainer, #UserName, #MainMenu, #Logout, #FooterContainer, #LoginBlock
	{
		display: none;
	}

	#TContentWrapper
	{
		left: 0;
		top: 0px;
		bottom: 0;
	}

	.TrialAlertBlock
	{
		display: none;
	}

	* html #TContentWrapper
	{
		position: fixed;
		height: 100%;

		padding: 0;
		margin: 0px 0 0 0;
	}

	* html
	{
		padding: 0 0 13px 0;
	}

	* html #Content
	{
		padding-right: 25px;
	}

	* html #ContentScroller
	{
		overflow-y: scroll;
	}

	#PageTitlePanel
	{
		top: 0px;
		left: 0;
	}

	* html #PageTitlePanel
	{
		left: 0;
		width: expression((document.body.clientWidth)+'px');
	}

	#PageTitle
	{
		margin-left: 0;
	}

	* html #SplitterHeader
	{
		left: 0;
		right: 0;
		width: 160%;
	}

	<?php if ( $Toolbar ) { ?>

	<?php } ?>

<?php } ?>

<?php
	if ( $SplitterMode ) {
?>
	#ContentScroller
	{
		overflow: hidden!important;
		overflow-y: hidden!important;
	}

	* html #ContentScroller
	{
		overflow-y: hidden;
		overflow: hidden;
	}

	#Content
	{
		padding: 0;
		height: 100%;
	}

	<?php if ( $Toolbar ) { ?>

	<?php } ?>

<?php }

	if ( $noScroll ) {
?>
	#ContentScroller
	{
		overflow: hidden!important;
		overflow-y: hidden!important;
	}

	* html #ContentScroller
	{
		overflow-y: hidden;
	}
<?php } ?>

<?php if ( $FullScreen || $Inplace) { ?>
	
	#HeaderContainer {height: 0px}
	#TContentWrapper {top: 10px; left: 10px}
	#FooterContainer {display: none; height: 2px}
	#LoginBlock {display: none}
	#Header {display: none}
	
	/*#TContentWrapper {padding: 0px; margin: 0px; bottom: 30px; border: 3px solid red}
	#Wrap {padding: 0px; margin: 0px; bottom: 0}*/

<?php } ?>
	
<?php if ( $Inplace ) { ?>
	
	/*#SubToolbar {height: 0px; display: none}*/
	.FullScreenBlock {display: none}

<?php } ?>