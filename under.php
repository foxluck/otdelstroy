<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 3600');
header('X-Powered-By:http://vkreative.ru');

define ('CREATE_YEAR',"2010"); 
if (CREATE_YEAR==date("Y")) $copy_y=CREATE_YEAR;
else $copy_y=CREATE_YEAR." - ".date("Y");
define ('COPYRIGHT',"Разработано компанией <a href=\"http://vkreative.ru/\" target=\"_blank\" style=\"color:black;\">«Креатив»</a>&nbsp;&copy;&nbsp;".$copy_y.""); // Copyright

print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n";
print "<HTML lang=\"ru\">\n";
print "<HEAD>\n";

print "<TITLE>Интернет-магазин OtdelStroy31</TITLE>\n";

print "</HEAD>\n";

?>

<body>
<table width="100%" border="0">
<tr>
<td align="center">
<h2>К.Е.Д.Р.<br><h5>Креативное Единство Дизайнерских Решений</h5></h2>

<br>
Разработка сайта - Компания <a href="http://k-e-d-r.ru">«К.Е.Д.Р.»</a><br>
По всем вопросам обращаться <a href="mailto:info@k-e-d-r.ru">info@k-e-d-r.ru</a><br>
<br><br>
<img src="lamp.gif">

</td>
</tr>
</table>

</body>

<?php

print "</HTML>\n";
?>
