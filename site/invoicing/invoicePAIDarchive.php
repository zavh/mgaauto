<?php
if(!isset($_SERVER['HTTP_REFERER']) && ((count(get_included_files()) ==1))){
		header ('Location:index.php');
		exit;
}
else if(isset($_SERVER['HTTP_REFERER']) && ((count(get_included_files()) ==1))){
	require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	require_once(UTILSDIR."/essentials_open.php");
}

if(isset($_GET['search'])){
	if($_GET['search']=='byref'){
		$condition = '';
		$criteria = json_decode($_GET['criteria'],true);
		for($i=0;$i<count($criteria);$i++){
			$condition .= "AND inv_ref LIKE '%".$criteria[$i]."%' ";
		}
		$sql = getRefSql($condition);
		$body = getBody($con, $sql);
	}
	else 	if($_GET['search']=='bydate'){
		$dateString = $_GET['datestring'];
		$searchDate = date("Y-m",strtotime($dateString));
		if($searchDate == '1970-01'){
			$body = getErrMsg("$dateString is not a valid Month/Period");
		}
		else {
			$startd = date("Y-m-01",strtotime($dateString));
			$endd = date("Y-m-t",strtotime($dateString));
			$sql = "SELECT
								inv_id,
								inv_raw_dat,
								invoice.inv_ctype,
								invoice.inv_ref,
								payment.payment_date as inv_paid_on,
								payment.`payment_amount` as inv_amount
							FROM `payment`, `invoice`
							WHERE payment.invoice_id=invoice.inv_id
							AND payment_date BETWEEN '$startd' AND '$endd'";
			$body = getBody($con, $sql);
		}
	}
	$footer = getFooter();
}
else {
	$condition = 'ORDER BY inv_paid_on DESC LIMIT 50';
	$sql = getRefSql($condition);
	$body = getBody($con, $sql);
	$footer = '';
}

echo $body;
echo $footer;

//echo $paidInvTab;
//echo $resetButton;

function getRefSql($condition){
	$sql = "SELECT inv_id, inv_ref, inv_raw_dat, inv_ctype, inv_paid_on, inv_amount from `invoice` WHERE inv_paid_on IS NOT NULL $condition";
	return $sql;
}

function getBody($con, $sql){
	$invObj = new DbTables($con, 'invoice');

	$tabheads = "<table style='border-collapse:collapse;width:100%' class='w3-black'>
					<tr class='w3-light-gray' style='text-align:left;border-top:3px solid rgba(255,255,255,0.05)'><th>Invoice Ref</th><th>Paid On</th><th>Amount</th></tr>";


	$paidInvRes = $invObj->getSqlResult($sql);
	$paidInvTab = $tabheads;
	$paidInvTab .= getPaidInvoiceList($paidInvRes);
	$paidInvTab .= "</table>";

	return $paidInvTab;
}

function getFooter(){
	$resetButton = "<div class='w3-center' style='margin:4px 0px 4px 0px'>
										<a
											href='javascript:void(0)'
											onclick=\"ajaxInvFunction('resetSearchArea', '', 'paidInvoiceDiv')\"
											class='w3-gray nodec dot'>
											X
										</a>
									</div>";
	return $resetButton;
}

function getErrMsg($msg){
	return "<div class='w3-center w3-tiny' style='border-bottom:1px solid rgba(255,255,255,0.2)'>$msg</div>";
}
function getPaidInvoiceList($arr){
	$upTab ="";
	for($i=0;$i<count($arr);$i++){
		if($arr[$i]['inv_ctype'] == 2)
			$textClass = 'w3-text-lime';
		else if($arr[$i]['inv_ctype'] == 3)
					$textClass = 'w3-text-light-blue';
		$upTab .= "<tr style='border-bottom:1px solid rgba(255,255,255,0.2)' class='$textClass'>
								<td><a
											href='javascript:void(0)'
											onclick='viewInvoice(\"".$arr[$i]['inv_raw_dat']."\", \"2\", \"".$arr[$i]['inv_id']."\")'
											class='nodec'>".
											$arr[$i]['inv_ref']."
										</a></td>
								<td>".$arr[$i]['inv_paid_on']."</td>
								<td id='invid-".$arr[$i]['inv_id']."' style='position:relative'>".$arr[$i]['inv_amount']."</td>
							</tr>";
	}
	return $upTab;
}
?>
