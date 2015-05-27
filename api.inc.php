<?php
/* -------------------------------------------------------------------------- *\
|* -[ ToDo - API ]----------------------------------------------------------- *|
\* -------------------------------------------------------------------------- */

/**
 * Task object
 *
 * @param mixed $task task id or object
 * @return object task object
 */
function api_todo_task($task){
 // get object
 if(is_numeric($task)){$task=$GLOBALS['db']->queryUniqueObject("SELECT * FROM todo_tasks WHERE id='".$task."'");}
 if(!$task->id){return FALSE;}
 // check and convert
 $task->subject=stripslashes($task->subject);
 $task->description=stripslashes($task->description);
 $task->statusText=api_todo_taskStatusText($task);
 if(!strlen($task->updDate)){$task->updDate=$task->addDate;}
 return $task;
}

/**
 * Task Status
 *
 * @param mixed $task task id or object
 * @param boolean $textOnly return text without icon
 * @param boolean $iconOnly return icon without text
 * @return string textual status
 */
function api_todo_taskStatusText($task,$textOnly=FALSE,$iconOnly=FALSE){
 if(is_numeric($task)){$task=api_todo_task($task);}
 if(!$task->id){return FALSE;}
 switch($task->status){
  case 1:$icon=api_icon("icon-certificate");$text=api_text("task-status-inserted");break;
  case 2:$icon=api_icon("icon-cog");$text=api_text("task-status-processing");break;
  case 3:$icon=api_icon("icon-ok");$text=api_text("task-status-completed");break;
  case 4:$icon=api_icon("icon-folder-open");$text=api_text("task-status-archived");break;
  default:$text=ucfirst(api_text("undefined"));
 }
 if($textOnly){$return=$text;}
  elseif($iconOnly){$return=$icon;}
   else{$return=$icon." ".$text;}
 return $return;
}

/**
 * Task modal window
 *
 * @param mixed $task task id or object
 * @return object task modal window object
 */
function api_todo_taskModal($task){
 if(is_numeric($task)){$task=api_todo_task($task);}
 if(!$task->id){return FALSE;}
 $return=new str_modal("task_modal_".$task->id);
 $return->header($task->subject);
 $return->body(nl2br($task->description));
 $dl_footer=api_link("tasks_edit.php?idTask=".$task->id,api_text("api-task-edit"),NULL,"btn");
 if($task->status<>1){$dl_footer.=api_link("submit.php?act=task_status&status=inserted&idTask=".$task->id,api_text("api-task-inserted"),NULL,"btn btn-info");}
 if($task->status<>2){$dl_footer.=api_link("submit.php?act=task_status&status=processing&idTask=".$task->id,api_text("api-task-processing"),NULL,"btn btn-warning");}
 if($task->status<>3){$dl_footer.=api_link("submit.php?act=task_status&status=completed&idTask=".$task->id,api_text("api-task-completed"),NULL,"btn btn-success");}
 $dl_footer.=api_link("submit.php?act=task_delete&idTask=".$task->id,api_icon("icon-trash icon-white"),api_text("api-task-delete"),"btn btn-danger",FALSE,api_text("api-task-delete-confirm"));
 $return->footer($dl_footer);
 return $return;
}

/**
 * Task Status modal window
 *
 * @param mixed $task task id or object
 * @return object task modal window object
 */
function api_todo_taskStatusModal($task){
 if(is_numeric($task)){$task=api_todo_task($task);}
 if(!$task->id){return FALSE;}
 $return=new str_modal("task_status_modal_".$task->id);
 $return->header($task->subject);
 // build status body dl
 $dl_body=new str_dl("br","dl-horizontal");
 $dl_body->addElement(api_text("api-task-dt-status"),api_todo_taskStatusText($task,TRUE));
 $dl_body->addElement(api_text("api-task-dt-add"),api_text("api-task-dd-add",array(api_account($task->addIdAccount)->name,api_timestampFormat($task->addDate,api_text("datetime")))));
 if($task->updIdAccount<>NULL){$dl_body->addElement(api_text("api-task-dt-upd"),api_text("api-task-dd-upd",array(api_account($task->updIdAccount)->name,api_timestampFormat($task->updDate,api_text("datetime")))));}
 $return->body($dl_body->render(FALSE));
 return $return;
}

/**
 * Tasks
 *
 * @param string $search search query
 * @param boolean $pagination limit query by page
 * @param string $where additional conditions
 * @return object $results array of tasks objects, $pagination pagination object, $query executed query
 */
function api_todo_tasks($search=NULL,$pagination=FALSE,$where=NULL){
 // definitions
 $return=new stdClass();
 $return->results=array();
 // build query
 $query_table="todo_tasks";
 // fields
 $query_fields="todo_tasks.*,accounts_accounts.name as accountName";
 // join
 $query_join=" LEFT JOIN accounts_accounts ON accounts_accounts.id=todo_tasks.idAccount";
 // group
 $query_group=" GROUP BY todo_tasks.id";
 // where
 $query_where="1";
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("status","1","todo_tasks.status");
 $query_where.=" AND ".$GLOBALS['navigation']->filtersParameterQuery("idAccount","1","todo_tasks.idAccount");
 // accounts
 if(!api_checkPermission("todo","tasks_view_all")||!isset($_GET['idAccount'])){
  $query_where.=" AND todo_tasks.idAccount='".api_account()->id."'";
 }else{

 }
 // search
 if(strlen($search)>0){
  $query_where.=" AND ( todo_tasks.subject LIKE '%".$search."%'";
  $query_where.=" OR todo_tasks.description LIKE '%".$search."%' )";
  //$query_where.=" OR todo_tasks.description LIKE '%".api_cleanString($search,"/[^A-Za-zÀ-ÿ0-9-' ]/","FALSENULL")."%' )";
 }
 // conditions
 if(strlen($where)>0){$query_where="( ".$query_where." ) AND ( ".$where." )";}
 // order
 $query_order=api_queryOrder("accounts_accounts.name ASC,todo_tasks.status ASC,todo_tasks.updDate DESC");
 // pagination
 if($pagination){
  $return->pagination=new str_pagination($query_table.$query_join,$query_where.$query_group,$GLOBALS['navigation']->filtersGet());
  // limit
  $query_limit=$return->pagination->queryLimit();
 }
 // build query
 $return->query="SELECT ".$query_fields." FROM ".$query_table.$query_join." WHERE ".$query_where.$query_group.$query_order.$query_limit;
 // execute query
 $results=$GLOBALS['db']->query($return->query);
 while($result=$GLOBALS['db']->fetchNextObject($results)){$return->results[$result->id]=api_todo_task($result);}
 // return objects
 return $return;
}

?>