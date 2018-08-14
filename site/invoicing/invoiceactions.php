<?php
print_r($_POST);
if(!isset($_POST['command']) || !isset($_POST['archive'])) exit();
else {
	$command = $_POST['command'];
	$id = $_POST['archive'];
}
require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
require_once(TEMPLATEDIR."/header.php");
require_once(UTILSDIR."/commons.php");

if($command == 'delete'){
	//First : Get the json filename for Deleting
	//Second: Reset the requests those have been marked as belonging to the invoice id`
	//Third : Raise the month update table flag that performance has been changed
	//Fourth: Delete the invoice record from invoice table
	//Fifth : Delete the archived json file
	$invObj = new DbTables($con, 'invoice');
	//First stage: getting the json filename
	$archiveName = $invObj->valueLookUp(array('inv_raw_dat'), $id, 'inv_id');

	$reqObj = new DbTables($con, 'requests');
	//Second stage: update the request table
	echo "Request Result Update for invoice id:$id - ".$reqObj->updateRecord('invoice',0,'invoice',$id);

	//Third stage: Raise Month Update Flag
	raiseMonthUpdateFlag($id, $invObj, $con);
	//Fourth stage: Delete from invoice table
	echo "Delete Invoice status: ".$invObj->deleteRecord('inv_id', $id);
	print_r($archiveName);
	try{
		//Fifth stage: Delete the stored json archive
		unlink(INVOICESTORE."/".$archiveName[0]['inv_raw_dat']);
		echo "Deleting : ".INVOICESTORE.$archiveName[0]['inv_raw_dat'];
	}
 catch(Exception $e){echo $e-getMessage();}
}
if($command == 'paydateupdate'){
	$paydate = $_POST['paydate'];
	$invObj = new DbTables($con, 'invoice');
	echo "Update Invoice status: ".$invObj->updateRecord('inv_paid_on', $paydate, 'inv_id', $id);

	$reqObj = new DbTables($con, 'requests');
	"Request Result Update for invoice id:$id - ".$reqObj->updateRecord('paid',1,'invoice',$id);

	//Raise Month Update Flag
	raiseMonthUpdateFlag($id, $invObj, $con);
}
if($command == 'reset'){
	$invObj = new DbTables($con, 'invoice');
	echo "Update Invoice status: ".$invObj->setNull('inv_paid_on', 'inv_id', $id);

	$reqObj = new DbTables($con, 'requests');
	"Request Result Update for invoice id:$id - ".$reqObj->updateRecord('paid',0,'invoice',$id);

	//Raise Month Update Flag
	raiseMonthUpdateFlag($id, $invObj, $con);
}
require_once(TEMPLATEDIR."/footer.php");
header ('Location:'.$_SERVER['HTTP_REFERER']);

function raiseMonthUpdateFlag($id, $invObj, $con){
	$field = array("inv_from_date");
	$invdate = $invObj->valueLookUp($field, $id, 'inv_id');//Get Invoice period for month update
	monthUpdated($con, $invdate[0]['inv_from_date'], '1');
}
?>
