<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");
	include(UTILSDIR."/autocompletedata.php");

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
		$pagetitle = "Invoice and Payment Management";
		include(TEMPLATEDIR."/topmenu.php");
	?>
	<!-- Top Menu Ends-->
		<!--###########################DIV FOR MAIN PRESENTER TABLES###########################-->
		<div id="invrecords" style="overflow:hidden;top:20px;position:relative;z-index:1" class=" w3-row">
			<div class='w3-row-padding w3-twothird'>
				<?php echo $invtab;?>
			</div>
			<div class="w3-third" id='invoicePanel'>
				<?php include("invoicePAIDarchive.php")?>
				<div class="w3-card w3-margin-right w3-margin-bottom" style="max-height:40vh;overflow:auto"><?php echo $paidInvTab;?></div>
				<?php include("invoiceUPAIDarchive.php")?>
				<div class="w3-card w3-margin-right w3-margin-bottom" style="max-height:40vh;overflow:auto"><?php echo $upCorpTab;?></div>
				<div class="w3-card w3-margin-right w3-margin-bottom" style="max-height:40vh;overflow:auto"><?php echo $upBankTab;?></div>
			</div>
		</div>
		<!--###########################DIV FOR MAIN PRESENTER TABLES###########################-->

		<div class="w3-card-4 w3-display-container report-selector" id="reportform">
			  <!-- Invoice Form Starts-->
			  <form method="POST" action="" autocomplete="off">
				<table style="width:100%;height:100%;" class="w3-display-top-middle w3-tiny">
					<tr><th style="text-align:right;width:40%">From:</th>
						<td>
							<input type="date" name="startdate" class="w3-input" id="startdate" required value="<?php echo $startdate;?>">
						</td>
					</tr>
					<tr><th style="text-align:right">To:</th>
						<td>
							<input type="date" name="enddate" class="w3-input" id="enddate" required value="<?php echo $enddate;?>">
						</td>
					</tr>
					<tr><th style="text-align:right">Select Type:</th>
						<td>
						<select class="w3-select" id="client_type" name="client_type" onchange="invCondition()">
							<option value='0' selected disabled>All</option>
							<option value='2'>Due</option>
							<option value='3'>Card</option>
						</select>
						</td>
					</tr>
					<tr class="spel" id="spel-2" style="display:none">
						<th style="text-align:right">Corporate Name:</th>
						<td>
							<input type="text" class="w3-input els autocomplete" name="corp_id" id="el-2" placeholder="Corporate Name" required disabled size=12>
						</td>
					</tr>
					<tr class="spel" id="spel-3" style="display:none">
						<th style="text-align:right">Bank Name:</th>
						<td>
							<input type="text" class="w3-input els autocomplete" name="bank_id" id="el-3" placeholder="Bank Name" required disabled size=12>
						</td>
					</tr>
					<tr><th style="text-align:right">Record Type:</th>
						<td>
						<select class="w3-select" id="record_type" name="record_type">
							<option value='all'>All</option>
							<option value='invoiced'>Invoiced</option>
							<option value='uninvoiced'>Uninvoiced</option>
						</select>
						</td>
					</tr>
					<tr>
						<td colspan=2 style="text-align:center">
							<input type="button" value="SHOW" class="w3-bar w3-button w3-light-blue" onclick="processInvoice()">
						</td>
					</tr>
				</table>
			  </form>
			  <div class="w3-display-bottom-middle w3-center"><button class="report-selector-toggle" id="selectorshow" value="down" onclick="showReportSelector()">&#8744;</button></div>
			  <!-- Invoice Form Ends-->
		</div>
		<!-- Top Menu Ends-->

	</div>
<?php
	include(TEMPLATEDIR."/footer.php");
?>
<script src="<?php echo JSDIR;?>/invoice.js?version=1.0.2"></script>
<script>
		var jscorporates = [<?php echo $corpjvar;?>];
		var jsbanks = [<?php echo $bankjvar;?>];

		autocomplete(document.getElementById("el-2"), jscorporates);
		autocomplete(document.getElementById("el-3"), jsbanks);
</script>
