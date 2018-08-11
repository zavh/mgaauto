<?php
require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
require_once(TEMPLATEDIR."/header.php");

//print_r($_POST);
$obj = json_decode($_POST["invdat"], false);

$obj->invdate = $_POST['invdate'];
$obj->invref = $_POST['invref'];
$obj->invaddr = $_POST['invaddr'];
$obj->invsub = $_POST['invsub'];
$obj->invfrd = $_POST['invfrd'];
$obj->invftr = $_POST['invftr'];

$formattedData = json_encode($obj);

$filename = $obj->invname.".json";
if($_POST['invsav']){
	$invArr['inv_ref'] = $_POST['invref'];
	$invArr['inv_raw_dat'] = $filename;
	$invArr['inv_ctype'] = $obj->invctype;
	if($obj->invctype == 2){
		$cObj = new DbTables($con, 'corporate');
		$ctype_id = $cObj->idLookUp('corporate_name',$obj->invcname,'corporate_id');
	}
	else if($obj->invctype == 3){
		$cObj = new DbTables($con, 'bank');
		$ctype_id = $cObj->idLookUp('bank_code',$obj->invcname,'bank_id');
	}
	$invArr['inv_from_date'] = $obj->invfromd;
	$invArr['inv_to_date'] = $obj->invtod;
	$invArr['inv_ctype_id'] = $ctype_id;
	$now = date("Y-m-d H:i:s");
	$invArr['inv_created'] = $now;
	$invArr['inv_modified'] = $now;

	$invObj = new DbTables($con, 'invoice');
	$lid = $invObj->insertRecord($invArr);

	$reqObj = new DbTables($con, 'requests');
	for($i=0;$i<count($obj->requestids);$i++){
		$reqObj->updateRecord('invoice', $lid, 'id', $obj->requestids[$i]);
	}
}
//open or create the file
$handle = fopen(INVOICESTORE."/".$filename,'w+');

//write the data into the file
fwrite($handle,$formattedData);

//close the file
fclose($handle);

require_once(TEMPLATEDIR."/footer.php");
?>