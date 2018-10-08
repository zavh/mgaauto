<?php
$reportdate = $_SESSION['dailyreportdate'];
if(isset($_POST['reportdate'])){
	$reportdate = $_POST['reportdate'];
	$_SESSION['dailyreportdate'] = $_POST['reportdate'];
}

$sql = "SELECT * FROM `requests` WHERE `flight_date`= '$reportdate'
				UNION
				SELECT * FROM `cancelled_requests` WHERE `flight_date`= '$reportdate' ORDER BY `bank_id`, `corporate_id`
				";

////////////////////Preparing Spot Table////////////////////
$drdata = findBalFor($reportdate, $con);

$tabformat['1']['col'] = 5;
$tabformat['1']['bf_offset'] = 1;
$tabformat['1']['bf_head'] = array('SPOT : BF','','bf_tot_pax', 'bf_tot_am', 'bf_tot_rec');
$tabformat['1']['head'] = array('','Name of Client','No of Pax','Bill Amount', 'Amount Received');
$tabformat['1']['data'] = array(NULL,'Name','Pax','Bill Amount', 'Amount Received');
$tabformat['1']['foot'] = array('HEAD:Total',NULL,'day_tot_pax', 'day_tot_am', 'day_tot_rec');
$tabformat['1']['monsum'] = array('HEAD:Grand Total',NULL,'mon_tot_pax', 'mon_tot_am', 'mon_tot_rec');

$tabformat['2']['col'] = 6;
$tabformat['2']['bf_offset'] = 1;
$tabformat['2']['bf_head'] = array('CORPORATE : BF', '','bf_tot_pax', 'bf_tot_am', 'bf_tot_rec', 'bf_tot_due');
$tabformat['2']['head'] = array('Name of Org','Name of Client','No of Pax','Bill Amount', 'Amount Received', 'Amount Due');
$tabformat['2']['data'] = array('Corporate','Name','Pax','Bill Amount', 'Amount Received', 'Amount due');
$tabformat['2']['foot'] = array('HEAD:Total',NULL,'day_tot_pax', 'day_tot_am', 'day_tot_rec', 'day_tot_due');
$tabformat['2']['monsum'] = array('HEAD:Grand Total',NULL,'mon_tot_pax', 'mon_tot_am', 'mon_tot_rec', 'mon_tot_due');

$tabformat['3']['col'] = 6;
$tabformat['3']['bf_offset'] = 1;
$tabformat['3']['bf_head'] = array('BANK : BF', '','bf_tot_pax', 'bf_tot_am', 'bf_tot_rec', 'bf_tot_due');
$tabformat['3']['head'] = array('Name of Bank','Name of Card Holder','No of Pax','Bill Amount', 'Amount Received', 'Amount Due');
$tabformat['3']['data'] = array('Bank','Name','Pax','Bill Amount', 'Amount Received', 'Amount due');
$tabformat['3']['foot'] = array('HEAD:Total',NULL,'day_tot_pax', 'day_tot_am', 'day_tot_rec', 'day_tot_due');
$tabformat['3']['monsum'] = array('HEAD:Grand Total',NULL,'mon_tot_pax', 'mon_tot_am', 'mon_tot_rec', 'mon_tot_due');

$sumCats = array('Spot', 'Corporate','Bank');
$paxtot =0; $amtot=0; $amrec=0; $amdue=0;
for($i=1;$i<4;$i++){

	$drdata[$i]['mon_tot_pax'] = $drdata[$i]['bf_tot_pax'] + $drdata[$i]['day_tot_pax'];
	$drdata[$i]['mon_tot_am'] = $drdata[$i]['bf_tot_am'] + $drdata[$i]['day_tot_am'];
	$drdata[$i]['mon_tot_rec'] = $drdata[$i]['bf_tot_rec'] + $drdata[$i]['day_tot_rec'];
	$drdata[$i]['mon_tot_due'] = $drdata[$i]['bf_tot_due'] + $drdata[$i]['day_tot_due'];
	$catTabs[$i] = formatCats($drdata[$i], $tabformat[$i], $i);

	$sumDat[$i-1] = array($sumCats[$i-1],$drdata[$i]['mon_tot_pax'], $drdata[$i]['mon_tot_am'],$drdata[$i]['mon_tot_rec'],$drdata[$i]['mon_tot_due']);
	$paxtot +=$drdata[$i]['mon_tot_pax']; $amtot+=$drdata[$i]['mon_tot_am']; $amrec+=$drdata[$i]['mon_tot_rec']; $amdue+=$drdata[$i]['mon_tot_due'];
}
$sumTot = array('Total', $paxtot, $amtot,$amrec,$amdue);
$sumCatClass = array('w3-text-amber', 'w3-text-light-blue', 'w3-text-lime');
$sumTabHeads = array('Category', 'No of Pax', 'Bill Amount','Amount Received','Amount Due');

$sumTab = "<div class='w3-margin'><table style='border-collapse:collapse;background:#444;width:100%'>";
$sumTab .= "<tr class='w3-dark-gray'><th>".implode("</th><th>", $sumTabHeads)."</th></tr>";
//print_r($sumDat);
for($i=0;$i<count($sumDat);$i++){
		$sumTab .= "<tr style='border-bottom:1px solid rgba(255,255,255,0.08)' class='".$sumCatClass[$i]."'><td>".implode("</td><td>",$sumDat[$i])."</td></tr>";
}
$sumTab .= "<tr class='w3-blue-gray'><th>".implode("</th><th>", $sumTot)."</th></tr>";
$sumTab .= "</table></div>";


function formatCats($row, $format, $mop){
	if($mop == 1) {
		$headclass = 'w3-text-amber';
		$gradient = 'linear-gradient(to bottom right, white, #ffc107)';
	}
	else if($mop == 3) {
		$headclass = 'w3-text-lime';
		$gradient = 'linear-gradient(to bottom right, white, #cddc39)';
	}
	else if($mop == 2) {
		$headclass = 'w3-text-light-blue';
		$gradient = 'linear-gradient(to bottom right, white, #87ceeb)';
	}
	$catagtable = "<table style='width:100%;margin:0px 0px 0px 1px;background-image: $gradient;'>";
	######FORMATTING BALANCE FORWARD ROW######
	$catagtable .= "<tr class='$headclass w3-dark-gray'>";
	for($i=0;$i<$format['col'];$i++){
		if($i==0) {
			$catagtable .= "<td colspan=".($format['bf_offset']+1).">".$format['bf_head'][$i]."</td>";
			$i += $format['bf_offset'];
			continue;
		}
		$catagtable .= "<td>".$row[$format['bf_head'][$i]]."</td>";
	}
	$catagtable .= "</tr>";
	######FORMATTING TABLE HEAD ROW######
	$catagtable .= "<tr style='background:rgba(255,255,255,0.4)'>";
	for($i=0;$i<$format['col'];$i++){
		$catagtable .= "<th>".$format['head'][$i]."</th>";
	}
	$catagtable .= "</tr>";
	######FORMATTING DAILY DATA ROWS######
	if(isset($row['rec'])){
		for($j=0;$j<count($row['rec']);$j++){
			$catagtable .= "<tr style='background:rgba(255,255,255,0.4)'>";
			for($i=0;$i<$format['col'];$i++){
				if(is_null($format['data'][$i])) $catagtable .= "<td></td>";
				else $catagtable .= "<td>".$row['rec'][$j][$format['data'][$i]]."</td>";
			}
			$catagtable .= "</tr>";
		}
	}
	######FORMATTING DAILY DATA SUM######
	$catagtable .= "<tr style='background:rgba(255,255,255,0.4)'>";
	for($i=0;$i<$format['col'];$i++){
		if(is_null($format['foot'][$i])) $catagtable .= "<td></td>";
		else {
			$cellconfig = explode(":",$format['foot'][$i]);
			if($cellconfig[0]=='HEAD') $catagtable .= "<th>".$cellconfig[1]."</th>";
			else
			$catagtable .= "<th>".$row[$format['foot'][$i]]."</th>";
		}
	}
	$catagtable .= "</tr>";
	######FORMATTING MONTHLY DATA SUM######
	$catagtable .= "<tr style='background:rgba(255,255,255,0.4)'>";
	for($i=0;$i<$format['col'];$i++){
		if(is_null($format['monsum'][$i])) $catagtable .= "<td></td>";
		else {
			$cellconfig = explode(":",$format['monsum'][$i]);
			if($cellconfig[0]=='HEAD') $catagtable .= "<td>".$cellconfig[1]."</td>";
			else
			$catagtable .= "<td>".$row[$format['monsum'][$i]]."</td>";
		}
	}
	$catagtable .= "</tr>";

	$catagtable .= "</table>";
	return $catagtable;
}


function findBalFor($reportdate, $con){
	$temp = explode("-", $reportdate);
	$startdate = $temp[0]."-".$temp[1]."-"."01";
	$banks = getBanks($con);
	$corps = getCorps($con);
	$bfsql = "SELECT * from `requests` WHERE `flight_date` BETWEEN '$startdate' AND '$reportdate' ORDER BY `flight_date`, `flight_time`";
	$monthObj = new DBTables($con, 'requests');
	$md = $monthObj->getSqlResult($bfsql); //Month Data
	$daily_count = 0;
	$count_1 = 0;
	$count_2 = 0;
	$count_3 = 0;
	$data = array(); //Processed Data
	for($j=1;$j<4;$j++){
		$data[$j]['day_tot_pax'] = 0;
		$data[$j]['day_tot_am'] = 0;
		$data[$j]['day_tot_rec'] = 0;
		$data[$j]['day_tot_due'] = 0;

		$data[$j]['bf_tot_pax'] = 0;
		$data[$j]['bf_tot_am'] = 0;
		$data[$j]['bf_tot_rec'] = 0;
		$data[$j]['bf_tot_due'] = 0;
	}

	for($i=0;$i<count($md);$i++){
		$type_count = ${"count_".$md[$i]['client_type']};
		if($md[$i]['flight_date'] == $reportdate){
			$data['daily'][$daily_count]['Name'] = $md[$i]['name'];
			$data['daily'][$daily_count]['Bank'] = ($md[$i]['bank_id']==NULL ?'-':$banks[$md[$i]['bank_id']]);
			$data['daily'][$daily_count]['Corporate'] = ($md[$i]['corporate_id']==NULL ?'-':$corps[$md[$i]['corporate_id']]);
			$data['daily'][$daily_count]['Pax'] = $md[$i]['no_of_passengers'];
			$data['daily'][$daily_count]['Bill Amount'] = $md[$i]['amount'];
			$data[$md[$i]['client_type']]['rec'][$type_count] = $data['daily'][$daily_count];
			$data['daily'][$daily_count]['Contact'] = $md[$i]['name'];
			$data['daily'][$daily_count++]['Assigned'] = $md[$i]['assigned_to'];

			if($md[$i]['paid'] == 1){
				$data[$md[$i]['client_type']]['rec'][$type_count]['Amount Received'] = $md[$i]['amount'];
				$data[$md[$i]['client_type']]['rec'][$type_count]['Amount due'] = 0;
				$data[$md[$i]['client_type']]['day_tot_am'] += $md[$i]['amount'];
				$data[$md[$i]['client_type']]['day_tot_rec'] += $md[$i]['amount'];
			}
			else {
				$data[$md[$i]['client_type']]['rec'][$type_count]['Amount Received'] = 0;
				$data[$md[$i]['client_type']]['rec'][$type_count]['Amount due'] = $md[$i]['amount'];
				$data[$md[$i]['client_type']]['day_tot_am'] += $md[$i]['amount'];
				$data[$md[$i]['client_type']]['day_tot_due'] += $md[$i]['amount'];
			}
			$data[$md[$i]['client_type']]['day_tot_pax'] += $md[$i]['no_of_passengers'];
			${"count_".$md[$i]['client_type']}++;
		}
		else {
			if($md[$i]['paid'] == 1){
				$data[$md[$i]['client_type']]['bf_tot_am'] += $md[$i]['amount'];
				$data[$md[$i]['client_type']]['bf_tot_rec'] += $md[$i]['amount'];
			}
			else {
				$data[$md[$i]['client_type']]['bf_tot_am'] += $md[$i]['amount'];
				$data[$md[$i]['client_type']]['bf_tot_due'] += $md[$i]['amount'];
			}
			$data[$md[$i]['client_type']]['bf_tot_pax'] += $md[$i]['no_of_passengers'];
		}
	}
//	print_r($data);
	return $data;
}
?>
