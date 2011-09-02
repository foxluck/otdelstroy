package Paths;
use Cwd;

$Bin	= cwd;

#Определяем пути к файлам.

if ($ENV{'HTTPS'} =~	/on/)	{
	$BaseURL		=	'https://';
}
else	{
	$BaseURL		=	'http://';
}

#определяем имя сервера (для $return_url)
$BaseURL .= $ENV{'SERVER_NAME'};

#определяем порт, если не '80', то добавляется к $return_url
($ENV{SERVER_PORT} =~ /^(80|443)$/) ?	'' : ($BaseURL .= ':'.$ENV{SERVER_PORT});


$BaseURL			.=	$ENV{'SCRIPT_NAME'};
$BaseURL			=~	s/(\/[^\/]+)$//;
$ServerName			=	$ENV{'SERVER_NAME'};

#если вы при запуске скрипта получили эту ошибку, впишите путь к каталогу скриптов вместо $Bin. 
#Не используйте заключительный слэш: '/usr/local/.../shop'
$BasePath			= 	$Bin
or die "Perl cannot define the current directory, please add it manually"; 

$TemplatesBase			=	'/Templates';
$SessionsBase			=	'/Sessions';

$TemplatesPath			=	$BasePath.$TemplatesBase;
$SessionsPath			=	$BasePath.$SessionsBase;

$CvrtFileName			=	'checks.inf';
$FailureRusTemplate		=	'failure_rus.htm';
$SuccessRusTemplate		=	'success_rus.htm';
$ErrorRusTemplate		=	'error_rus.htm';
$FailureEngTemplate		=	'failure_eng.htm';
$SuccessEngTemplate		=	'success_eng.htm';
$ErrorEngTemplate		=	'error_eng.htm';

$CloseURL			=	$BaseURL.'/close.htm';
$ResultURL			=	$BaseURL.'/result.cgi';
$MainPage			=	$BaseURL.'/test.cgi';


$SIGN_TOOL			=	'%checker_exe%';
$SIGN_INI			=	'%checker_ini%';

$FORM_URL                       =       '%serverurl%';

$SHOP_IP			= 	$ENV{'SERVER_NAME'};

use strict;
use vars qw
	(
		@ISA
		@EXPORT		
		$Bin
		$BaseURL
		$SelfURL
		$CloseURL		
		$ResultURL
		$SERVER_URL
                $FORM_URL
                $ResultURL
                $ServerName
		
		$BasePath
		$TemplatesBase
		$SessionsBase		
		$TemplatesPath
		$SessionsPath
                $CvrtFileName
		$SuccessRusTemplate
		$FailureRusTemplate
                $ErrorRusTemplate
		$SuccessEngTemplate
		$FailureEngTemplate
                $ErrorEngTemplate

		$MainPage
		$SIGN_TOOL
		$SIGN_INI
		$SHOP_IP
	);
use Exporter;
@ISA		=	qw(Exporter);

@EXPORT		=	qw
	(
		$Bin
		$BasePath
		$BaseURL
		$CloseURL
                $ResultURL
		$SERVER_URL
	        $FORM_URL
		$ServerName

		$TemplatesBase
		$SessionsBase
		$TemplatesPath
		$SessionsPath
                $CvrtFileName
		$PageTemplate
		$SuccessRusTemplate
		$FailureRusTemplate
                $ErrorRusTemplate
		$SuccessEngTemplate
		$FailureEngTemplate
                $ErrorEngTemplate

		$MainPage
		$SIGN_TOOL
		$SIGN_INI
		$SHOP_IP
);

1;
