<?php
$reportdate = date("Y-m-d");
if(isset($_POST['reportdate'])){

	$reportdate = $_POST['reportdate'];
}

$sql = "SELECT * FROM `requests` WHERE `flight_date`= '$reportdate' ORDER BY `bank_id`, `corporate_id` ";

////////////////////Preparing Spot Table////////////////////
$spotdata = findBalFor($reportdate, $con, 1);
$spottable  = '<table class="w3-hoverable w3-border" style="width:100%">';
$spottable .= '<tr><td class="w3-red">Spot</td><td colspan=4></td></tr>';
$spottable .= '<tr class="w3-sand w3-tiny"><td>BF</td><td></td><td>'.$spotdata['bf']['pax'].'</td><td>'.$spotdata['bf']['am'].'</td><td>'.$spotdata['bf']['am'].'</td></tr>';
$spottable .= '<tr><th></th><th>Name of Client</th><th>No of Pax</th><th>Bill Amount</th><th>Amount Received</th></tr>';
////////////////////Spot Table Prepared////////////////////

////////////////////Preparing Card Table////////////////////
$carddata = findBalFor($reportdate, $con, 3);
$cardtable  = '<table class="w3-hoverable w3-border" style="width:100%">';
$cardtable .= '<tr><td class="w3-red">Bank</td><td colspan=5></td></tr>';
$cardtable .= '<tr class="w3-sand w3-tiny"><td>BF</td><td></td><td>'.$carddata['bf']['pax'].'</td><td>'.$carddata['bf']['am'].'</td><td></td><td>'.$carddata['bf']['am'].'</td></tr>';
$cardtable .= '<tr><th>Name of Bank</th><th>Name of Card Holder</th><th>No of Pax</th><th>Bill Amount</th><th>Amount Received</th><th>Amount Due</th></tr>';
////////////////////Card Table Prepared////////////////////

////////////////////Preparing Corporate Table////////////////////
$corpdata = findBalFor($reportdate, $con, 2);
$corptable  = '<table class="w3-hoverable w3-border" style="width:100%">';
$corptable .= '<tr><td class="w3-red">Corporate</td><td colspan=5></td></tr>';
$corptable .= '<tr class="w3-sand w3-tiny"><td>BF</td><td></td><td>'.$corpdata['bf']['pax'].'</td><td>'.$corpdata['bf']['am'].'</td><td></td><td>'.$corpdata['bf']['am'].'</td></tr>';
$corptable .= '<tr><th>Name of Org</th><th>Name of Client</th><th>No of Pax</th><th>Bill Amount</th><th>Amount Received</th><th>Amount Due</th></tr>';
////////////////////Corporate Table Prepared////////////////////

////////////////////Preparing Entries Table////////////////////
$entrtable = '<table class="w3-table-all" style="width:100%">';
$entrtable .= '<tr><th>Name</th><th>Contact</th><th>Flight</th><th>Date-Time</th><th>Pax</th><th>Dir</th><th>Req</th><th>Amount</th><th>MoP</th><th>Bank</th><th>Type</th><th>Corporate</th><th>Assigned</th></tr>';
////////////////////Entries Table Prepared////////////////////

////////////////////Preparing Summary Table////////////////////
$sumtotalpax = $spotdata['grtotal']['pax'] + $carddata['grtotal']['pax'] + $corpdata['grtotal']['pax'];
$sumtotalamt = $spotdata['grtotal']['am'] + $carddata['grtotal']['am'] + $corpdata['grtotal']['am'];
$sumtotalrec = $spotdata['grtotal']['am'];
$sumtotaldue = $corpdata['grtotal']['am'] + $carddata['grtotal']['am'];

$summarytable  = '<table class="w3-hoverable w3-border" style="width:100%">';
$summarytable .= "<tr class='w3-teal'><th colspan='6'>Up to Date Summary till : $reportdate</th></tr>";
$summarytable .= "<tr><th>Category</th><th>No of Pax</th><th>Bill Amount</th><th>Amount Received</th><th>Amount Due</th></tr>";
$summarytable .= "<tr><td>Spot</td><th>".$spotdata['grtotal']['pax']."</th><td>".$spotdata['grtotal']['am']."</td><td>".$spotdata['grtotal']['am']."</td><td>0</td></tr>";
$summarytable .= "<tr><td>Bank</td><th>".$carddata['grtotal']['pax']."</th><td>".$carddata['grtotal']['am']."</td><td>0</td><td>".$carddata['grtotal']['am']."</td></tr>";
$summarytable .= "<tr><td>Corporate</td><th>".$corpdata['grtotal']['pax']."</th><td>".$corpdata['grtotal']['am']."</td><td>0</td><td>".$corpdata['grtotal']['am']."</td></tr>";
$summarytable .= "<tr class='w3-gray'><th>Total</th><th>$sumtotalpax</th><th>$sumtotalamt</th><th>$sumtotalrec</th><th>$sumtotaldue</th></tr>";
////////////////////Summary Table Prepared/////////////////////
$banks = getBanks($con);
$corps = getCorps($con);
$result = $con->query($sql);
$entries = array();
if($result->num_rows>0){

	while($row=$result->fetch_assoc()){
		if($row['client_type'] == '1')
			$spottable = formatSpot($spottable, $row);
		if($row['client_type'] == '2')
			$corptable = formatCorp($corptable, $row, $corps);
		if($row['client_type'] == '3')
			$cardtable = formatCard($cardtable, $row, $banks);
		$key = $row['flight_date']." ".$row['flight_time']."-".$row['id'];
		$entries[$key] = $row;
	}
	$entrtable = formatEntry($entrtable, $banks, $corps, $entries, $con);
}
$spottable .= '<tr class="w3-tiny"><td>Total</td><td></td><td>'.$spotdata['total']['pax'].'</td><td>'.$spotdata['total']['am'].'</td><td>'.$spotdata['total']['am'].'</td></tr>';
$spottable .= '<tr class="w3-tiny w3-pale-green"><td>Grand Total</td><td></td><td>'.$spotdata['grtotal']['pax'].'</td><td>'.$spotdata['grtotal']['am'].'</td><td>'.$spotdata['grtotal']['am'].'</td></tr>';
$spottable .= '</table>';

$cardtable .= '<tr class="w3-tiny"><td>Total</td><td></td><td>'.$carddata['total']['pax'].'</td><td>'.$carddata['total']['am'].'</td><td></td><td>'.$carddata['total']['am'].'</td></tr>';
$cardtable .= '<tr class="w3-tiny w3-pale-green"><td>Grand Total</td><td></td><td>'.$carddata['grtotal']['pax'].'</td><td>'.$carddata['grtotal']['am'].'</td><td></td><td>'.$carddata['grtotal']['am'].'</td></tr>';
$cardtable .= '</table>';

$corptable .= '<tr class="w3-tiny"><td>Total</td><td></td><td>'.$corpdata['total']['pax'].'</td><td>'.$corpdata['total']['am'].'</td><td></td><td>'.$corpdata['total']['am'].'</td></tr>';
$corptable .= '<tr class="w3-tiny w3-pale-green"><td>Grand Total</td><td></td><td>'.$corpdata['grtotal']['pax'].'</td><td>'.$corpdata['grtotal']['am'].'</td><td></td><td>'.$corpdata['grtotal']['am'].'</td></tr>';
$corptable .= '</table>';
$summarytable .= '</table>';
$entrtable .= '</table>';
echo "<div id='reportcontainer' class='w3-row'>";
echo "<div id='spot' class=' dayreport '>".$spottable."</div>";
echo "<div id='card' class=' dayreport '>".$cardtable."</div>";
echo "<div id='due' class=' dayreport '>".$corptable."</div>";
echo "</div>";
echo "<div id='summarycontainer' class=''>";
echo "<div id='summary'>".$summarytable."</div>";
echo "</div>";


function formatSpot($spottable, $row){
	$spottable .= "<tr>";
	$spottable .= "<td></td>";
	$spottable .= "<td>".$row['name']."</td>";
	$spottable .= "<td>".$row['no_of_passengers']."</td>";
	$spottable .= "<td>".$row['amount']."</td>";
	$spottable .= "<td>".$row['amount']."</td>";
	$spottable .= "</tr>";

	return $spottable;
}

function formatCard($cardtable, $row, $banks){
	$cardtable .= "<tr>";
	$cardtable .= "<td>".$banks[$row['bank_id']]."</td>";
	$cardtable .= "<td>".$row['name']."</td>";
	$cardtable .= "<td>".$row['no_of_passengers']."</td>";
	$cardtable .= "<td>".$row['amount']."</td>";
	$cardtable .= "<td></td>";
	$cardtable .= "<td>".$row['amount']."</td>";
	$cardtable .= "</tr>";

	return $cardtable;
}

function formatCorp($corptable, $row, $corps){
	$corptable .= "<tr>";
	$corptable .= "<td>".$corps[$row['corporate_id']]."</td>";
	$corptable .= "<td>".$row['name']."</td>";
	$corptable .= "<td>".$row['no_of_passengers']."</td>";
	$corptable .= "<td>".$row['amount']."</td>";
	$corptable .= "<td></td>";
	$corptable .= "<td>".$row['amount']."</td>";
	$corptable .= "</tr>";

	return $corptable;
}

function findBalFor($reportdate, $con, $type){
	$temp = explode("-", $reportdate);
	$startdate = $temp[0]."-".$temp[1]."-"."01";
	$bfsql = "SELECT SUM(`amount`) as am, SUM(`no_of_passengers`) as pax FROM `requests` WHERE `flight_date` BETWEEN '$startdate' AND '$reportdate' AND `client_type` = $type";
	$tdsql = "SELECT SUM(`amount`) as am, SUM(`no_of_passengers`) as pax FROM `requests` WHERE `flight_date` = '$reportdate' AND `client_type` = $type";
	//Full month
	$result = $con->query($bfsql);
	$fullmonth = $result->fetch_assoc();
	//print_r($fullmonth);
	//Report Date
	$result = $con->query($tdsql);
	$today = $result->fetch_assoc();
	$data['bf']['am'] = $fullmonth['am'] - $today['am'];
	$data['bf']['pax'] = $fullmonth['pax'] - $today['pax'];
	$data['grtotal'] = $fullmonth;
	$data['total'] = $today;

	return $data;
}
function getBanks($con){
	$sql = "SELECT * FROM bank";
	$result = $con->query($sql);
	$banks = array();
	while($row = $result->fetch_assoc()){
		$banks[$row['bank_id']] = $row['bank_code'];
	}
	return $banks;
}
function getCorps($con){
	$sql = "SELECT * FROM `corporate`";
	$result = $con->query($sql);
	$corps = array();
	while($row = $result->fetch_assoc()){
		$corps[$row['corporate_id']] = $row['corporate_name'];
	}
	return $corps;
}
function formatEntry($entrtable, $banks, $corps, $entries, $con){
	ksort($entries);
	$pos = count($entries)/2;
	$i = 0;
	$count=0;
	$spotrec = 0;
	$cardrec = 0;
	$corprec = 0;
	$totpax = 0;
	$totam = 0;
	foreach($entries as $datetime => $entry){
		if(($pos - $i)<0) $tooltippos = "bottom:-8px";
		else $tooltippos = "top:-8px";
		$i++;
		if($entry['arrival_departure']==0) $dir = "Arrival";
		else $dir = "Departure";

		if($entry['requirements'] != NULL) {
			$req = getRequirements($entry['requirements'], $con);
			$spreq = '<span class="w3-badge w3-tooltip w3-round">'.$req['num'].'<span style="position:absolute;left:-10px;'.$tooltippos.';width:150px;text-align:left;z-index:100"
						class="w3-text w3-tag w3-round">'.$req['des'].'</span></span>';
		}
		else $spreq = "None";

		if($entry['mode_of_payment']==1) {
			$mop = "Cash";
			$spotrec++;
		}
		if($entry['mode_of_payment']==2) {
			$mop = "Card";
			$cardrec++;
		}
		if($entry['mode_of_payment']==3) {
			$corprec++;
			$mop = "Due";
		}

		if($entry['client_type'] == "1"){
			$type = "Spot";
			$bank = "-";
			$corp = "-";
		}
		if($entry['client_type'] == "2"){
			$type = "Corporate";
			$bank = "-";
			$corp = $corps[$entry['corporate_id']];
		}
		if($entry['client_type'] == "3"){
			$type = "Card";
			$bank = $banks[$entry['bank_id']];
			$corp = "-";
		}

		$entrtable .= "<tr>
						<td>".$entry['name']."</td>
						<td>".$entry['contact']."</td>
						<td>".$entry['flight_no']."</td>
						<td>".substr($datetime,0,19)."</td>
						<td>".$entry['no_of_passengers']."</td>
						<td>".$dir."</td>
						<td>".$spreq."</td>
						<td>".$entry['amount']."</td>
						<td>".$mop."</td>
						<td>".$bank."</td>
						<td>".$type."</td>
						<td>".$corp."</td>
						<td><div id='asgn-".$entry['id']."' contenteditable='true' spellcheck='false'
								 onkeypress = 'editAssigned(\"asgn-".$entry['id']."\", event)'>
								 ".$entry['assigned_to']."</div></td>
					   </tr>";
		$totpax += $entry['no_of_passengers'];
		$totam += $entry['amount'];
		$count++;
	}
	$entrtable .= "<tr class='w3-white'>
					<th colspan='4'>Total Records: $count, Spot Records: $spotrec, Card Records: $cardrec, Corporate Records: $corprec</th>
					<th>$totpax</th>
					<td colspan=2></td>
					<th>$totam</th>
					<td colspan='5'></td></tr>";
	return $entrtable;
}
function getRequirements($id, $con){
	$req['num'] = 0;
	$req['des'] = '';
	$sql = "SELECT * FROM `requirements` WHERE req_id=$id";
	$result = $con->query($sql);
	if($result->num_rows>0){
		$req['des']='<ul class="w3-ul">';
		$row = $result->fetch_assoc();
		if($row['hotel']==1){
			$req['num']++;
			$req['des'] .= "<li>Hotel</li>";
		}
		if($row['transport']==1){
			$req['num']++;
			$req['des'] .= "<li>Transport to/from</li>";
		}
		if($row['baggage']==1){
			$req['num']++;
			$req['des'] .= "<li>Extra Baggage</li>";
		}
		if($row['bag_store']==1){
			$req['num']++;
			$req['des'] .= "<li>Baggge Store</li>";
		}
		if($row['other']!=NULL){
			$req['num']++;
			$req['des'] .= "<li>".$row['other']."</li>";
		}
		$req['des'] .= "</ul>";
	}

	return $req;
}
?>
