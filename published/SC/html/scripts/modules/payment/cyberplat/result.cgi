#! C:/perl/bin/perl -w

##########################################################################################
#	Script for unapplette technology
##########################################################################################

##########################################################################################
#	Defining the environment
##########################################################################################
use strict;

##########################################################################################
#	Loading standard libraries
##########################################################################################

use IPC::Open2;						# Pipes (signing and encrypting)
use CGI		':cgi';					# CGI
use Fcntl	':flock';				# Exclusive files access.
use CGI::Carp qw (fatalsToBrowser);

##########################################################################################
#	Loading module internal libraries
##########################################################################################

use Paths;						# Paths to files
use Data;						# Variables

use Util;						# Additional functions
use Log;						# Module for interaction with the log-file

##########################################################################################
#	Initialization
##########################################################################################

use vars qw(%in $Answer $Status $TransAmount $Amount $Curr $TransCurrency $AuthCode $TransDate $TransID
	    $Description $CustomerName $CustomerTitle $CustomerMessg);
use vars qw ($SignedReply $Reply $SystemError);


##########################################################################################
#	Start work
##########################################################################################
my %z;

# get input and check server signature
$OrderID 	= param ('orderid');
$SessionID 	= param ('sid');
$Language	= param ('Language')	|| 'ru';
$SignedReply	= param ('reply');
($Language eq 'ru') 	?	(   %ERRORS	= %ERRORS_RUS ) :  ( %ERRORS 	= %ERRORS_ENG );

my $LogFileName = $BasePath.'/cybercrd.log';

&OpenLog($LogFileName, $LogLevel);

&WriteLog("OID:$OrderID, Received server response", 2);

&WriteLog ("OID:$OrderID, Server reponse: $SignedReply", 1);

#check and if OK remove the server signature, else error message is returned
# Under Win32 we use corresponding OLE automation server instead of checker.exe
$Reply = $^O =~ /MSWin32/
	? eval { require Checker; return Checker::verify( $SignedReply ); }
	: &Sign( $SignedReply );
&WriteLog("OID:$OrderID Server signature is checked, reply=$Reply", 1);

#in case error occurred checking signature
if ($Reply   =~/SessionStatus=/i)
{
	$TemplatesPath .= '/';
	&WriteLog ("Error checking server signature: $Reply", 3);
	&AddError('E008');
}

#parse the server response and save all parameters to a hash
if (&WeAreOK()) {
    &ParseResults ( $Reply );
}

if (!isValidID($TerminalID))
{
	$ManyTerminals	=	0;
}

if ( $ManyTerminals && $ManyTemplates )
{
	my $TP = $TemplatesPath . "_".$TerminalID;
	$TemplatesPath = $TP if -e $TP;
}
$TemplatesPath .= '/';
if ( $ManyTerminals && $ManyChecks )
{
	$SessionsPath .= "_".$TerminalID;
}
$SessionsPath .= '/';
&WriteLog ("TerminalID=$TerminalID, TemplatesPath=$TemplatesPath, SessionsPath=$SessionsPath", 1);

&WeAreOK();

if ($SaveSession) {
	&SaveSessionStatus;
}

if ($SaveChecks) {
	&SaveCheck($SignedReply);
}

if ($WillConvert) {
	&SaveSessionConvertor;
}
if ($Answer =~ /false/i) {
	CloseLog();
	exit;
}

if ($Status == 0)
{
	&ShowSuccess();
} else {
	&ShowFailure();
}

&WriteLog("OID:$OrderID, Log closed", 2);

#Close log
CloseLog();
exit;

##########################################################################################
#	End of work
##########################################################################################



##########################################################################################
#	subprograms
##########################################################################################


#Save current session status in file
sub SaveSessionStatus()
{
	unless (-e $SessionsPath) {
		mkdir $SessionsPath, 0755 or &AddError ('E009', "Can't create $SessionsPath directory: $!");
	}
	#Forming session status string
	my $String = "SessionID=$SessionID&OrderID=$OrderID&Status=$Status";
	if ($z{'transactionid'})	{	$String	.=  "&TransactionID=$TransID";			}
	if ($z{'description'})         {   	$String .=  "&Description=$Description";  }
	&WriteLog ("description=$z{'description'}&&$Description", 2);
	my $PreviousSession;

	&WriteLog("OID:$OrderID. Received session status $SessionID: $String", 1);

	my $Filename	=	$SessionsPath . $SessionID . $SessionExt;
	if (-s $Filename) 	{
		open (FH, "<".$Filename) 	or &AddError('F001', $Filename, $!);
		while (<FH>) {
			$PreviousSession	.= $_;
		}
		close FH 			or &AddError('F005', $Filename, $!);

	if ($Status == 0)	{ $String	= $String . "\n\nNEXT ATTEMPT->\n".  $PreviousSession; }
	elsif ($PreviousSession){ $String	= $PreviousSession . "\n\nNEXT ATTEMPT->\n" . $String; }
	}

	#Writing to file with exclusive file locking.
	open(FH, ">".$Filename)					or &AddError('F001', $Filename, $!);
	if	(&WeAreOK())
	{
		flock(FH, LOCK_EX)				or &AddError('F002', $Filename, $!);
		if	(&WeAreOK())
		{
			(print FH $String)			or &AddError('F003', $Filename, $!);
			if	(&WeAreOK())
			{
				flock(FH, LOCK_UN)				or &AddError('F004', $Filename, $!);
				if	(&WeAreOK())
				{
					close FH						or &AddError('F005', $Filename, $!);
				}
			}
		}
	}
}


#Showing the page of successful transaction completion
sub ShowSuccess()
{
	&WriteLog("OID:$OrderID The page of successful transaction completion is shown: $TransactionID", 1);
	my $Messg	= 	&form_messg;
	my $SuccessTemplate;
	($Language eq 'ru')  	? ($SuccessTemplate = $SuccessRusTemplate) : ($SuccessTemplate = $SuccessEngTemplate);
	my $template	=	&getform($TemplatesPath.$SuccessTemplate);
	$template		=~	s/<<TransactionID>>/$Messg/ig;
	$template		=~	s/<<back>>/$MainPage/gi;
	print				$template;

}

#forms html output for successful transaction as a table content
sub form_messg {
	my ($param, $messg, @Record);
	my @outData	= @{$LANG_SETTINGS{$Language}};

	push @Record, { $outData[0] => $EnterpriseName },{$outData[1] => $MerchantName},{$outData[2] => $ServerName },
	{$outData[3] => $HelpDesk }, {$outData[4] => $TransAmount." ".$TransCurrency }, {$outData[5] => $TransCurrency },
	{$outData[6] => $TransDate}, {$outData[7] => $TransID}, {$outData[8] => $AuthCode}, {$outData[9] => $CustomerName},
	{$outData[10] => $OperationType };

	my $color;
	my $mod=2;
	foreach $param (@Record) {
		$color = "white";
		foreach my $key (keys %$param) {
			next unless ($param->{$key});
			if ($mod%2) {$color = "#e6e6e6";}
			$messg .= "<TR bgcolor=$color><TD><font size=2><bgcolor=$color>$key:</TD>
			<TD><font size=2><bgcolor=$color>$param->{$key}</TD></TR>\n";
			$mod++;
		}
    	}
    return $messg;
}

#Showing the page of failed transaction
sub ShowFailure()
{
	&WriteLog("OID:$OrderID The page of a failed transaction is shown: $Description", 1);
	my $Messg	=  "$CustomerTitle<BR>$CustomerMessg";
	my $FailureTemplate;
	($Language eq 'ru')  	? ($FailureTemplate = $FailureRusTemplate) : ($FailureTemplate = $FailureEngTemplate);
	my $template	=  &getform($TemplatesPath.$FailureTemplate);
	$template	=~ s/<<SessionDescription>>/$Messg/gi;
	$template		=~	s/<<back>>/$MainPage/gi;
	print		$template;
}

#Add error to the log
#On input gets an error code and up to three additional parameters
sub AddError()
{
	$OperationStatus	=	1;
	my $num				=	shift;
	my ($Param1, $Param2, $Param3)			=	(shift, shift, shift);
	$ErrorLog{$num}		=	$ERRORS{$num};
	$ErrorLog{$num}		=~	s/<<Param1>>/$Param1/gi;
	$ErrorLog{$num}		=~	s/<<Param2>>/$Param2/gi;
	$ErrorLog{$num}		=~	s/<<Param3>>/$Param3/gi;
	&WriteLog("OID:$OrderID ".$ErrorLog{$num}, 3);
}

#Showing errors log
#Shows log content in browser
sub ShowErrors()
{
	my $ErrorTemplate;
	($Language eq 'ru')  	? ($ErrorTemplate = $ErrorRusTemplate) : ($ErrorTemplate = $ErrorEngTemplate);
	my $template	=	&getform($TemplatesPath.$ErrorTemplate, 1);
	my $ErrorsList	=	"";
	foreach my $Error (sort keys %ErrorLog)
	{
		$ErrorsList	.=	"<li><b>$Error</b>: $ErrorLog{$Error}</li>\n"
	}
	$template		=~	s/<<Reason>>/$ErrorsList/gi;
	$template		=~	s/<<back>>/$MainPage/gi;
	print "Content-type: text/html\n\n";
	print			$template;
}

sub WeAreOK()
{
	if ($OperationStatus)
	{
		&ShowErrors();
		exit;
	}
	return 1;
}


############################################################################
sub ParseResults {
############################################################################
    my $response = shift;
    &PStr ($response);
    (defined $z{'answer'})		?	($Answer = $z{'answer'})	:	($Answer = '');
    (defined $z{'status'})		?	($Status = $z{'status'}) 	: 	($Status = '');
    (defined $z{'terminal'})		?	($TerminalID  = $z{'terminal'})	: 	($TerminalID = '');
    (defined $z{'orderid'})		?	($OrderID = $z{'orderid'}) 	: 	($OrderID = '');
    (defined $z{'sessionid'})		?	($SessionID = $z{'sessionid'}) 	: 	($SessionID = '');
    (defined $z{'description'})		?       ($Description = $z{'description'}) : 	($Description = '');

    if ($Status == 0) {

    (defined $z{'transactionid'})	?	($TransID = $z{'transactionid'}) 	: ($TransID = '');
    (defined $z{'transactionamount'})	?	($TransAmount = $z{'transactionamount'}) : ($TransAmount = '');
    (defined $z{'transactioncurrency'})	?	($TransCurrency = $z{'transactioncurrency'}) : ($TransCurrency = '');
    (defined $z{'customername'})	?	($CustomerName = $z{'customername'}) : ($CustomerName = '');
    (defined $z{'transactiondate'})	?	($TransDate = $z{'transactiondate'}) : ($TransDate = '');
    (defined $z{'authcode'})		?	($AuthCode = $z{'authcode'}) : ($AuthCode = '');
    if ($TransAmount)				{$TransAmount =~ s/(\d\d)$/\.$1/;	}
    } else {

    (defined $z{'customertitle'})	?	($CustomerTitle = $z{'customertitle'}) : ($CustomerTitle = '');
    (defined $z{'customermessage'})	?	($CustomerMessg = $z{'customermessage'}) : ($CustomerMessg = '');

    }

}


############################################################################
sub PStr		#08/25/00 2:35
############################################################################
{
	my $string	=	shift;
	my @fields	=	split(/&/, $string);

	foreach(@fields)
	{
		/([^=]+)=(.*)/ && do
		{
			my ($field, $value)	=	($1, $2);
			$z{lc($field)}		=	$value;
		}

	}
}	##PStr


#This procedure calls an external utility for signing outcoming data and checking signature
#on incoming data
#Parameters:
#What to sign/check
#Is text signed or checked (0 - signing, 1 - checking)
#On output - results of utility work
sub Sign()
{
	my $text			=	shift;		#What to sign/check
	my $stext			=	'';
	my $pid;

	my $filename		=	"$SIGN_TOOL -c -f $SIGN_INI";	#Path to the utility

	#Open bidirectional pipe.
	$pid				=	open2(\*Reader, \*Writer, $filename);

	#Pass data and close the stream
	print Writer $text;
	close Writer;
	#Get data and close the stream
	while (my $line	= <Reader>)
	{
		$stext			.=	$line;
	}
	close Reader;

	#Wait for process to exit.
	waitpid($pid, 0);
	return $stext;					#Return results of the utility work.
}

#Saves signed session results in file.
sub SaveCheck()
{
	unless (-e $SessionsPath) {
		mkdir $SessionsPath, 0755 or &AddError ('E009', "Can't create $SessionsPath directory: $!");
	}
	my $Check = shift;
	my $PreviousCheck;
	#Write to file with exclusive lock.
	my $Filename	=	$SessionsPath . $SessionID . $CheckExt;
	if (-s $Filename) 	{
		open (FH, "<".$Filename) or &AddError('F001', $Filename, $!);
		while (<FH>) {
			$PreviousCheck	.= $_;
		}
		close FH or &AddError('F005', $Filename, $!);

	if ($Status == 0)	{ $Check	= $Check . "\n\nNEXT ATTEMPT->\n".  $PreviousCheck; }
	elsif ($PreviousCheck)	{ $Check	= $PreviousCheck . "\n\nNEXT ATTEMPT->\n" . $Check; }
	}

	open(FH, ">".$Filename) or &AddError('F001', $Filename, $!);
	binmode (FH);
	if (&WeAreOK())
	{
		flock(FH, LOCK_EX) or &AddError('F002', $Filename, $!);
		if	(&WeAreOK())
		{
			(print FH $Check) or &AddError('F003', $Filename, $!);
			if	(&WeAreOK())
			{
				flock(FH, LOCK_UN) or &AddError('F004', $Filename, $!);
				if	(&WeAreOK())
				{
					close FH or &AddError('F005', $Filename, $!);
				}
			}
		}
	}
}

sub SaveSessionConvertor		#01/09/01 8:26
############################################################################
{
	my $FileName = $SessionsPath . $CvrtFileName;
	my $Message = $OrderID . '|' . $SessionID . '|' . $Status;
	unless (-s $FileName) 	{	$Message = "SessionID|OrderID|Status" . "\n" . $Message;	}

	open(OUTFILE, ">>".$FileName)		or die "can't open OUTFILE: $!";
	flock(OUTFILE, LOCK_EX)			or die "can't lock OUTFILE: $!";

	(print OUTFILE $Message, "\n")		or die "can't write OUTFILE: $!";

	flock(OUTFILE, LOCK_UN)			or die "can't flock OUTFILE: $!";
	close OUTFILE				or die "can't close OUTFILE: $!";

}	##SaveSessionConvertor
