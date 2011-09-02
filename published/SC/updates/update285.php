<?php

//fix repeat locals
$sql ='SELECT id, lang_id, COUNT( * ) as cnt 
FROM SC_local
GROUP BY id, lang_id
HAVING COUNT( * ) >1';
$db_result = mysql_query($sql);
while($row = mysql_fetch_assoc($db_result)){
	$row['cnt'] = intval($row['cnt']);
	--$row['cnt'];
	$fix_query = "DELETE FROM SC_local WHERE id='{$row['id']}' AND lang_id={$row['lang_id']} LIMIT {$row['cnt']}";
	mysql_query($fix_query);
}
mysql_query('ALTER TABLE  `SC_local` ADD PRIMARY KEY (  `id` ,  `lang_id` )');
?>