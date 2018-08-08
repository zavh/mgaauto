<?php

$invObj = new DbTables($con, 'invoice');

$tabheads = "<table class='w3-table-all'>
				<tr><th>Invoice Ref</th><th>Paid On</th><th>Action</th></tr>";

$sql = "SELECT inv_id, inv_ref, inv_raw_dat, inv_paid_on from `invoice` WHERE inv_paid_on IS NOT NULL ORDER BY inv_paid_on DESC LIMIT 5 ";
$paidInvRes = $invObj->getSqlResult($sql);
$paidInvTab = $tabheads;
$paidInvTab .= getPaidInvoiceList($paidInvRes, 'corporate_name');
$paidInvTab .= "</table>";


function getPaidInvoiceList($arr, $code){
	$upTab ="";
	for($i=0;$i<count($arr);$i++){
		$upTab .= "<tr>
						<td><a href='javascript:void(0)' onclick='viewInvoice(\"".$arr[$i]['inv_raw_dat']."\")'>".$arr[$i]['inv_ref']."</a></td>
						<td>".$arr[$i]['inv_paid_on']."</td>
						<td id='invid-".$arr[$i]['inv_id']."' style='position:relative'>
							<a 
								href='javascript:void(0)' class='nodec' 
								onclick=\"deleteInvoice('".$arr[$i]['inv_id']."','".$arr[$i]['inv_ref']."', 'reset')\"
								title='Reset Payment Date'>&#10060;</a>
						</td>";	
	}
	return $upTab;
}
?>