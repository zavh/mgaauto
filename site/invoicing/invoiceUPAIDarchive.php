<?php
if(file_exists(INVOICESTORE)){
	$invfiles = scandir(INVOICESTORE);

	//print_r($invfiles);
	for($i=0;$i<count($invfiles);$i++){
		//if()
	}
}

$invObj = new DbTables($con, 'invoice');

$tabheads = "<table style='width:100%;border-collapse:collapse'>
				<tr style='text-align:left' class='w3-light-gray'><th>Invoice Ref</th><th>Name</th><th>Created On</th><th>Amount</th><th>Arrear</th><th>Action</th></tr>";

$sql = "SELECT * FROM `invoice`, `corporate` WHERE `inv_ctype` = 3 AND invoice.inv_ctype_id = corporate.corporate_id AND inv_paid_on is NULL";
$corpRes = $invObj->getSqlResult($sql);
$upCorpTab = $tabheads;
$upCorpTab .= getInvoiceList($corpRes, 'corporate_name', 'w3-light-blue');
$upCorpTab .= "</table>";


$sql = "SELECT * FROM `invoice`, `bank` WHERE `inv_ctype` = 2 AND invoice.inv_ctype_id = bank.bank_id AND inv_paid_on is NULL";
$bankRes = $invObj->getSqlResult($sql);
$upBankTab = $tabheads;
$upBankTab .= getInvoiceList($bankRes, 'bank_code', 'w3-lime');
$upBankTab .= "</table>";

function getInvoiceList($arr, $code, $c){
	$upTab ="";
	$deleteUA ="";
	$sumAmount = 0; $sumArrear = 0;
	for($i=0;$i<count($arr);$i++){
		$sumAmount += $arr[$i]['inv_amount']; $sumArrear += $arr[$i]['inv_arrear'];
		if($arr[$i]['inv_amount'] == $arr[$i]['inv_arrear']){
			$deleteUA = "	<a
											href='javascript:void(0)' class='nodec'
											onclick=\"deleteInvoice('".$arr[$i]['inv_id']."','".$arr[$i]['inv_ref']."', 'delete')\"
											title='Delete Invoice'>&#10060;</a>" ;
		}
		else {
			$deleteUA = "	<a
											href='javascript:void(0)' class='nodec'
											onclick=\"alert('There are payments associated with this Invoice. Delete those first.')\"
											title='Deletion not allowed'>🚫</a>" ;
		}
		$upTab .= "<tr style='border-bottom:1px solid rgba(0,0,0,0.1)'>
						<td><a
									href='javascript:void(0)'
									onclick='viewInvoice(\"".$arr[$i]['inv_raw_dat']."\",\"1\",\"".$arr[$i]['inv_id']."\")'>
									".$arr[$i]['inv_ref']."
								</a></td>
						<td>".$arr[$i][$code]."</td>
						<td>".$arr[$i]['inv_created']."</td>
						<td>".$arr[$i]['inv_amount']."</td>
						<td>".$arr[$i]['inv_arrear']."</td>
						<td id='invid-".$arr[$i]['inv_id']."' style='position:relative'>
						$deleteUA
							<a
								href='javascript:void(0)' class='nodec dot $c w3-center'
								onclick=\"markAsPaidInvoice(this, '".$arr[$i]['inv_id']."')\"
								title='Mark Invoice as Paid'>&#10003;</a>
								<div class='w3-card-4 w3-center payday w3-animate-right' id='payid-".$arr[$i]['inv_id']."'>
									<form action='invoiceactions.php' method='POST' id='formid-".$arr[$i]['inv_id']."'>
									<table style='border-collapse:collapse;width:100%'>
										<tr id='input-1-".$arr[$i]['inv_id']."'>
											<td>Paid on: &nbsp;<input type='date' name='paydate' id='paydate-".$arr[$i]['inv_id']."' required></td>
											<td>Amount:<input type='text' name='paidamount' id='paidamount-".$arr[$i]['inv_id']."' size='8' required></td>
											<td><a href='javascript:void(0)' class='dot $c nodec' onclick=\"payStage('".$arr[$i]['inv_id']."', 1,'')\">⇨</a></td>
										</tr>

										<tr id='input-2-".$arr[$i]['inv_id']."' style='display:none'>
											<td><a href='javascript:void(0)' class='dot $c nodec' onclick=\"payStage('".$arr[$i]['inv_id']."', 2,'back')\">⇦</a></td>
											<td>Paid By <input type='text' name='paidby' id='paidby-'".$arr[$i]['inv_id']."></td>
											<td><a href='javascript:void(0)' class='dot $c nodec' onclick=\"payStage('".$arr[$i]['inv_id']."', 2,'next')\">⇨</a></td>
										</tr>

										<tr id='input-3-".$arr[$i]['inv_id']."' style='display:none'>
											<td><a href='javascript:void(0)' class='dot $c nodec' onclick=\"payStage('".$arr[$i]['inv_id']."', 3,'back')\">⇦</a></td>
											<td>Remark <input type='text' name='comment' id='comment-'".$arr[$i]['inv_id']."></td>
											<td><input type='submit' value='go'></td>
										</tr>
									</table>

										<input type='hidden' name='command' value='paydateupdate'>
										<input type='hidden' name='archive' value='".$arr[$i]['inv_id']."'>
										<input type='hidden' name='inv_amount' value='".$arr[$i]['inv_amount']."'>
										<input type='hidden' name='inv_arrear' id='inv_arrear-".$arr[$i]['inv_id']."' value='".$arr[$i]['inv_arrear']."'>
									</form>
								</div>
						</td>";
	}
	$upTab .= "<tr style='text-align:left'><th></th><th colspan='2' class='w3-wide'>Total : </th><th>$sumAmount</th><th>$sumArrear</th><th></th></tr>";
	return $upTab;
}
?>
