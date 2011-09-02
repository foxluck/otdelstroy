@rem = 'Р•£®бва†ж®п OLE-™ЃђѓЃ≠•≠в† §Ђп ѓаЃҐ•а™® ® бЃІ§†≠®п нЂ•™ваЃ≠≠ле ѓЃ§ѓ®б•©
cd libipriv
call setup.bat
cd ..
@rem = '--*-Perl-*--
@echo off
if "%OS%" == "Windows_NT" goto WinNT
perl -x -S "%0" %1 %2 %3 %4 %5 %6 %7 %8 %9
goto endofperl
:WinNT
perl -x -S "%0" %*
if NOT "%COMSPEC%" == "%SystemRoot%\system32\cmd.exe" goto endofperl
if %errorlevel% == 9009 echo You do not have Perl in your PATH.
goto endofperl
@rem ';
#! C:/perl/bin/perl -w
#line 14
##########################################################################################
#	Defining environment
##########################################################################################
use integer;
use strict;
use Cwd;
use Util;

my $Bin				= 	cwd;

my $DataTemplate		=	$Bin.'/Install.tpl/Data.tpl';

my $DataFilename		=	'Data.pm';

my $PathsTemplate		=	$Bin.'/Install.tpl/Paths.tpl';

my $PathsFilename		=	'Paths.pm';

my $CheckerTemplate		=	$Bin.'/Install.tpl/Checker_shop.tpl';

my $CheckerFilename		=	$Bin.'/checker.ini';

my $CheckerExe			=	$Bin.'/checker.exe';

my $EnterpriseName		= 	'';

my $MerchantName		=	'';

my $OperationType		= 	'';

my $HelpDesk			= 	'';

my $KeyPhrase			=	'';

my $OperationMode		=	'';

my $KeyPath			=	'';

my $KeyPathReal			=	$Bin.'/Keys/Real/';

my $KeyPathTest			=	$Bin.'/Keys/Test/';

my $ServerUrl			=	'';

my $ServerTest			=	'payment.cyberplat.ru/cgi-bin/GetForm.cgi';

my $ServerReal			=	'card.cyberplat.ru/cgi-bin/GetForm.cgi';

my $ShopID			=	'';

my $BankKey			=	'';

my $line;

my $addphrase			=	'';

##########################################################################################
#	Loading the libraries
##########################################################################################
if (-s 'Paths.pm') {
	open (PATHS, 'Paths.pm') or die "Can't open Paths.pm: $!\n";
	while ($line = <PATHS>) {
		if ($line =~ /^\$SIGN_TOOL\b/i) {
			($KeyPath) 	= $line =~ /\'(.+)\'/;
			$KeyPath	=~ s/checker.exe//;
		}
	}
	close PATHS;
}

$line = '';

if (-s 'Data.pm') {
	open (DATA, 'Data.pm')	or die "Can't open Data.pm: $!\n";
	while ($line =  <DATA>) {
		if ($line =~ /^\$EnterpriseName\b/) {
			($EnterpriseName) 	= $line =~ /\'(.+)\'/;
		} elsif ( $line =~ /^\$MerchantName\b/) {
			($MerchantName)		= $line =~ /\'(.+)\'/;
		} elsif ($line	=~ /^\$HelpDesk\b/) {
			($HelpDesk) 		= $line =~ /\'(.+)\'/;
		} 
	}
	close DATA;
}

print "CyberCard e-shop installation procedure.\n\n";
print "Please enter following values:\n";
print "(note: If default values are correct just press Enter)\n";

$line	= 	'';
while (!$line or $line !~ /^(1|2)$/) {
	print "Enter shop operation mode (real - 1, test - 2)...";
	chomp ($line 	= 	<STDIN>);
	$OperationMode	=	$line;
}

$line 	=	'';

if ($OperationMode == 1) {	
	$ServerUrl	=	$ServerReal;
	$KeyPath	=	$KeyPathReal;
} else {
	$ServerUrl	=	$ServerTest;
	$KeyPath	=	$KeyPathTest;
}	

while (!$line) {
	print "\nEnter your Enterprise name <$EnterpriseName>...";
	chomp ($line = <STDIN>);
	if ($line )	{ 	$EnterpriseName = $line;	}
	last if ($EnterpriseName);
}

$line = '';

while (!$line) {
	print "\nEnter your Merchant name <$MerchantName>...";
	chomp ($line = <STDIN>);
	if ($line) 	{	$MerchantName 	= $line;	}
	last if ($MerchantName);
}

$line = '';

while (!$line) {
	print "\nEnter your contact e-mail <$HelpDesk>...";
	chomp ($line = <STDIN>);
	if ($line) 	{	$HelpDesk 	= $line;	}
	last if ($HelpDesk);
}

$line	=	'';

print "\nEnter Authorization Server URL <$ServerUrl>...";
chomp($line				=	<STDIN>);
if ($line)
{
	$ServerUrl		=	$line;
}
$line	=	'';

print "\nEnter the path to keys directory <$KeyPath>...";
chomp($line				=	<STDIN>);
if ($line)
{  #провер€ем ввел ли пользователь иной путь к ключам
   	if ($line!~/(\\|\/)$/) { #есть ли разделитель в конце пути?
   	   my $delim='';  
	   ($delim)=$line=~/(\\|\/)/;# находим разделитель и добавл€ем в конец строки.
	   $line.=$delim;
	   }
	$KeyPath			=	$line;
	}
$line	= '';


my $CodeFile         = $KeyPath.'secret.key';

#ќпредел€ем код магазина из файла secret.key
unless (-s $CodeFile) {	
print "\nCan't find secret.key file in the $KeyPath directory.\n";
print "Please, put all the shop keys in the selected directory and run the script again.\n";
exit;
}

open (SHOPID, "<$CodeFile"); 
	 $.	= 0;
	 do {$line=<SHOPID>} until $.==2 || eof;
	 $line	=~ s/\W+$//;
	 $ShopID=substr ($line, -8);
	 $ShopID=~ s/^0*//;
	$line='';
close SHOPID;

unless ($ShopID =~ /^\d+$/) { 	
	print "\nError reading secret.key in $KeyPath directory.\n";
	print "Please check if the file has a correct format\n"; 
	exit;
}

my $PubKeysFile	= $KeyPath.'pubkeys.key';
unless (-s $PubKeysFile) {	
	print "\nCan't find pubkeys.key file in the $KeyPath directory.\n";
	print "Please, put all the shop keys in the selected directory and run the script again.\n";
	exit;
}

open (BANKKEY, $PubKeysFile);
	while ($line = <BANKKEY>) {
		if ($line =~ /CyberCardServer/) {
			$line	=~ s/\W+$//;
			$BankKey 	= substr ($line, -8);
			$BankKey	=~ s/^0+//;
			last;
		}
	}
close BANKKEY;

unless ($BankKey =~ /^\d+$/) {
	print "\nError getting CyberCardServer code at pubkeys.key in $KeyPath directory.\n";
	print "Please check if CyberCardServer key is imported in pubkeys.key file and/or \nthe file has a correct format\n"; 
	exit;
}
$line = '';

while (!$line)
{
	print "\nEnter password for signing shop messages...";
	chomp($line		=	<STDIN>);
	if ($line)
	{
		$KeyPhrase		=	$line;
	}
}
$line	=	'';

&Update($DataTemplate,		$DataFilename);
&Update($PathsTemplate,		$PathsFilename);
&Update($CheckerTemplate,	$CheckerFilename);

print "\nThank you for your cooperation.\n";

sleep (2);
exit;

sub Update()
{
	my $Source		=	shift;
	my $Destination		=	shift;
	
	my $Template		=	&getform($Source, 1);
	
	$Template			=~	s/%shopid%/$ShopID/gi;
	$Template			=~	s/%password%/$KeyPhrase/gi;
	$Template			=~	s/%keypath%/$KeyPath/gi;
	$Template			=~	s/%checker_exe%/$CheckerExe/gi;
	$Template			=~	s/%checker_ini%/$CheckerFilename/gi;
	$Template			=~	s/%serverurl%/$ServerUrl/gi;
	$Template			=~ 	s/%enterprise_name%/$EnterpriseName/gi;
	$Template			=~ 	s/%merchant_name%/$MerchantName/gi;
	$Template			=~ 	s/%help_desk%/$HelpDesk/gi;
	$Template			=~ 	s/%bankkey%/$BankKey/gi;

	open FILE, '>'.$Destination;
	print FILE $Template;
	close FILE;
}__END__
:endofperl