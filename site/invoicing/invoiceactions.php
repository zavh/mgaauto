<?php
if(!isset($_POST['command']) || !isset($_POST['archive'])) exit();
else {
	$command = $_POST['command'];
	$id = $_POST['archive'];
}
require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
require_once(TEMPLATEDIR."/header.php");
require_once(UTILSDIR."/commons.php");
require_once(CLASSDIR."/class_month_data.php");
################################### DELETION LOGIC ###################################
# First : Get the json filename for Deletion, read it for month update							 #
# Second: Process the Monthupdate. Reverse the updates raised in invprocessor.php 	 #
# Third : Reset the requests those have been marked as belonging to the invoice id`  #
# Fourth: Delete the invoice record from invoice table															 #
# Fifth : Delete the archived json file																							 #
# Sixth: execute month update																												 #
######################################################################################
if($command == 'delete'){
	//First stage: getting the json filename
	$invObj = new DbTables($con, 'invoice');
	$archiveName = $invObj->valueLookUp(array('inv_raw_dat'), $id, 'inv_id');
	//Second stage: Process month update
	//Reading the invoice
	$invArr = getJSONobj(INVOICESTORE."/".$archiveName[0]['inv_raw_dat']);
	//Reading the Month report
	$mdFile = PERFORMANCEREPORTDIR."/monthreport-".date("Y-m",strtotime($invArr['invfromd'])).".json";
	$mdArr = getJSONobj($mdFile);
	$md = new month_data($mdArr);
	$md->setRepository($mdFile);
	$md->invoiceDelete(count($invArr['requestids']), $invArr['invctype']);
	//Third stage: update the request table
	$reqObj = new DbTables($con, 'requests');
	$reqObj->updateRecord('invoice',0,'invoice',$id);
	//Fourth stage: Delete from invoice table
	$invObj->deleteRecord('inv_id', $id);
	try{
		//Fifth stage: Delete the stored json archive
		unlink(INVOICESTORE."/".$archiveName[0]['inv_raw_dat']);
	}
 catch(Exception $e){echo $e-getMessage();}
}
################################### PAYMENT LOGIC ###################################
# First: For partial payment, just update the invoice. do the monthupdate in
# Second: For full payment, update invoice, update request with full paid flag
if($command == 'paydateupdate'){
	$paydate = $_POST['paydate'];
	$arrear = $_POST['inv_arrear'];
	$amount = $_POST['paidamount'];
	$paidby = $_POST['paidby'];
	$comment = $_POST['comment'];
	$fpf = 0; //Full Paid Flag, raised in Stage 2
	$rc = 0; //Record count in the invoice
	$now = date("Y-m-d");
	$invObj = new DbTables($con, 'invoice');
	$m = $invObj->valueLookUp(array('inv_ctype'), $id, 'inv_id');
	$mop = $m[0]['inv_ctype'];

	//Stage 1: Partial Payment
	if($amount<$arrear){
		//Stage 1: prepare partial payment data
		$newVals['inv_arrear'] = $arrear-$amount;
		$newVals['inv_modified'] = $now;
	}
	//Stage 2: Full Payment
	else if($amount==$arrear){
		//Stage 2: prepare full payment data
		$newVals['inv_paid_on'] = $paydate;
		$newVals['inv_arrear'] = '0.00';
		$newVals['inv_modified'] = $now;
		//Stage 2: mark the request table with the said invoice id as paid
		$reqObj = new DbTables($con, 'requests');
		$reqObj->updateRecord('paid',1,'invoice',$id);
		$fpf = 1;
		$rc = $con->affected_rows;
	}
	//Common execution of Stage 1 and Stage 2 : Updating the invoice, mainly the arrear
	$invObj->updateRecords($newVals, 'inv_id', $id);
	//Stage 3: Insert new payment in payment table
	$field = array('inv_from_date');
	$im = $invObj->valueLookUp($field,$id,'inv_id');
	$payObj = new DbTables($con, 'payment');
	$data['invoice_id'] = $id;
	$data['invoice_month'] = $im[0]['inv_from_date'];
	$data['payment_date'] = $paydate;
	$data['payment_amount'] = $amount;
	$data['payment_comment'] = $comment;
	$data['paid_by'] = $paidby;
	$data['user'] = $_SESSION['table_id'];
	$payObj->insertRecord($data);

	//Stage 4:Update month report
	$mdFile = PERFORMANCEREPORTDIR."/monthreport-".date("Y-m",strtotime($data['invoice_month'])).".json";
	$mdArr = getJSONobj($mdFile);
	$md = new month_data($mdArr);
	$md->setRepository($mdFile);
	$md->addPayment($amount, $fpf, $mop, $rc);
	$arrearArr = $md->getArrearArr();
	monthArrearUpdate($con, $md->md['month'], $arrearArr);
	updateConsucutiveMonths($con, $md);
	//echo "<pre>";print_r($md);echo "</pre>";
}
if($command == 'reset'){
	print_r($_POST);
	$payment_id = $_POST['payment_id'];
	$rc = $_POST['req_id_count'];
	$report_month = $_POST['report_month'];
	$amount = $_POST['amount'];
	$mop = $_POST['ctype'];
	$mode = $_POST['mode'];

	$invObj = new DbTables($con, 'invoice');
	$payObj = new DbTables($con, 'payment');
	$mdFile = PERFORMANCEREPORTDIR."/monthreport-".date("Y-m",strtotime($report_month)).".json";
	$mdArr = getJSONobj($mdFile);
	$md = new month_data($mdArr);
	$md->setRepository($mdFile);

	if($mode == 1){
		$arrearArr = $invObj->valueLookUp(array("inv_arrear"), $id, "inv_id");
		$currentArrear = $arrearArr[0]['inv_arrear'];
		$newArrear = $currentArrear + $amount;
		$invObj->updateRecord('inv_arrear',$newArrear,'inv_id',$id);
		$fpf = 0;
	}
	else if($mode == 2){
		$invObj->updateRecord('inv_arrear',$amount,'inv_id',$id);
		$invObj->setNull('inv_paid_on', 'inv_id', $id);
		$reqObj = new DbTables($con, 'requests');
		$reqObj->updateRecord('paid',0,'invoice',$id);
		$fpf = 1;
	}

	$payObj->deleteRecord('payment_id', $payment_id);

	//Execute Month Update
	$md->removePayment($amount, $fpf, $mop, $rc);
	$arrearArr = $md->getArrearArr();
	monthArrearUpdate($con, $md->md['month'], $arrearArr);
	updateConsucutiveMonths($con, $md);
	//echo "<pre>";print_r($md);echo "</pre>";
}
require_once(TEMPLATEDIR."/footer.php");
header ('Location:'.$_SERVER['HTTP_REFERER']);
?>
