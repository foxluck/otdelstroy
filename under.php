<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600');
header('X-Powered-By:http://vkreative.ru');

define ('CREATE_YEAR',"2010"); 
if (CREATE_YEAR==date("Y")) $copy_y=CREATE_YEAR;
else $copy_y=CREATE_YEAR." - ".date("Y");
define ('COPYRIGHT',"����������� ��������� <a href=\"http://vkreative.ru/\" target=\"_blank\" style=\"color:black;\">��������</a>&nbsp;&copy;&nbsp;".$copy_y.""); // Copyright

print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
print "<HTML lang=\"ru\">\n";
print "<HEAD>\n";

print "<TITLE>��������-������� OtdelStroy31</TITLE>\n";

print "</HEAD>\n";

?>

<body>
<table width="100%" border="0">
<tr>
<td align="center">
<h2>�.�.�.�.<br><h5>���������� �������� ������������ �������</h5></h2>

<br>
���������� ����� - �������� <a href="http://k-e-d-r.ru">��.�.�.�.�</a><br>
�� ���� �������� ���������� <a href="mailto:info@k-e-d-r.ru">info@k-e-d-r.ru</a><br>
<br><br>
<img src="lamp.gif">

</td>
</tr>
</table>

</body>

<?php

print "</HTML>\n";
?>
