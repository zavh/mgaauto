<?php
$rustart = getrusage();
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");
	include(CLASSDIR."/class_dboard_create.php");
	include(UTILSDIR."/commons.php");

	if(isset($_POST['targetmonth'])){
		$_SESSION['performancemonth'] = $_POST['targetmonth'];
	}
	else {
		if(!isset($_SESSION['performancemonth']))
			$_SESSION['performancemonth'] = date("Y-m-d");
	}
	$td = $_SESSION['performancemonth'];
	$tdReadable = date("M Y",strtotime($td));
	$tdYYYY = date("Y",strtotime($td));
	$startdate = date("Y-m-01",strtotime($td));
	$enddate = date("Y-m-t", strtotime($td));
	$targetfile = PERFORMANCEREPORTDIR."/"."monthreport-".date("Y-m",strtotime($tdReadable)).".json";
	$reportExists = true;
	$msg = '';

	if($tdYYYY == '1970'){
		$reportExists = false;
		$msg = "<strong>'$td'</strong> is not a valid date or format. <br>
				Please search again.<br>
				Valid search formats are: 2018-07, July 2018, 2018 July<br>
				You can also simply put the name to get the report of the month of THIS year. For example, putting Jan or January will provide reports of January ".date("Y");
	}
	else if(file_exists($targetfile)){
		if(isset($_POST['dbload'])){
			if($_POST['dbload']){
				monthUpdated($con, $td, 1);
			}
		}
		//Check if there are any updates
		$mrObj = new DbTables($con, "monthreport");
		$field = array('update_status');
		$value = date("Y-m-01",strtotime($tdReadable));
		$id = "report_month";
		$descision = $mrObj->valueLookUp($field, $value, $id);

		if($descision[0]['update_status'] == 1){
			//Update status 1 indicates there are updates to the month data
			//In such case, whole month will be read from DB and new JSON file will be written
			$rd = monthArrayDB($con, $startdate, $enddate);
			$monthSummary = genFromDb($rd, $tdReadable, $con);
			monthUpdated($con, $tdReadable, 0);
		}
		else
		//Start populating data
		$monthSummary = getJSONobj($targetfile);
	}
	else{
		// Start reading DB, create JSON file and send to read the newly created JSON
		$rd = monthArrayDB($con, $startdate, $enddate);
		if(count($rd) == 0){
			$reportExists = false;
			$msg = "Your search criteria $td is converted into $tdReadable.<br>No record exists for that period.";
		}
		else{ //MAIN ENGINE FOR DB
			//Generating month summary
			$monthSummary = genFromDb($rd, $tdReadable, $con); //Generating month summary
			//Registering the newly generated repoort in DB with an update_status of 0
			registerMonthData($con, $tdReadable); //update_status 0 indicates no update
		}
	}
?>
<script src="<?php echo JSDIR;?>/Chart.bundle.min.js"></script>

<div style="height:100vh" class="w3-gray" >
<!-- Top Panel Starts-->
<?php
	$pagetitle = "Monthly Performance Dashboard";
	$menuitems[0]['classes']  = "monthreportsearch";
	$menuitems[0]['details']  = "<form method=\"POST\" action=\"\" autocomplete=\"off\">";
	$menuitems[0]['details'] .= "<span>Search for Report: </span>";
	$menuitems[0]['details'] .= "<div class=\"autocomplete w3-gray\">";
	$menuitems[0]['details'] .= " <input type=\"text\" value=\"\" id=\"reportmonth\" size=\"15\" name=\"targetmonth\" placeholder=\"ex:Jul 2018\"> ";
	$menuitems[0]['details'] .= "</div>";
	$menuitems[0]['details'] .= " <input type=\"submit\" value=\"go\"> ";
	$menuitems[0]['details'] .= "</form>";

	$menuitems[1]['classes']  = 'w3-center';
	$menuitems[1]['details']  = "<div>";
	$menuitems[1]['details'] .= "<a href='#' class='dot w3-pale-blue  w3-center nodec' style='font-size:8px;'>&#10094;</a>&nbsp;&nbsp;";
	$menuitems[1]['details'] .= "<a href='javascript:void(0)' class='dot w3-yellow w3-center nodec' style='font-size:8px' onclick='setDBFlag()'>D</a>&nbsp;&nbsp;";
	$menuitems[1]['details'] .= "<a href='#' class='dot w3-orange w3-center nodec' style='font-size:8px'>&#x276F;</a>";
	$menuitems[1]['details'] .= "</div>";
	include(TEMPLATEDIR."/topmenu.php");
?>
<!-- Top Panel Ends-->
<!-- Main Panel Wrapper -->

<?php
	if($reportExists)
		include(TEMPLATEDIR."/monthreport.php");
	else {
		include(TEMPLATEDIR."/monthreporterr.php");
	}
	$ru = getrusage();
	echo "This process used " . rutime($ru, $rustart, "utime") .
	    " ms for its computations\n";
	echo "It spent " . rutime($ru, $rustart, "stime") .
	    " ms in system calls\n";
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
		var jsrepdates = [<?php echo getMonthsOfyear();?>];
		autocomplete(document.getElementById("reportmonth"), jsrepdates);
</script>


<?php
function genFromDb($rd, $tdReadable, $con){
	$dashObj = new DashDat($rd, $tdReadable);
	$monthSummary['invoice'] = 0;
	//echo "<pre>";print_r($dashObj);echo "</pre>";
	//$monthSummary['total'] = $dashObj->recovered[1]['amount'];
	for($i=1;$i<4;$i++){
		$monthSummary['invoice'] +=
			$dashObj->unInvoiced[$i]['amount'] +
			$dashObj->invoiced[$i]['amount'];
	}
	//echo $monthSummary['total'];
	$monthSummary['cash'] = $dashObj->recovered[1]['amount'];
	$monthSummary['total'] = $monthSummary['cash'] + $monthSummary['invoice'];
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

	$streams = array('arrear_spot','arrear_corp','arrear_bank'); //preparing arrear tracking
	$targetMonth = date("Y-m-01",strtotime($tdReadable));
	$aa = getLastMonthArrear($con, $targetMonth);
	if(count($aa)>0)
		$arrearArr = json_decode($aa[0]['arrear'],true);
	else $arrearArr = array('1'=>0,'2'=>0,'3'=>0);

	for($i=1;$i<4;$i++){
		if($i==1) {
			$monthSummary['stream'][1]['amount'] = $dashObj->recovered[1]['amount'];
		}
		else {
			$monthSummary['stream'][$i]['amount'] =
				$dashObj->unInvoiced[$i]['amount'] +
				$dashObj->invoiced[$i]['amount'];
		}

		$monthSummary['stream'][$i]['recovered'] = $dashObj->recovered[$i]['amount'];
		$monthSummary['stream'][$i]['pax'] = $dashObj->paxArr[$i];
		$monthSummary['stream'][$i]['request'] = $dashObj->recArr[$i];
		$monthSummary['stream'][$i]['uninvcount'] = $dashObj->unInvoiced[$i]['count'];
		$monthSummary['stream'][$i]['thisarrear'] = $monthSummary['stream'][$i]['amount'] - $monthSummary['stream'][$i]['recovered'];
		$monthSummary['stream'][$i]['fullarrear'] = $monthSummary['stream'][$i]['thisarrear'] + $arrearArr[$i];
		if(isset($dashObj->clients[$i]))
			$monthSummary['stream'][$i]['noclients'] = count($dashObj->clients[$i]);
		else $monthSummary['stream'][$i]['noclients'] = null;
		$monthSummary['stream'][$i]['raised_invoice'] = count($dashObj->invoices[$i]);
		if(isset($dashObj->invoicePaid[$i]))
			$monthSummary['stream'][$i]['invoice_paid'] = count($dashObj->invoicePaid[$i]);
		else $monthSummary['stream'][$i]['invoice_paid'] = null;
		$monthSummary['stream'][$i]['uninv_records'] = $dashObj->unInvoiced[$i]["count"];
		$monthSummary['stream'][$i]['revgen'] = ($monthSummary['stream'][$i]['amount']/$monthSummary['total'])*100;
		$monthSummary['stream'][$i]['reqgen'] = ($monthSummary['stream'][$i]['request']/$monthSummary['total_rec'])*100;
		$monthSummary['stream'][$i]['paxgen'] = ($monthSummary['stream'][$i]['pax']/$monthSummary['total_pax'])*100;
	}
	$monthSummary['chartlabel'] = $dashObj->getChartLabel();
	$monthSummary['streamMonthDat'] = $dashObj->getChartDat();
	$monthSummary['donut']= $dashObj->getDonut();
	$monthSummary['pie']= $dashObj->getPie();
	$monthSummary['clients']= $dashObj->clients;
	$monthSummary['month']= $targetMonth;

// Saving the data in json format in designated directory
	$formattedData = json_encode($monthSummary);
	$filename = "monthreport-".date("Y-m",strtotime($tdReadable)).".json";
	$handle = fopen(PERFORMANCEREPORTDIR."/".$filename,'w+');
	fwrite($handle,$formattedData);
	fclose($handle);
// Saving completed
	return $monthSummary;
}

function registerMonthData($con, $tdReadable){
	$repository_name = "monthreport-".date("Y-m",strtotime($tdReadable)).".json";
	$report_month = date("Y-m-01",strtotime($tdReadable));
	$mrObj = new DbTables($con, "monthreport");//Month Report Object
	$mrRow = array('report_month' => $report_month, 'repository_name'=>$repository_name,'update_status'=>0);
	$mrObj->insertRecord($mrRow);
}

function monthArrayDB($con, $startdate, $enddate){
	$sql = "SELECT * from requests LEFT JOIN invoice on requests.invoice = invoice.inv_id WHERE requests.flight_date BETWEEN '$startdate' AND '$enddate' ";
	$repObj = new DBTables($con,"requests");
	$rd = $repObj->getSqlResult($sql);
	return $rd;
}

function performanceWidget($ms,$widgetType,$title){
	$revgenClass = array("w3-col w3-amber","w3-col w3-lime","w3-col w3-light-blue");
	echo "	<div class=\"w3-margin w3-dark-gray w3-card-4 w3-round\" style=\"overflow:hidden\">
					<div class=\"w3-row w3-center w3-tiny\" style=\"background:rgba(0,0,0,0.4)\">$title</div>
					<div class=\"w3-row w3-center w3-margin\">";
		for($i=1;$i<4;$i++){
				$perc = $ms[$i][$widgetType];
				$percVal = round($perc)."%";
				if($perc<5)$percVal = "";
				echo "<div class=\"".$revgenClass[$i-1]."\" style=\"width:$perc%\">$percVal</div>";
		}
	echo "</div>
						<div class=\"w3-row w3-center w3-wide\" style=\"margin-bottom:4px;width:100%\">
							<span class=\"w3-amber\">&nbsp;&nbsp;</span>&nbsp;Cash
							<span class=\"w3-lime\">&nbsp;&nbsp;</span>&nbsp;Card
							<span class=\"w3-light-blue\">&nbsp;&nbsp;</span>&nbsp;Corporate
						</div>
					</div>";
}
function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

function getMonthsOfyear(){
	$montharr = array();
	for($i=1;$i<13;$i++){
		$montharr[$i]= "\"".date("M Y",strtotime(date("Y")."-".$i."-01"))."\"";
	}
	$monthstr = implode(",",$montharr);
	return $monthstr;
}
?>
