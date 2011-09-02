#! C:/perl/bin/perl -w

#ReadME
#This script can be used only as part of CyberPOS software as it uses some of it's functions and variables.
#All configuration parameters are in  USER DEFINED VARIABLES section below.  There you will
#find description of the variables and values they can be set to.
#In some situtations #SCRIPT VARIABLES section may be edited (e.g. in case authorisation server
#script is moved to another place), but it is not recommended to do without a reason.

#Version	= 2.0;

use CGI ':cgi';
use strict;
use HTTP::Request::Common;				# Обращение к серверу.
use LWP::UserAgent;					# Общение с сетью.
use IPC::Open2;						# Двунаправленные pipes (подпись/шифрование)
use Fcntl	':flock';				# Экслюзивный доступ к файлам
use POSIX qw(strftime);
#use CGI::Carp qw (fatalsToBrowser);

use Log;
use Util;
use Paths;

###################################################################################################
# USER DEFINED VARIABLES
###################################################################################################
#Configure the system so only the administrator can run the script. After it's done, set the variable to '0'
#While $Security=1 the script won't work.
my $Security    = 1;

#show or not results in Browser 0 - 'no', 1 - 'yes'. This parameter is used to prevent showing
#result page in Browser if the request was made by another program through command line.
my $ShowPage	= 1;

#save or not server signed documents in /Sessions directory with '.res' extension. 1 - 'yes', 0 - 'no'
my $SaveChecks	= 1;

#How detailed the log must be: 1 - very detailed (use for debugging), 2 - only important events
my $LogTolerance= 1;

#The script's  directory: !!! use this variable in case $Bin can't be defined by FindBin.pm
#to use $BasePath just uncomment the variable and write a real path to your shop directory
my $BasePath	= '';

if (!$Bin) {
	$Bin = $BasePath;
}

#path to templates directory
my $TemplateBase= $Bin. '/Templates/';
my $TemplatePath= $TemplateBase.'result.htm';

###################################################################################################
# SCRIPT VARIABLES
###################################################################################################

my $SessionsPath= $Bin.'/Sessions';            #Results dirictory
my $LogFilename	= $Bin.'/reversal.log';
#use this URL for transactions in the real mode
#my $ServerURL	= "https://card.cyberplat.ru/cgi-bin/ProcessTransaction.cgi";
#use this URL for transactions in the test mode
my $ServerURL	= "https://payment.cyberplat.ru/cgi-bin/ProcessTransaction.cgi";


my $Ext		= '.sta';
my $ChecksExt	= '.res';
my $results	= '';
my $response	= '';
my $next	= '';			#is used to divide records in Session files if one TransactionID was used more than once
my $CallType	= 0;  			#if method Post ('0'), error calls 'die' if ARGV ('1'), error calls 'exit'
my $State	= 0;


###################################################################################################
# START WORK
###################################################################################################

$| = 1;
my $TransactionID	=	0 ; 		#must be a number
my $TransactionType     =	"reversal";
my $Pay			=	0 ;     	#sum (is not obligatory)
my $ParentTransactionID	=	0 ;     	#is returned by the server
my $SessionDescription;     	#string returned by the server
my (%args, $Query, $Comment, $ReturnPage, $SelfUrl, $Port, $PageName, $Message, $arg, $status);

&OpenLog ($LogFilename, $LogTolerance);
if ($Security)
{
        &error ("Check security of the script. It should be configured to be accessed only by the administrator", $CallType);
}

&WriteLog("Log opened", 2);
# if script was called from command line
if (@ARGV)
{
	my $temp;
	foreach $temp (@ARGV) {

		$arg .= $temp.'&';
	}
	%args		=	&ParseString ($arg);

	$TransactionID	=	$args {'transactionid'};

	$Comment	=	$args {'comment'};

	$CallType	= 1;
}

#if script was called through CGI
elsif ( $ENV {'REQUEST_METHOD'} eq 'POST')
{
	$TransactionID	=	param ('transactionid');

	$Comment	=	param ('comment');

	$ReturnPage  	=	$ENV {'HTTP_REFERER'};
}

else
{
       $TemplatePath    =       $TemplateBase.'reversal.htm';
       $Port		=	':'.$ENV{'SERVER_PORT'} unless $ENV{'SERVER_PORT'} =~ /^[80|443]$/;
       $SelfUrl         =       $ENV {'SERVER_NAME'}.$Port.$ENV {'SCRIPT_NAME'};
       $State=&ShowPage ("Sample Reversal Page", "");
       unless ($State)
       {
          &error ("Error getting form: $TemplatePath", $CallType);
        }
    exit;
}

if ( !IsValidID ($TransactionID))
{
	&error ("TransactionID must be numeric", $CallType);
}

if ($Comment)
{
	$Comment =~s/^[^\w]+$//gm;
	$Comment = substr $Comment, 0, 125;
}

$Query	=	join ("&", "TransactionID=$TransactionID", "TransactionType=$TransactionType");

if ($Comment)
{
	$Query	=	join ("&", $Query, "Comment=$Comment");
}
&WriteLog ("The query=$Query", 1);

#send data to the server
($response, $status)        =   &communicateHost($ServerURL, $Query);
unless ($status)
{
	&error ($response, $CallType);
}

&WriteLog ("Response=$response", 2);

if (! $response =~ m/^status/mi)
{
        &error ("Response has incorrect format", $CallType);
}

if ($SaveChecks)
{
	&SaveChecks ( $response);
}

#Check AC signature
# Under Win32 we use corresponding OLE automation server instead of checker.exe
$response		= $^O =~ /MSWin32/
	? eval { require Checker; return Checker::verify( $response ); }
	: &Sign($response, 1);
&WriteLog ("Checked response=$response", 1);

if ($response   =~/SessionStatus=|Error=/i)
{
    &error ($response, $CallType);
}

my %results		= &ParseString ($response);
my $Status		= $results {'status'};
if (!defined ($Status)) 	{	$Status	= -100; 	}
my $ErrorCode		= $results {'errorcode'};

$ParentTransactionID = 	defined ($results {'parenttransactionid'}) ? $results {'parenttransactionid'} : $TransactionID;
$SessionDescription	= $results {'description'};

&SaveStatus;

#show results in browser if POST request
if ($ShowPage && $CallType == 0)
{
	my $PageName	= '';
	my $Message	= '';
	if ($Status == 0)
	{
		$PageName 	= $SessionDescription;
		$Message	= "$TransactionType for TransactionID: $ParentTransactionID has been successfully performed.";
        }
	else
	{
		$PageName 	= "Failure Page";
		$Message	= "$TransactionType for TransactionID: <b>$ParentTransactionID</b> has failed. ";
		if ($ErrorCode)
		{
		    $Message.=" ErrorCode=$ErrorCode, ";
		}
		if ($SessionDescription)
		{
			$Message.="<BR>". $SessionDescription;
		}
	}
  if (! ($State = &ShowPage ($PageName, $Message)))
  {
		&error ("Couldn't show result page", $CallType);
  }
}
&WriteLog ("Log closed\n", 2);

#return result to a calling program
if ($CallType == 1)
{
	my $Message="Status=$Status";
	$Message .=", ParentTransactionID=$ParentTransactionID";
	if ($ErrorCode)
	{
	    $Message .=", ErrorCode=$ErrorCode";
	}
    	$Message .= ", Description=$SessionDescription";
	print $Message;
}
exit;

###################################################################################################
# END OF WORK
###################################################################################################
# SUBPROGRAMS
###################################################################################################
sub error
{
	my $string 	= shift;
	my $CallType	= shift;
	if ($CallType == 0)
	{
		my $pageName 	= "Error";
		my $message	= "Error :  ".$string;
		&ShowPage  ($pageName, $message);
		&WriteLog ("Log closed", 2);
		exit;
	}
	if ($CallType == 1)
	{
		print "Error: $string\n";
		&WriteLog ("Log closed", 2);
		exit;
	}
	else { &WriteLog ("Unknown CallType=$CallType, check value of '$CallType'", 2); }
}


sub SaveChecks
{
	my $Check	= shift;

	my $SubDir	=	"/$TransactionType/";
	unless (-e $SessionsPath.$SubDir)
	{
		mkdir $SessionsPath.$SubDir, 0755
		or &error ("Can't create $SessionsPath$SubDir: $!", $CallType);
	}
	my $Filename			=	$SessionsPath .$SubDir. $TransactionID. $ChecksExt;
	#Если файл не пустой, то добавить следующую запись в конец с разделителем next
	if (-s $Filename)	{$next  =  "\nNEXT ATTEMPT->\n";}
	$Filename			=  ">>".$Filename;
	&WriteLog ("Saving Check", 1);
	open(FH, $Filename)			or &error ("Couldn't open $Filename; $!", $CallType);
	binmode (FH);
	flock(FH, LOCK_EX)			or &error ("Couldn't lock $Filename: $!", $CallType);
			print FH $next, $Check, "\n"	or &error ("Couldn't print to $Filename: $!", $CallType);
	flock(FH, LOCK_UN)     			or &error ("Couldn't unlock $Filename: $!", $CallType);
	close FH				or &error ("Couldn't close $Filename; $!", $CallType);
}

sub SaveStatus
{
	my $SubDir	=	"/$TransactionType/";
	unless (-e $SessionsPath.$SubDir)
	{
		mkdir $SessionsPath.$SubDir, 0755
		or &error ("Can't create $SessionsPath$SubDir: $!", $CallType);
	}

	#Формирование строки статуса сессии
	my $Status				=	"Status=$Status";
	if ($ParentTransactionID)   {	$Status	.=	"&ParentTransactionID=$ParentTransactionID";	}
	if ($ErrorCode)		    {	$Status .=	"&ErrorCode=$ErrorCode";			}
	if (! $SessionDescription)  {   $SessionDescription = "Transaction has failed"; }

	$Status	.=	"&SessionDescription=$SessionDescription";

	my $FileIndex	=	defined ($ParentTransactionID) ? $ParentTransactionID : $TransactionID;
	&WriteLog ("Saved Status=$Status", 2);

	#Запись
	my $Filename			=	$SessionsPath .$SubDir. $FileIndex . $Ext;
	#Если файл не пустой, то добавить следующую запись в конец с разделителем next
	if (-s $Filename)	{$next  =  "\nNEXT ATTEMPT->\n";}
	$Filename			=  ">>".$Filename;
	&WriteLog ("SessionType=$Filename", 1);
	open(FH, $Filename)			or &error ("Couldn't open $Filename; $!", $CallType);
	binmode (FH);
	flock(FH, LOCK_EX)			or &error ("Couldn't lock $Filename: $!", $CallType);
			print FH $next, $Status, "\n"	or &error ("Couldn't print to $Filename: $!", $CallType);
	flock(FH, LOCK_UN)     			or &error ("Couldn't unlock $Filename: $!", $CallType);
	close FH				or &error ("Couldn't close $Filename; $!", $CallType);
}

sub communicateHost
{
	my ($request, $result, $status);
	my $url				=	shift;
	my $data			=	shift;
	my $ua				=	LWP::UserAgent->new or &error ("Error constructing UserAgent object: $!", $CallType);

	if ($data)				#Если пытаемся передавать данные
	{
		# Under Win32 we use corresponding OLE automation server instead of checker.exe
		my $sdata 	= $^O =~ /MSWin32/
			? eval { require Checker; return Checker::sign( $data ); }
			: &Sign($data, 0);
		&WriteLog ("Signed request=$sdata", 1);
		if ($sdata      !~ /BEGIN(.*)END/si)
                {
		        &error ("Error signing or checking signature. Check keys parameters: $sdata", $CallType);
		}
		$request	=	$ua->request(POST $url, ['message', $sdata])  or &error ("Error establishing connection to $url: $!", $CallType);
		if ($request->is_success)
		{
			$result	=	$request->content;
			$status	=	1;
			&WriteLog("Got response from $url", 1);
		}
		else
		{
			$result = 	$request -> message;
			$status = 	0;
			&WriteLog("Can't connect to $url, $result", 2);

		}
	}
	else {
		$result	= 'No data to send to the server'; $status = 0;
		&WriteLog ("No data to send to the server", 2);
	}
	return				$result, $status;
}

sub Sign
{
	my $text			=	shift;		#Что подписывать/проверять
        my $mode			=	shift;		#Подписывается текст или проверяется (0 - подпись, 1 - проверка)
	my $stext			=	'';

	my $pid;

	my $filename		=	$SIGN_TOOL;	#Путь к утилите

	if ($mode)
	{	$filename		.=	" -c -f $SIGN_INI";	}
	else
	{	$filename		.=	" -s -f $SIGN_INI";	}

	&WriteLog ("Checker paths=$filename", 1);
	#Открыть двунаправленный pipe.
	$pid				=	open2(\*Reader, \*Writer, $filename)  or &error ("Couldn't open pipe: $!", $CallType);
	#Передать данные и закрыть поток.
	print Writer $text		or &error ("Can't write data to checker: $!", $CallType);
	close Writer;
	#Принять данные и закрыть поток.
	while (my $line		= <Reader>)
	{
		$stext			.=	$line or &error ("Cant' read signed data from checker: $!", $CallType);
	}
	close Reader;

	#Дождаться завершения процесса.
	waitpid($pid, 0);

	return $stext;						#Вернуть результат работы утилиты.
}

sub IsValidID
{
	my $TransactionID	= 	shift;
	if ($TransactionID =~/^\d+$/) {
		return 1;
	}
	return 0;
}

sub ShowPage
{
	my ($PageName, $Message)= @_;
	my $ReturnPage  = $ReturnPage;
	my $template	 = &getform ($TemplatePath);
	$template        =~ s/<<PageName>>/$PageName/;
	$template        =~ s/<<Message>>/$Message/;
        $template        =~ s/<<SelfUrl>>/$SelfUrl/;
	$template	 =~ s/<<ReturnPage>>/$ReturnPage/;
	print $template;
return 1;
}

