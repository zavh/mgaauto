<?php
if(!isset($_SERVER['HTTP_REFERER']) && ((count(get_included_files()) ==1))){
		header ('Location:index.php');
		exit;
}
else if(isset($_SERVER['HTTP_REFERER']) && ((count(get_included_files()) ==1))){
	require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	require_once(UTILSDIR."/essentials_open.php");
  $reportdate = $_SESSION['dailyreportdate'];
  require_once(UTILSDIR."/commons.php");
}

if(isset($_GET['mode'])){
  $mode = $_GET['mode'];
}
else $mode = 'daily';
$entrysql = getEntrySql($mode, $reportdate);
$result = $con->query($entrysql);
$entryArr = array();
$table = initPresentation();
if($result->num_rows>0){
  $banks = getBanks($con);
  $corps = getCorps($con);
	while($row=$result->fetch_assoc()){
		$key = $row['flight_date']." ".$row['flight_time']."-".$row['id'];
		$entryArr[$key] = $row;
	}
  $table = formatEntry($table, $banks, $corps, $entryArr, $con);
}

$table .= '</table>';

echo $table;
function getEntrySql($mode, $date){
  if($mode == 'daily') $condition = " = '$date'";
  else if($mode == 'monthly'){
    $fromd = date("Y-m-01", strtotime($date));
    $tod = date("Y-m-t", strtotime($date));
    $condition = " BETWEEN '$fromd' AND '$tod'";
  }

  $sql = "SELECT * FROM `requests` WHERE `flight_date` $condition
        UNION
        SELECT * FROM `cancelled_requests` WHERE `flight_date` $condition ORDER BY `bank_id`, `corporate_id`
        ";
  return $sql;
}

function initPresentation(){
  $entrtable = "<table class='w3-hoverable' style='width:100%;border-collapse:collapse' id='dayEntries'>";
  $entrtable .= "<tr class='w3-light-gray'>
  				<th>Name</th>
  				<th>Contact</th>
  				<th>Flight</th>
  				<th>Date-Time</th>
  				<th>Pax</th>
  				<th>Dir</th>
  				<th>Req</th>
  				<th>Amount</th>
  				<th class='bankKiller'>Bank</th>
  				<th class='cardKiller'>Card no</th>
  				<th class='corpKiller'>Corporate</th>
  				<th>
  					<select class='w3-light-gray' id='ctype_select' style='border:none' onchange='filterCtype()'>
  						<option value='All'>All Types</option>
  						<option value='Spot'>Spot</option>
  						<option value='Corporate'>Corporate</option>
  						<option value='Cancelled'>Cancelled</option>
  						<option value='Card'>Card</option>
  					</select>
  				</th>
  				<th>Assigned</th>
  				<th class='entryAction'>Action</th>
  				</tr>";
    return $entrtable;
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
			$entry['req_item'] = $req['req_item'];
		}
		else $spreq = "None";

		if($entry['mode_of_payment']==1) {
			$mop = "Cash";
			if($entry['invoice']>-1)
				$spotrec++;
		}
		if($entry['mode_of_payment']==2) {
			$mop = "Card";
			if($entry['invoice']>-1)
				$cardrec++;
		}
		if($entry['mode_of_payment']==3) {
			$mop = "Due";
			if($entry['invoice']>-1)
				$corprec++;
		}

		if($entry['client_type'] == "1"){
			$type = "Spot";
			$bank = "-";
			$corp = "-";
			$card_no = "-";
			$trclass = 'w3-text-purple';
		}
		if($entry['client_type'] == "2"){
			$type = "Corporate";
			$bank = "-";
			$card_no = "-";
			$corp = $corps[$entry['corporate_id']];
			$entry['corporate_name'] = $corp;
			$trclass = 'w3-text-indigo';
		}
		if($entry['client_type'] == "3"){
			$type = "Card";
			$bank = $banks[$entry['bank_id']];
			$card_no = $entry['card_no'];
			$corp = "-";
			$entry['bank_code'] = $bank;
			$trclass = 'w3-text-teal';
		}
		if($entry['invoice']<0){
			$type = "Cancelled";
			$trclass = 'w3-text-red';
			$actionLink  = "<a class='w3-red nodec dot' href='javascript:void(0)' onclick='undoCancel(".json_encode($entry).")' title='Undo Cancel'>U</a>";
			$totpax -= $entry['no_of_passengers'];
			$totam -= $entry['amount'];
			$count--;
		}
		else if($entry['invoice']==0){
			$actionLink  = "<a class='w3-pale-green nodec dot' href='javascript:void(0)' onclick='loadDoc(".json_encode($entry).")' title='Edit'>E</a>";
			$actionLink .= "&nbsp;";
			$actionLink .= "<a class='w3-pale-red nodec dot' href='javascript:void(0)' onclick='deleteEntry(".json_encode($entry).")' title='Delete'>D</a>";
			$actionLink .= "&nbsp;";
			$actionLink .= "<a class='w3-aqua nodec dot' href='javascript:void(0)' onclick='cancelEntry(".json_encode($entry).")' title='Cancel'>C</a>";
		}
		else {
			$actionLink = "<a class='w3-gray dot nodec'
			href='javascript:void(0)'
			onclick=\"alert('This entry has been invoiced. You need to remove the invoice first.')\"
			title='No Action Allowed'>N</a>";
		}
		$entrtable .= "<tr style='border-bottom:1px solid rgba(0,0,0,0.2)' class='$trclass w3-hover-black'>
						<td>".$entry['name']."</td>
						<td>".$entry['contact']."</td>
						<td>".$entry['flight_no']."</td>
						<td>".date("d-m-Y H:m:s", strtotime(substr($datetime,0,19)))."</td>
						<td>".$entry['no_of_passengers']."</td>
						<td>".$dir."</td>
						<td>".$spreq."</td>
						<td>".$entry['amount']."</td>
						<td>".$bank."</td>
						<td>".$card_no."</td>
						<td>".$corp."</td>
						<td>".$type."</td>
						<td><div id='asgn-".$entry['id']."' contenteditable='true' spellcheck='false'
								 onkeypress = 'editAssigned(\"asgn-".$entry['id']."\", event)'>
								 ".$entry['assigned_to']."</div></td>
						<td class='entryAction'>$actionLink</td>
					   </tr>";
		$totpax += $entry['no_of_passengers'];
		$totam += $entry['amount'];
		$count++;
	}
	$entrtable .= "<tr class='w3-light-gray'>
					<th colspan='4'>Total Records: $count, Spot: $spotrec, Card: $cardrec, Corporate: $corprec</th>
					<th>$totpax</th>
					<td colspan=2></td>
					<th>$totam</th>
					<td colspan='6'></td></tr>";
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
			$req['req_item'][$req['num']] = 'hotel';
			$req['num']++;
			$req['des'] .= "<li>Hotel</li>";
		}
		if($row['transport']==1){
			$req['req_item'][$req['num']] = 'transport';
			$req['num']++;
			$req['des'] .= "<li>Transport to/from</li>";
		}
		if($row['baggage']==1){
			$req['req_item'][$req['num']] = 'baggage';
			$req['num']++;
			$req['des'] .= "<li>Extra Baggage</li>";
		}
		if($row['bag_store']==1){
			$req['req_item'][$req['num']] = 'bag_store';
			$req['num']++;
			$req['des'] .= "<li>Baggge Store</li>";
		}
		if($row['other']!=NULL){
			$req['req_item'][$req['num']] = 'others';
			$req['req_item'][$req['num']+1] = $row['other'];
			$req['num']++;
			$req['des'] .= "<li>".$row['other']."</li>";
		}
		$req['des'] .= "</ul>";
	}

	return $req;
}
?>
