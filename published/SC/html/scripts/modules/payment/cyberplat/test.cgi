#!C:/perl/bin/perl -w

$orderid=time();
$orderid = substr ($orderid, 0, 8);

print "Content-type: text/html\n\n";
print
'
<HTML>
<HEAD>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<META HTTP-EQUIV="Expires" CONTENT="Mon, 06 Jan 1990 00:00:01 GMT">
<TITLE>Sample Shop Page</TITLE>
<style>
th
{
	font-family: arial; font-size: 9pt;
}
td
{
	font-family: arial; font-size: 9pt;
}
</style>
</HEAD>
<BODY bgcolor=#ffffff>
<form action=cybercrd.cgi method=post id=form1 name=form1>
<center>
<table border=0 cellpadding=0 cellspacing=0 bgcolor="#2d6294" width=400>
<tr>
<td>
<table border=0 cellpadding=3 cellspacing=1 width="100%">
<tr>
<td align="center" colspan=2 bgcolor="#2d6294"><font size=2><font color="#ffffff">&nbsp;<b>�������� �������</b></font></td>
</tr>
<tr>
<td colspan=2 bgcolor="#ffffff"><font size=2>
��� �������� ��������� ������ ��������.
<ol>
<li>�������� �����, ������� ���� ���������.
<li>�������� ������.
<li>������� ������ <b>������</b>.
</ol>
</font></td>
</tr>
<tr>
<td colspan=2 bgcolor="#6da2d4"><font size=2><font color="#ffffff">&nbsp;<b>�����</b></font></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>OrderID (*):</td>
<td bgcolor="#ffffff"><input type=text name=orderid value="'.$orderid.'"></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>����������<br>�������:</td>
<td bgcolor="#ffffff"><input type=text name=paymentdetails value="������ ������ #'.$orderid.'" ></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>����� ������(*):</td>
<td bgcolor="#ffffff"><input type=text name=amount value="4.00" maxlength=6 size=6></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>������(*):</td>
<td bgcolor="#ffffff"><select name=currency  style="width:120"><option value=1>$ �������</option><option selected value=2>� �����</option><option value=3>EURO</option></select></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>��� ���������<BR>�����</td>
<td bgcolor="#ffffff"><select name=cardtype style="width:120">
<option  selected value=""> </option>
<option  value="VI"> Visa </option><option value="EU"> EuroCard/MasterCard </option>
<option value="DC"> Diners Club </option></select></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>TerminalID:</td>
<td bgcolor="#ffffff"><input type=text name=terminal></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>����</td>
<td bgcolor="#ffffff"><select name=language style="width:120"><option  selected value=ru> rus </option><option value=eng> eng </option></select></td></tr>
</table>
</td></tr>
</table>
<table border=0 cellpadding=0 cellspacing=0 bgcolor="#2d6294" width=400>
<tr>
<td>
<table border=0 cellpadding=3 cellspacing=1 width="100%">
<tr>
<td colspan=2 bgcolor="#6da2d4"><font size=2><font color="#ffffff">&nbsp;<b>������ � ����������</b></font></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>E-mail(*):</td>
<td bgcolor="#ffffff"><input type=text name=email value="support@cyberplat.com"></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>�������:</td>
<td bgcolor="#ffffff"><input type=text name=phone value="745-4060"></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>�����:</td>
<td bgcolor="#ffffff"><input type=text name=address value="Moscow, Kutuzovsky pr. 12"></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>�������(*):</td>
<td bgcolor="#ffffff"><input type=text name=lastname value="Ivanov"></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>���(*):</td>
<td bgcolor="#ffffff"><input type=text name=firstname value="Ivan"></td></tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>��������:</td>
<td bgcolor="#ffffff"><input type=text name=middlename value="Ivanovich"></td>
</tr>
<tr>
<td bgcolor="#ecf2f8"><font size=2>���������������<br>� CyberPlatPay?</td>
<td bgcolor="#ffffff"><input type=checkbox name=registered></td></tr>
<tr>
	<td colspan=2 align=center bgcolor="#2d6294"><input type=submit value="  ������ "></td>
</tr>
</table>
</td>
</tr>
</table>
</form>

<table border=0 cellpadding=0 cellspacing=0 bgcolor="#ffffff" width=400>
<tr>
<td>
<p align=justify> <b>��������!</b> �� ��������� ���� �� �������� �� ������ �������� 
<a href="http://www.cyberplat.com" target="_blank">Cyberplat.com</a> ��� ���������� � �������� 
���������� � ����� �������� �����. ��� ������ ����� �������� �� 
����������� ��������� (SSL) ��������������� �� ��������������� ������ � �������� 
������������ ��� ��������. ������ ����� ���������� ��������
<a href="https://digitalid.verisign.com/" target="_blank">Verisign.com</a>

<p align=justify> <b>Attention!</b> At the next step you will be moved to the 
<a href="http://www.cyberplat.com" target="_blank">Cyberplat.com</a> server to fill out the form and 
send your credit card information.  This information will be passed directly 
to the authorization server by secure protocol (SSL) and will not be accessible for the shop. The 
server has a certificate by  
<a href="https://digitalid.verisign.com/" target="_blank">Verisign.com</a>
</td>
</tr>
</table>

</BODY>
</HTML>
';
