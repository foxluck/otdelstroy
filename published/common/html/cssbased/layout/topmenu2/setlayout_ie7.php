<?php

	header("Content-type: text/css; charset: charset=iso-8859-1");
	header("Cache-Control: must-revalidate");

	$Toolbar = isset( $_GET['toolbar'] ) && $_GET['toolbar'];
	$DirectAccess = isset( $_GET['da'] ) && $_GET['da'];
	$SplitterMode = isset( $_GET['splitter'] ) && $_GET['splitter'];
	$noScroll = isset( $_GET['ns'] ) && $_GET['ns'];

	if ( $Toolbar ) {
?>
	#Toolbar
	{
		display: block;
	}

	#ContentWrapper
	{
		bottom: 46px;
	}

<?php }

	if ( $DirectAccess ) {
?>

	#Header, #HeaderContainer, #UserName, #MainMenu, #Logout, #FooterContainer
	{
		display: none;
	}

	#ContentWrapper
	{
		left: 0;
		bottom: 0;
	}

	#PageTitlePanel
	{
		top: 0px;
	}

	#PageTitle
	{
		margin-left: 0;
	}

	<? if ( $Toolbar ) { ?>

		#ContentWrapper
		{
			left: 0;
			bottom: 26px;
		}

	<?php } ?>

<?php } ?>

<?php
	if ( $SplitterMode ) {
?>
	#ContentScroller
	{
		overflow: hidden;
	}

	#Content
	{
		padding: 0;
		height: 100%;
	}

	<? if ( $Toolbar ) { ?>
		
	<?php } ?>

<?php }

	if ( $noScroll ) {
?>
	#ContentScroller
	{
		overflow: hidden;
	}
<?php } ?>