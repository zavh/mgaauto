<?php
if(file_exists(INVOICESTORE)){
	$invfiles = scandir(INVOICESTORE);

	//print_r($invfiles);
	for($i=0;$i<count($invfiles);$i++){
		//if()
	}
}

$invObj = new DbTables($con, 'invoice');

$tabheads = "<table class='w3-table-all'>
				<tr><th>Invoice Ref</th><th>Name</th><th>Created On</th><th>Action</th></tr>";

$sql = "SELECT * FROM `invoice`, `corporate` WHERE `inv_ctype` = 2 AND invoice.inv_ctype_id = corporate.corporate_id AND inv_paid_on is NULL";
$corpRes = $invObj->getSqlResult($sql);
$upCorpTab = $tabheads;
$upCorpTab .= getInvoiceList($corpRes, 'corporate_name');
$upCorpTab .= "</table>";


$sql = "SELECT * FROM `invoice`, `bank` WHERE `inv_ctype` = 3 AND invoice.inv_ctype_id = bank.bank_id AND inv_paid_on is NULL";
$bankRes = $invObj->getSqlResult($sql);
$upBankTab = $tabheads;
$upBankTab .= getInvoiceList($bankRes, 'bank_code');
$upBankTab .= "</table>";

function getInvoiceList($arr, $code){
	$upTab ="";
	for($i=0;$i<count($arr);$i++){
		$upTab .= "<tr>
						<td><a href='javascript:void(0)' onclick='viewInvoice(\"".$arr[$i]['inv_raw_dat']."\")'>".$arr[$i]['inv_ref']."</a></td>
						<td>".$arr[$i][$code]."</td>
						<td>".$arr[$i]['inv_created']."</td>
						<td id='invid-".$arr[$i]['inv_id']."' style='position:relative'>
							<a 
								href='javascript:void(0)' class='nodec' 
								onclick=\"deleteInvoice('".$arr[$i]['inv_id']."','".$arr[$i]['inv_ref']."', 'delete')\"
								title='Delete Invoice'>&#10060;</a>
							<a 
								href='javascript:void(0)' class='nodec' 
								onclick=\"markAsPaidInvoice(this, '".$arr[$i]['inv_id']."')\"
								title='Mark Invoice as Paid'>&#x274E;</a>
								<div class='w3-card-4 w3-center payday' id='payid-".$arr[$i]['inv_id']."'>
									<form action='invoiceactions.php' method='POST' >
										Paid on: &nbsp;<input type='date' name='paydate'>
										<input type='submit' value='go'>
										<input type='hidden' name='command' value='paydateupdate'>
										<input type='hidden' name='archive' value='".$arr[$i]['inv_id']."'>
									</form>
								</div>
						</td>";	
	}
	return $upTab;
}
?>