<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");

	$sql = "";
	$savetodb = 1;
	$mode = 0;
	$editmode = "contenteditable=\"true\"";
	$stylemode = "background:#eee";
	$classmode = "w3-light-gray";
	$pmntpanel = '';
	if(isset($_POST['command'])){
		if($_POST['command'] == 'archive'){
			$inv_id = $_POST['invid'];
			$mode = $_POST['mode'];
			$pmntpanel = getPayments($inv_id, $con, $mode);
			$invdat = getJSONobj(INVOICESTORE."/".$_POST['archive']);
			$var_addrs = $invdat->invaddr;
			$var_invdt = $invdat->invdate;
			$var_sbjct = $invdat->invsub;
			$var_refno = $invdat->invref;
			$var_frwrd = $invdat->invfrd;
			$var_footr = $invdat->invftr;
			$invoicename = $invdat->invname;
			$startdate = $invdat->invfromd;
			$enddate = $invdat->invtod;
			$ctype = $invdat->invctype;
			$name = $invdat->invcname;
			$thead = getTableHeadings($invdat->tabheads);
			$requestid = $invdat->requestids;
			$formatter['inword'] = $invdat->tabsumword;
			$formatter['tbody'] = formatInvoiceTabJSON($invdat->tabvals, $invdat->tabsumpax, $invdat->tabsumam, $invdat->invctype);
			$savetodb = 0;
			if($mode == 2){
				$editmode = '';
				$classmode = '';
				$stylemode = '';
			}
		}
		else
		if($_POST['command'] == 'new'){
			$invdat = getJSONobj("template.json");
			$name = $_POST['name'];
			$var_addrs = $invdat->invaddr;
			$var_invdt = date("d, F, Y");
			$var_sbjct = "Bill against Meet &amp; Assist Services for ".date("F-Y",strtotime($_POST['fromd']));
			$var_frwrd = $invdat->invfrd;
			$var_footr = $invdat->invftr;
			$condition = "";
			if(isset($_POST['fromd']))
				$startdate = $_POST['fromd'];
			else exit;
			if(isset($_POST['tod']))
				$enddate = $_POST['tod'];
			else exit;
			$invoicename = "from-$startdate-to-$enddate-";
			$tables = "`requests`";
			$var_refno = INVOICEREF.date("m/F/Y/",strtotime($_POST['tod']));
			if($_POST['ctype'] == '3'){
				$thead = getTableHeadings($invdat->tabheadsc);
				$invoicename .= "corp-".$_POST['name'];
				$var_refno .= $_POST['name'];
				$tables .= ", `corporate` ";
				$condition = "AND `mode_of_payment` = 3 AND requests.corporate_id=corporate.corporate_id ";
				$ctype = 3;
				if(isset($_POST['name'])){
					$condition .= "AND corporate.corporate_name = '".$_POST['name']."' ";
				}
			}
			if($_POST['ctype'] == '2'){
				$thead = getTableHeadings($invdat->tabheadsb);
				$invoicename .= "bank-".$_POST['name'];
				$var_refno .= $_POST['name'];
				$tables .= ", `bank`";
				$condition = "AND `mode_of_payment` = 2 AND requests.bank_id = bank.bank_id ";
				$ctype = 2;
				if(isset($_POST['name'])){
					$condition .= "AND bank.bank_code = '".$_POST['name']."' ";
				}
			}
			$sql = "SELECT * FROM $tables WHERE `flight_date` BETWEEN '$startdate' AND '$enddate' $condition ORDER BY `flight_date` ASC";
			$reqobj = new DbTables($con, 'request');
			$dr = $reqobj->getSqlResult($sql); //DB Result
			#### Request IDs affected by the invoice ###
			$requestid = array();
			#### Request IDs affected by the invoice ###
			$formatter = formatInvoiceTabDB($requestid, $dr, $ctype); //Invoice Table
		}
		$reqidjvar = "\"".implode("\",\"",$requestid)."\"";
		$invtab = "<table id='invtab'>".$thead.$formatter['tbody']."</table>";

		$invtab .= "<div class='w3-center w3-small w3-row' style='font-weight:bold' id='inv-aminword'>".$formatter['inword']."</div>";
	}
	else
		exit;

function getTableHeadings($thArray){
	$ths = "<tr>";
	for($i=0;$i<count($thArray);$i++){
		$ths .= "<th>".$thArray[$i]."</th>";
	}
	return $ths;
}
function getJSONobj($jsonfile){
	$str = file_get_contents($jsonfile);
	$json = json_decode($str);
	return $json;
}
function formatInvoiceTabJSON($tabdat, $invsumpax, $invsum, $mop){
	$tbody = "";
	for($i=0;$i<count($tabdat);$i++){
		$tbody .= "<tr class='inv-dat'>\n";
		for($j=0;$j<count($tabdat[$i]->rowdata);$j++)
			$tbody .= "<td>".$tabdat[$i]->rowdata[$j]."</td>\n";
		$tbody .= "</tr>\n";
	}
	if($mop == 2)
		$tbody .= "<tr style='font-weight:bold'><td></td><td></td><td></td><td>Total</td><td id='inv-sumpax'>$invsumpax</td><td id='inv-sumam'>$invsum</td><td></td></tr>";
	else if ($mop == 3)
		$tbody .= "<tr style='font-weight:bold'><td></td><td></td><td></td><td>Total</td><td id='inv-sumpax'>$invsumpax</td><td></td><td id='inv-sumam'>$invsum</td></tr>";
	return $tbody;
}
function formatInvoiceTabDB(&$requestid, $dr, $ctype){
	$invsum = 0;
	$invsumpax = 0;
	$f = new NumberFormatter("en_UK", NumberFormatter::SPELLOUT);
	$outputtab['tbody']  = "";

	for($i=0;$i<count($dr);$i++){
		$requestid[$i]=  $dr[$i]['id'];
		$dir = ($dr[$i]['arrival_departure']==0)?'Arr':'Dep';
		$outputtab['tbody'] .= "<tr class='inv-dat'>
						<td>".$dr[$i]['flight_date']."</td>
						<td>$dir</td>
						<td>".$dr[$i]['flight_no']."</td>
						<td>".$dr[$i]['name']."</td>
						<td>".$dr[$i]['no_of_passengers']."</td>";
		if($ctype == 3)
			$outputtab['tbody'] .="<td>".$dr[$i]['amount']/$dr[$i]['no_of_passengers']."</td>
												<td>".$dr[$i]['amount']."</td>
					   </tr>";
		if($ctype == 2)
			$outputtab['tbody'] .="<td>".$dr[$i]['amount']."</td>
												<td>".$dr[$i]['card_no']."</td>
					   </tr>";
		$invsum += $dr[$i]['amount'];
		$invsumpax += $dr[$i]['no_of_passengers'];
	}
	if($ctype == 2)
		$outputtab['tbody'] .= "<tr style='font-weight:bold'><td></td><td></td><td></td><td>Total</td><td id='inv-sumpax'>$invsumpax</td><td id='inv-sumam'>$invsum</td><td></td></tr>";
	if($ctype == 3)
		$outputtab['tbody'] .= "<tr style='font-weight:bold'><td></td><td></td><td></td><td>Total</td><td id='inv-sumpax'>$invsumpax</td><td></td><td id='inv-sumam'>$invsum</td></tr>";
	$outputtab['inword']= "Taka ".ucwords($f->format($invsum))." Only";
	return $outputtab;
}

function getPayments($inv_id, $con, $mode){
	$sql = "SELECT * FROM `payment` WHERE invoice_id=$inv_id";
	$payObj = new DbTables($con, 'payment');
	$r = $payObj->getSqlResult($sql);
	if(count($r)==0) return;
	$paytabheads = array('payment_date'=>'Payment Date', 'payment_amount'=>'Amount','paid_by'=>'Paid By','payment_comment'=>'Comment','user'=>'Record Inserted by','action'=>'Action');
	$headvals = "<tr><th>".implode("</th><th>",array_values($paytabheads))."</th></tr>";
	$columns = array_keys($paytabheads);

	$payTab = "<table style='width:100%' class='w3-table'>".$headvals;
	for($i=0;$i<count($r);$i++){
		$payTab .= "<tr>";
		$r[$i]['action'] =
			"<a
				href='javascript:void(0)'
				onclick='confirmReset(".$r[$i]['payment_id'].",$inv_id,\"".$r[$i]['payment_amount']."\",$mode)'>
				Delete
			</a>";
		for($j=0;$j<count($columns);$j++){
			$payTab .= "<td>".$r[$i][$columns[$j]]."</td>";
		}
		$payTab .= "</tr>";
	}
	$payTab .= "</table>";
	return $payTab;
}
?>
<input type="hidden" id="inv-name" value="<?php echo $invoicename;?>">
<input type="hidden" id="inv-fromd" value="<?php echo $startdate;?>">
<input type="hidden" id="inv-tod" value="<?php echo $enddate;?>">
<input type="hidden" id="inv-ctype" value="<?php echo $ctype;?>">
<input type="hidden" id="inv-cname" value="<?php echo $name;?>">
<page size="A4">
	<?php echo $pmntpanel;?>
<div class="w3-container w3-margin w3-small" style="font-family:'Times New Roman', Times, serif;">
<div class="w3-row w3-center" id="printdiv"><a href="javascript:void(0)" class="nodec" onclick="publishInvoice('<?php echo $savetodb;?>')"> 🖨</a>
</div>
	<div class="w3-margin">
		<div class="w3-row" style="font-weight: bold;">
			<div class="w3-col" style="width:80px">Invoice Date:</div>
			<!-- INVOICE DATE-->
			<div class="w3-rest" <?php echo $editmode?>  id="inv-date" style="<?php echo $stylemode;?>"><?php echo $var_invdt;?></div>
			<!-- INVOICE DATE-->
		</div>
		<div class="w3-row" style="font-weight:bold;margin-top:10px">
			<div class="w3-col" style="width:80px">REF:</div>
			<!-- INVOICE REF-->
			<div class="w3-rest" <?php echo $editmode?>  id="inv-ref" style="<?php echo $stylemode;?>"><?php echo $var_refno;?></div>
			<!-- INVOICE REF-->
		</div>
		<!-- INVOICE CUSTOMER ADDRESSING-->
		<div class="w3-row <?php echo $classmode?>" <?php echo $editmode?> style="margin-top:10px;" id='inv-addressing'><?php echo $var_addrs;?></div>
		<!-- INVOICE CUSTOMER ADDRESSING-->
		<div class="w3-row" style="font-weight:bold;margin-top:10px">
			<div class="w3-col" style="width:80px">Subject:</div>
			<!-- INVOICE SUBJECT-->
			<div class="w3-rest" <?php echo $editmode?> id="inv-sub" style="text-decoration:underline;<?php echo $stylemode;?>"><?php echo $var_sbjct;?></div>
			<!-- INVOICE SUBJECT-->
		</div>
		<!-- INVOICE FORWARDING-->
		<div class="w3-row <?php echo $classmode?>" style="margin-top:10px" <?php echo $editmode?> id="inv-forwarding"><?php echo $var_frwrd;?></div>
		<!-- INVOICE FORWARDING-->
	</div>
</div>
<div class="w3-row w3-tiny w3-margin" style="font-face:Arial">
<?php echo $invtab; ?>
</div>
<div class="w3-container w3-margin w3-small" style="font-family:'Times New Roman', Times, serif;">
	<div class="w3-margin">
		<!-- INVOICE FOOTER-->
		<div class="w3-row <?php echo $classmode?>" style="margin-top:10px" <?php echo $editmode?> id='inv-footer'><?php echo $var_footr;?></div>
		<!-- INVOICE FOOTER-->
	</div>
</div>
</page>
<style>
body {
    //background: url("<?php echo IMAGEFOLDER;?>/paid-rectangle-stamp-4.png") no-repeat right top;
}
table#invtab{
	width:100%;
}
table#invtab,tr,td,th{
    border: 1px solid #222;
    border-collapse:collapse;
}
@page {
    margin-top: 1.5in;
    margin-bottom: 5cm;
 }
@media print {
    body {
		margin-top: 0px; margin-bottom: 50mm;
		margin-left: 0mm; margin-right: 0mm;
		}
	#printdiv {
        display :  none;
    }
	header nav, footer {
	display: none;
}
}
</style>
<script>
	var reqidjvar = [<?php echo $reqidjvar;?>];
	var iam = 'external';
</script>
<script src="<?php echo JSDIR;?>/crudinvoice.js?version=0.1.0"></script>
<?php
	include(TEMPLATEDIR."/footer.php");
?>
