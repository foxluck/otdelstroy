package Log;

use strict;
use integer;

#use CGI::Carp qw(fatalsToBrowser);
use Fcntl ':flock';
use POSIX qw(strftime);
use Exporter;

use	vars						qw(@ISA @EXPORT $Version $LogFilename $LogTolerance);
@ISA						=	qw(Exporter);

$Version					=	1.1;

#Уровни ведения лог-файла:
#0 - тестовые сообщения и сообщения о работе с самим лог-файлом
#1 - сообщения о нормально завершенном действии, уровень 1
#2 - сообщения о нормально завершенном действии, уровень 2
#3 - незначительные ошибки и замечани
#4 - серьезные ошибки
#5 - ключевые ошибки и ошибки системы
my @LogLevels				=	('.', '-', '+', '*', '^', '!');

@EXPORT						=	qw(
								OpenLog
								CloseLog
								WriteLog
								SetLogTolerance
							);

$LogTolerance				=	0;

#Установить 'терпимость' к записываемым сообщениям. При терпимости 0 записываются все сообщения,
#при 'терпимости' 5 - только самые важные.
sub SetLogTolerance()
{
	$LogTolerance			=	shift;
}

#Открыть лог-файл. На входе получает имя файла и толерантность.
sub OpenLog
{
	if (!$LogFilename)
	{
		$LogFilename		=	shift;
	}
	my $LT					=	shift;
	if ($LT)	{
		&SetLogTolerance($LT);
	}

	&WriteLog("Log '$LogFilename' opened", 0);
}

#Закрыть лог-файл. На входе получает сообщение и его уровень.
sub CloseLog
{
	&WriteLog('Log closed', 0);
}

#Записать сообщение в лог-файл.
sub WriteLog()
{
	my $LogMessage			=	shift;
	my $LogLevel			=	shift;
	my $LevelSign			=	$LogLevels[$LogLevel];
	if (!$LevelSign){
			$LogLevel		=	0;
			$LevelSign		=	$LogLevels[$LogLevel];
	}

	if ($LogLevel < $LogTolerance)	{
		return;
	}

	my $TimeStamp			= strftime "%c", localtime;

	$LogMessage			= $LevelSign . $TimeStamp . ': ' . $LogMessage;

	open(LOGFILE, '>>'.$LogFilename)   	or die "can't open LOGFILE:$LogFilename: $!";
	flock(LOGFILE, LOCK_EX)		   	or die "can't flock LOGFILE: $!";

	(print LOGFILE $LogMessage, "\n")	or die "can't write LOGFILE: $!";

	flock(LOGFILE, LOCK_UN)			or die "can't flock LOGFILE: $!";
	close LOGFILE				or die "can't close LOGFILE: $!";
}

1;

