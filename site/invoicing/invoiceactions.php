<?php
print_r($_POST);
if(!isset($_POST['command']) || !isset($_POST['archive'])) exit();
else {
	$command = $_POST['command'];
	$id = $_POST['archive'];
}
require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
require_once(TEMPLATEDIR."/header.php");

if($command == 'delete'){
	$invObj = new DbTables($con, 'invoice');
	echo "Delete Invoice status: ".$invObj->deleteRecord('inv_id', $id);

	$reqObj = new DbTables($con, 'requests');
	echo "Request Result Update for invoice id:$id - ".$reqObj->updateRecord('invoice',0,'invoice',$id);
}
if($command == 'paydateupdate'){
	$paydate = $_POST['paydate'];
	$invObj = new DbTables($con, 'invoice');
	echo "Update Invoice status: ".$invObj->updateRecord('inv_paid_on', $paydate, 'inv_id', $id);
	
	$reqObj = new DbTables($con, 'requests');
	"Request Result Update for invoice id:$id - ".$reqObj->updateRecord('paid',1,'invoice',$id);
}
if($command == 'reset'){
	$invObj = new DbTables($con, 'invoice');
	echo "Update Invoice status: ".$invObj->setNull('inv_paid_on', 'inv_id', $id);
	
	$reqObj = new DbTables($con, 'requests');
	"Request Result Update for invoice id:$id - ".$reqObj->updateRecord('paid',0,'invoice',$id);
}
require_once(TEMPLATEDIR."/footer.php");
header ('Location:'.$_SERVER['HTTP_REFERER']);
?>