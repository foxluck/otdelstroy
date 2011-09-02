package Util;

use strict;
use integer;
#use CGI::Carp qw(fatalsToBrowser);
use vars qw(@ISA @EXPORT $Version $DebugString);

use Exporter;
@ISA		=	qw(Exporter);
$Version	=	1.2;
$DebugString=	'';

@EXPORT		=	qw(
					getform
					isValidID
					isValidInt
					isValidName
					isValidEmail
					ParseString
					$DebugString
				);

# Получение файла-шаблона
sub getform()
{
	my $template			=	shift;
	my $type				=	shift;
	my $value				=	'';
	open (TEMPLATE, $template) or die("Cant open template file $template: $!\n");
	if (!$type)
	{
		$value				.=	"Content-type: text/html\n\n";
	}
	while (my $line = <TEMPLATE>)	{	$value .= $line	}
	close TEMPLATE;
	return $value;
}

#Возвращает true если параметр - положительное целое.
sub isValidID(@$)
{
	$_					=	shift;
	if ($_ && isValidInt($_))	{	return 1;	}
	else						{	return 0;	}
}

#Возвращает true если параметр - положительное целое или 0.
sub isValidInt(@$)
{
	$_					=	shift;
	if (!$_)			{	return 0;	}
	if (/^\d+$/)		{	return 1;	}
	else				{	return 0;	}
}

#returns true if parameter is valid email
sub isValidEmail {
    my $email  = shift;
    if ($email =~ /^.+@.+\.\w{2,4}$/) {
        return 1;
    }
return 0;
}

sub isValidName {
    use locale;
    my $name = shift;
    if ($name =~ /^[^\d_!?]+$/) {
        return 1;
    }
return 0;
}


# Вернуть массив параметров из строки.
sub ParseString()
{

	my $string	=	shift;
	my @fields	=	split(/&/, $string);
    my %z		=	();

	foreach(@fields)
	{
		/([^=]+)=(.*)/ && do
		{
			my ($field, $value)	=	($1, $2);
			$z{lc($field)}		=	$value;
		}

	}
	return		%z;
}

1;

