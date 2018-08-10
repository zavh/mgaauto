<?php
function stripGets($url){
	$processedUrl = explode("?",$url);
	return $processedUrl[0];
}

function randPassGen(){
	$chars=array("ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz","0123456789");
	$tempPass = "";
	for($i=0;$i<8;$i++){
		$a = rand(0,2);
		$b = rand(0,(strlen($chars[$a])-1));
		$tempPass .= $chars[$a][$b];
	}
	$tempPass .= $chars[0][rand(0,(strlen($chars[0])-1))];
	$tempPass .= $chars[1][rand(0,(strlen($chars[1])-1))];
	$tempPass .= $chars[2][rand(0,(strlen($chars[2])-1))];
	return $tempPass;
}

function monthUpdated($con, $tdReadable, $status){
	$mrObj = new DbTables($con, "monthreport");
	$idvalue = "'".date("Y-m-01",strtotime($tdReadable))."'";
	$mrObj->updateRecord('update_status', $status, 'report_month', $idvalue);
}
?>
