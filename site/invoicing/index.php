<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");
	include(UTILSDIR."/autocompletedata.php");
	include(UTILSDIR."/commons.php");

	$td = date("Y-m-d");
	$startdate = date("Y-m-01");
	$enddate = date("Y-m-t", strtotime($td));

	if(isset($_POST['startdate'])){
		$enddate = $_POST['startdate'];
	}
	if(isset($_POST['enddate'])){
		$enddate = $_POST['enddate'];
	}

	$ctype = -1;
	$sql = "SELECT * FROM `requests`
			LEFT JOIN `bank` on requests.bank_id = bank.bank_id
			LEFT JOIN corporate on requests.corporate_id = corporate.corporate_id
			WHERE `client_type` != '1' AND requests.invoice=0 ORDER BY `flight_date` ASC";
	if(isset($_POST['client_type'])){
		$condition = "";
		$allowinvoice = 'false';
		$tables = "`requests`";

		if($_POST['client_type'] == '0'){
			$ctype = 0;
		}

		if($_POST['client_type'] == '1'){
			$condition = "AND `client_type` = 1 ";
			$ctype = 1;
		}
		if($_POST['client_type'] == '2'){
			$tables .= ", `corporate` ";
			$condition = "AND `client_type` = 2 AND requests.corporate_id=corporate.corporate_id ";
			$ctype = 2;
			if(isset($_POST['corp_id'])){
				$allowinvoice = true;
				$condition .= "AND corporate.corporate_name = '".$_POST['corp_id']."' ";
			}
		}
		if($_POST['client_type'] == '3'){
			$tables .= ", `bank`";
			$condition = "AND `client_type` = 3 AND requests.bank_id = bank.bank_id ";
			$ctype = 3;
			if(isset($_POST['bank_id'])){
				$allowinvoice = true;
				$condition .= "AND bank.bank_code = '".$_POST['bank_id']."' ";
			}
		}
		$sql = "SELECT * FROM $tables WHERE `flight_date` BETWEEN '$startdate' AND '$enddate' $condition";
	}
	###########################MAIN PRESENTER TABLES FORMATTER###########################
	$reqobj = new DbTables($con, 'request');
	$dr = $reqobj->getSqlResult($sql); //DB Result

	require_once("invoicepanel.php");
	$invtab = formatInvoiceTab($dr, $ctype); //Invoice Table
	###########################MAIN PRESENTER TABLES FORMATTER###########################
?>
	<div class="w3-gray w3-row" style="min-height:100vh">
	<!-- Top Menu Starts-->
	<?php
	$menuitems[0]['classes']  = 'w3-center w3-small darkmenu';
	$menuitems[0]['include']  = TEMPLATEDIR."/queryPaidInvoice.php";
	$menuitems[0]['details']  = "<a href='javascript:void(0)' onclick=\"showQuerySelector('down')\" class='nodec'>Query Paid Invoice</a>";

	$menuitems[1]['classes']  = 'w3-center w3-small darkestmenu';
	$menuitems[1]['include']  = TEMPLATEDIR."/partialInvoiceForm.php";
	$menuitems[1]['details']  = "<a href='javascript:void(0)' onclick=\"showReportSelector('down')\" class='nodec'>Partial Invoice</a>";

//	$menuitems[2]['classes']  = 'w3-center w3-small darkestmenu';
//	$menuitems[2]['details']  = "<a href='javascript:void(0)' onclick=\"showArrearSummary('down')\" class='nodec'>Arrear Summary</a>";
	$pagetitle = "Invoice and Payment Management";

	include(TEMPLATEDIR."/topmenu.php");
	//include(TEMPLATEDIR."/partialInvoiceForm.php");
	?>
	<!-- Top Menu Ends-->
	<!--########################### DIV FOR MAIN PRESENTER TABLES STARTS ###########################-->
		<div id="invrecords" style="overflow:hidden;top:8px;position:relative;z-index:1" class=" w3-row">
			<div class='w3-row-padding w3-quarter'>
				<?php echo $invtab['corptab'];?>
				<?php echo $invtab['banktab'];?>
			</div>
			<div class='w3-half w3-row-padding' id='invoicePanel'>
				<?php include("invoiceUPAIDarchive.php")?>
				<!-- Unpaid Corporate Invoice table-->
				<div class="w3-card w3-margin-bottom" style="max-height:50vh;overflow:auto">
					<div class='w3-white w3-round' style='overflow:hidden'>
						<div class="w3-light-blue w3-tiny w3-center">Unpaid Corporate Invoices</div>
						<?php echo $upCorpTab;?>
					</div>
				</div>
				<!-- Unpaid Bank Invoice table-->
				<div class="w3-card w3-margin-bottom" style="max-height:50vh;overflow:auto">
					<div class='w3-white w3-round' style='overflow:hidden'>
						<div class="w3-lime w3-tiny w3-center">Unpaid Bank Invoices</div>
					<?php echo $upBankTab;?>
				</div>
				</div>
			</div>
			<div class='w3-row-padding w3-quarter'>
				<div class='w3-black w3-round-large w3-card-4' style='overflow:hidden'>
					<div class="w3-blue-gray w3-tiny w3-center">Paid Invoices</div>
					<div style="max-height:90vh;overflow:auto" id="paidInvoiceDiv">
						<?php include("invoicePAIDarchive.php")?>
					</div>
			</div>
			</div>
		</div>
	<!--########################### DIV FOR MAIN PRESENTER TABLES ENDS ###########################-->
</div>
<?php
	include(TEMPLATEDIR."/footer.php");
?>
<script src="<?php echo JSDIR;?>/invoice.js?version=0.4"></script>
<script>
		var jscorporates = [<?php echo $corpjvar;?>];
		var jsbanks = [<?php echo $bankjvar;?>];

		autocomplete(document.getElementById("el-3"), jscorporates);
		autocomplete(document.getElementById("el-2"), jsbanks);
</script>
