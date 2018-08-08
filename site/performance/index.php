<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");
	include(CLASSDIR."/class_dboard_create.php");

	if(isset($_POST['targetmonth'])){
		$td = $_POST['targetmonth'];
	}
	else {
		$td = date("Y-m-d");
	}
	$tdReadable = date("M Y",strtotime($td));
	$tdYYYY = date("Y",strtotime($td));
	$startdate = date("Y-m-01",strtotime($td));
	$enddate = date("Y-m-t", strtotime($td));
	$targetfile = PERFORMANCEREPORTDIR."/".date("Y_m",strtotime($td))."_performnace.json";
	$reportExists = true;
	$msg = '';
	//echo $tdReadable;
	if($tdYYYY == '1970'){
		$reportExists = false;
		$msg = "<strong>'$td'</strong> is not a valid date or format. <br>
				Please search again.<br>
				Valid search formats are: 2018-07, July 2018, 2018 July<br>
				You can also simply put the name to get the report of the month of THIS year. For example, putting Jan or January will provide reports of January ".date("Y");
	}
	else if(file_exists($targetfile)){
		//Start populating data
	}
	else{
		// Start reading DB, create JSON file and send to read the newly created JSON
		$sql = "SELECT * from requests LEFT JOIN invoice on requests.invoice = invoice.inv_id WHERE requests.flight_date BETWEEN '$startdate' AND '$enddate' ";
		$repObj = new DBTables($con,"requests");
		$rd = $repObj->getSqlResult($sql);
		if(count($rd) == 0){
			$reportExists = false;
			$msg = "Your search criteria $td is converted into $tdReadable.<br>No record exists for that period.";
		}
		else{ //MAIN ENGINE
			$dashObj = new DashDat($rd, $tdReadable);

			$monthSummary['total'] = 0;
			for($i=1;$i<4;$i++){
				$monthSummary['total'] += $dashObj->recovered[$i]['amount'] +
																	$dashObj->unInvoiced[$i]['amount'] +
																	$dashObj->invoiced[$i]['amount'];
			}
			//echo $monthSummary['total'];
			$monthSummary['cash'] = $dashObj->recovered[1]['amount'];
			$monthSummary['invoice'] = $monthSummary['total'] - $dashObj->recovered[1]['amount'];
			$monthSummary['recovered'] = $dashObj->recovered[2]['amount'] + $dashObj->recovered[3]['amount'];
			$monthSummary['pending_payment'] = $monthSummary['invoice'] - $monthSummary['recovered'];
			$monthSummary['invoice_raised'] = count($dashObj->invoices[1]) + count($dashObj->invoices[2]) + count($dashObj->invoices[3]);
			$monthSummary['invoice_paid'] = 0;
			if(isset($dashObj->invoicePaid[2]))
				$monthSummary['invoice_paid'] += count($dashObj->invoicePaid[2]);
			if(isset($dashObj->invoicePaid[3]))
				$monthSummary['invoice_paid'] += count($dashObj->invoicePaid[3]);
			$monthSummary['invoice_unpaid'] = $monthSummary['invoice_raised'] - $monthSummary['invoice_paid'];
			$monthSummary['uninv_records'] = $dashObj->uinreccount;
			$monthSummary['total_pax'] = $dashObj->paxArr[1]+$dashObj->paxArr[2]+$dashObj->paxArr[3];
			$monthSummary['total_rec'] = $dashObj->recArr[1]+$dashObj->recArr[2]+$dashObj->recArr[3];

			for($i=1;$i<4;$i++){
				$monthSummary['stream'][$i]['amount'] =
					$dashObj->recovered[$i]['amount'] +
					$dashObj->unInvoiced[$i]['amount'] +
					$dashObj->invoiced[$i]['amount'];
				$monthSummary['stream'][$i]['recovered'] = $dashObj->recovered[$i]['amount'];
				$monthSummary['stream'][$i]['pax'] = $dashObj->paxArr[$i];
				$monthSummary['stream'][$i]['request'] = $dashObj->recArr[$i];
				$monthSummary['stream'][$i]['uninvcount'] = $dashObj->unInvoiced[$i]['count'];
				if(isset($dashObj->clients[$i]))
					$monthSummary['stream'][$i]['noclients'] = count($dashObj->clients[$i]);
				else $monthSummary['stream'][$i]['noclients'] = null;
				$monthSummary['stream'][$i]['raised_invoice'] = count($dashObj->invoices[$i]);
				if(isset($dashObj->invoicePaid[$i]))
					$monthSummary['stream'][$i]['invoice_paid'] = count($dashObj->invoicePaid[$i]);
				else $monthSummary['stream'][$i]['invoice_paid'] = null;
				$monthSummary['stream'][$i]['uninv_records'] = $dashObj->unInvoiced[$i]["count"];
				$monthSummary['stream'][$i]['revgen'] = ($monthSummary['stream'][$i]['amount']/$monthSummary['total'])*100;
			}
			$monthSummary['chartlable'] = $dashObj->getChartLabel();
			$monthSummary['streamMonthDat'] = $dashObj->getChartDat();

//			echo "<pre>";
//			print_r($monthSummary);
//			echo "</pre>";
		}
	}

?>
<script src="<?php echo JSDIR;?>/Chart.bundle.min.js"></script>
	<!-- Top menu starts-->

	<!-- Top menu ends-->
<!-- Top Panel to choose date -->
<div class="w3-row w3-tiny w3-dark-gray">
	<div class="w3-row">
	<div class="w3-half" id="monthreportdashTitle">
		<span style="float:left">&nbsp;<a href="javascript:void(0)" class="nodec" onclick="w3_open()">&#9776;</a>&nbsp;</span>
		Monthly Performance Dashboard
	</div>
	<div class="w3-half" id="monthreportsearch">
		<form method="POST" action="" autocomplete="off">
			Search for Report:
			<div class="autocomplete w3-white">
				<input type="text" value="" id="reportmonth" size="10" name="targetmonth" placeholder="ex:Jul 2018">
			</div>
			<input type="submit" value="go">
		</form>
	</div>
	</div>
</div>
<!-- Top Panel Ends -->

<!-- Main Panel Wrapper -->
<div style="height:80vh" >
<?php
	if($reportExists)
		include(TEMPLATEDIR."/monthreport.php");
	else {
		include(TEMPLATEDIR."/monthreporterr.php");
	}
?>
</div>
<!-- Main Panel Wrapper Ends -->
<?php
	include(TEMPLATEDIR."/footer.php");
?>
<style>
.dashtot{
	border-bottom:1px solid #eee;
	font-weight:bolder;
	font-size:15px;
	font-family:'Arial';
	text-shadow:1px 1px 0 #444;
}
.dashwidgethead{
	background:rgba(0,0,0,0.4);
}
</style>
<script>
		var jsrepdates = ["Jan 2018","Feb 2018","Mar 2018","Apr 2018","May 2018","Jun 2018","Jul 2018","Aug 2018","Sep 2018","Oct 2018","Nov 2018","Dec 2018"];;

		autocomplete(document.getElementById("reportmonth"), jsrepdates);
</script>
