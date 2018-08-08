<?php
require_once("../classes/class_db_tables.php");
if(file_exists("utils/db_read.php"))
	include("utils/db_read.php");
else if(file_exists("db_read.php"))
	include("db_read.php");

if(isset($_GET['newassgn']) && isset($_GET['id'])){
	$recobj =  new DbTables($con, 'requests');
	$result = $recobj->updateRecord("assigned_to", $_GET['newassgn'], 'id', $_GET['id']);
	if($result)
		echo $_GET['newassgn'];
	else echo "false";
}



if(file_exists("utils/db_close.php"))
	include("utils/db_close.php");
else if(file_exists("db_close.php"))
	include("db_close.php");
?>