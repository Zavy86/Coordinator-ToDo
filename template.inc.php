<?php
/* -------------------------------------------------------------------------- *\
|* -[ ToDo - Template ]------------------------------------------------------ *|
\* -------------------------------------------------------------------------- */
include("module.inc.php");
$module_name="todo";
include("../core/api.inc.php");
api_loadModule();
// print header
$html->header(api_text("module-title"),$module_name);
// get objects
//$promotion=api_promotions_promotion($_GET['idPromotion']);
// acquire variables
//$g_idCategory=$_GET['idCategory'];
// build navigation tab
global $navigation;
$navigation=new str_navigation((api_baseName()=="tasks_list.php"?TRUE:FALSE));
// todo list
$navigation->addTab(api_text("nav-list"),"tasks_list.php");
// filters
if(api_baseName()=="tasks_list.php"){
 // status
 $navigation->addFilter("multiselect","status",api_text("filter-status"),array(1=>api_text("task-status-inserted"),2=>api_text("task-status-processing"),3=>api_text("task-status-completed"))); //,4=>api_text("task-status-archived"
 if(api_checkPermission($module_name,"tasks_view_all")){
  // referent
  $filter_account_array=array();
  $contacts=$GLOBALS['db']->query("SELECT accounts_accounts.id,accounts_accounts.name FROM todo_tasks JOIN accounts_accounts ON accounts_accounts.id=todo_tasks.idAccount ORDER BY accounts_accounts.name ASC");
  while($contact=$GLOBALS['db']->fetchNextObject($contacts)){$filter_account_array[$contact->id]=stripslashes($contact->name);}
  $navigation->addFilter("multiselect","idAccount",api_text("filter-account"),$filter_account_array);
 }
 // if not filtered load default filters
 if($_GET['resetFilters']||($_GET['filtered']<>1&&$_SESSION['filters'][api_baseName()]['filtered']<>1)){$_GET['status']=array(1,2);}
}
// operations
if($todo->id){
 $navigation->addTab(api_text("nav-operations"),NULL,NULL,"active");
 if($promotion->typology==1&&$promotion->status==0){$navigation->addSubTab(api_text("nav-sendmail"),"submit.php?act=promotion_sendmail&idPromotion=".$promotion->id,NULL,NULL,(api_checkPermission($module_name,"promotions_edit")?TRUE:FALSE),"_self",api_text("nav-sendmail-confirm",count($promotion->receivers)));}
 if($promotion->status>0){$navigation->addSubTab(api_text("nav-status"),"promotions_status.php?idPromotion=".$promotion->id,NULL,NULL,(api_checkPermission($module_name,"promotions_view")&&$promotion->typology<3?TRUE:FALSE));}
 if(($promotion->typology==1&&$promotion->status==0)||$promotion->status>0){$navigation->addSubTabDivider();}
 $navigation->addSubTab(api_text("nav-export"),"promotions_export.php?idPromotion=".$promotion->id."&letterhead=1",NULL,NULL,(api_checkPermission($module_name,"promotions_view")?TRUE:FALSE),"_blank");
 $navigation->addSubTab(api_text("nav-export-letterhead"),"promotions_export.php?idPromotion=".$promotion->id."&letterhead=0",NULL,NULL,(api_checkPermission($module_name,"promotions_view")&&$promotion->typology<3?TRUE:FALSE),"_blank");
}else{
 $navigation->addTab(api_text("nav-add"),"todo_add.php");
}
// show navigation
$navigation->render();
// check permissions before displaying module
if($checkPermission==NULL){content();}else{if(api_checkPermission($module_name,$checkPermission,TRUE)){content();}}
// print footer
$html->footer();
?>