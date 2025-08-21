<?php
$db=new PDO('sqlite:database/database.sqlite');
$q=$db->query("SELECT key,value,type FROM panel_settings WHERE key LIKE 'stopbot_%'");
foreach($q as $r){echo $r['key'].'='.$r['value'].' ('.$r['type'].")\n";}
