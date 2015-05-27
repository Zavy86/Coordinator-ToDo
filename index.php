<?php
/* -[ Redirect ]------------------------------------------------------------- */
$alert=$_GET['alert'];
if(isset($alert)){$alert="?alert=".$alert;}
$act=$_GET['act'];
if(isset($act)){$act="&act=".$act;}
header("location: todo_list.php".$alert.$act);
?>