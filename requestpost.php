<?php
if(!isset($_SERVER['HTTP_REFERER']))
	header ('Location:index.php');


	require_once("utils/db_read.php");
	require_once("utils/commons.php");
	require_once("classes/class_db_tables.php");

	if(isset($_POST['editrequest'])){
		$upDat = array();
		$preObj = json_decode($_POST['predata']);
		//print_r($preObj);
		if($_POST['pnumber'] != $preObj->no_of_passengers) $upDat['no_of_passengers'] = $_POST['pnumber'];
		if($_POST['direction'] != $preObj->arrival_departure) $upDat['arrival_departure'] = $_POST['direction'];
		if($_POST['dtravel'] != $preObj->flight_date) $upDat['flight_date'] = $_POST['dtravel'];
		if($_POST['ttravel'] != $preObj->flight_time) $upDat['flight_time'] = $_POST['ttravel'];
		if($_POST['pname'] != $preObj->name) $upDat['name'] = $_POST['pname'];
		if($_POST['tnumber'] != $preObj->contact) $upDat['contact'] = $_POST['tnumber'];
		if($_POST['fnumber'] != $preObj->flight_no) $upDat['flight_no'] = $_POST['fnumber'];
		if($_POST['provider'] != $preObj->assigned_to) $upDat['assigned_to'] = $_POST['provider'];
		if($_POST['amount'] != $preObj->amount) $upDat['amount'] = $_POST['amount'];

		if($_POST['ctype'] != $preObj->client_type){
			$upDat['client_type'] = $_POST['ctype'];
			$upDat['mode_of_payment'] = $_POST['payment'];
			if($preObj->mode_of_payment == 3)
				$upDat['corporate_id'] = NULL;
			if($preObj->mode_of_payment == 2){
				$upDat['card_no'] = NULL;
				$upDat['bank_id'] = NULL;
			}
			if($preObj->mode_of_payment == 1){
				$upDat['paid'] = 0;
			}
			if($_POST['ctype'] == 1){
				$upDat['paid'] = 1;
			}
		}

		if($_POST['ctype'] == 2){
			if(!isset($preObj->corporate_name)){
				$upDat['corporate_id'] = getCorp($con, $_POST['corporate_id']);
			}
			else if($_POST['corporate_id'] != $preObj->corporate_name){
				$upDat['corporate_id'] = getCorp($con, $_POST['corporate_id']);
			}
		}
		if($_POST['ctype'] == 3){
			if(!isset($preObj->bank_code)){
				$upDat['bank_id'] = getCorp($con, $_POST['bank_id']);
				$upDat['card_no'] = $_POST['card_no'];
			}
			else if($_POST['bank_id'] != $preObj->bank_code){
				$upDat['bank_id'] = getCorp($con, $_POST['bank_id']);
				$upDat['card_no'] = $_POST['card_no'];
			}
		}
		############### Managing Requirements ###############
		$reqObj = new DbTables($con, 'requirements');
		//If [no previous] requirements, but appears in [update] request
		if($preObj->requirements == NULL && isset($_POST['req'])){
			//create new request
			$requirement = $_POST['req'];
			$upDat['requirements'] = get_requirement_sql($requirement, $con);
		}
		//If [BOTH] Previous requirements and Update reqquirements exist
		else if($preObj->requirements != NULL && isset($_POST['req'])){
			//Check if there is any update
			$sql = "SELECT * FROM `requirements` WHERE `req_id`=".$preObj->requirements;
			$r = $reqObj->getSqlResult($sql);
			$upReq = array();
			$existingReq = $r[0];
			foreach($existingReq as $key=>$value){
				if($key=='req_id') continue;
				if($key=='other'){
					if(!isset($_POST['req']['other']) && $value != NULL){
						echo "$key needs to be removed";
						$upReq[$key] = NULL;
					}
					else if(isset($_POST['req']['other'])){
						if($value != $_POST['req']['other']){
							echo "$key needs to be Updated";
							$upReq[$key] = $_POST['req']['other'];
						}
					}
					continue;
				}
				if($value>0 && isset($_POST['req'][$key])){
					continue;
				}
				else if($value>0 && !isset($_POST['req'][$key])){
					echo $key." needs to be deleted from DB<br>";
					$upReq[$key] = '0';
				}
				else if($value==0 && isset($_POST['req'][$key])){
					echo $key." needs to be Inserted in DB<br>";
					$upReq[$key] = '1';
				}
			}
			if($upReq != NULL){
				print_r($upReq);
				$reqObj->updateRecords($upReq, 'req_id', $preObj->requirements);
			}
		}
		//If [previous] requirements exist but [update] does not have any
		else if($preObj->requirements != NULL && !isset($_POST['req'])){
			//Delete existing request ID and remove from Request table
			$upDat['requirements'] = 0;
			$reqObj->deleteRecord('req_id', $preObj->requirements);
		}
		############### Managing Requirements ###############
		if($upDat != NULL){
			$upDatObj = new DbTables($con, 'requests');
			$upDatObj->updateRecords($upDat, 'id', $preObj->id);
			monthUpdated($con, $_POST['dtravel'], '1');
		}
		//print_r($upDat);
	}
	else {
		$sql = init($_POST, $con);
		if($sql>0) echo "Request inserted successfully";

	}
	presentation($_POST);
	header ('Location:'.$_SERVER['HTTP_REFERER']);

	function presentation($fd){
		if($fd['direction'] == 0)
			$direction = "Arrival";
		else if($fd['direction'] == 1)
			$direction = "Departure";
		if(isset($fd['req']))
			$specialreq = "Yes";
		else $specialreq = "None";

		$pt  = '<table class="w3-table-all">';//Presentation Table
		$pt .= '<tr><th>Passanger/Group Leader</th><td>'.$fd['pname'].'</td></tr>';
		$pt .= '<tr><th>Contact Number</th><td>'.$fd['tnumber'].'</td></tr>';
		$pt .= '<tr><th>Flight Number</th><td>'.$fd['fnumber'].'</td></tr>';
		$pt .= '<tr><th>Date and Time of travel</th><td>'.$fd['dtravel'].' '.$fd['ttravel'].'</td></tr>';
		$pt .= '<tr><th>Pax</th><td>'.$fd['pnumber'].'</td></tr>';
		$pt .= '<tr><th>Arrival/Departure</th><td>'.$direction.'</td></tr>';
		$pt .= '<tr><th>Additional Requirements</th><td>'.$specialreq.'</td></tr>';
		$pt .= '<tr><th>Amount</th><td>'.$fd['amount'].'</td></tr>';
		$pt .= '<tr><td colspan=2>Request entered successfully. <a href="'.$_SERVER['HTTP_REFERER'].'">Back to Dashboard</a></td></tr>';
		$pt .= '</table>';

		echo $pt;
	}
	function getBank($con, $bank_code){
		$bankobj = new DbTables($con, 'bank');
		$bank_id = $bankobj->idLookUp('bank_code', $bank_code, 'bank_id');
		if($bank_id == NULL){
			$newbank['bank_code'] = $bank_code;
			$bank_id = $bankobj->insertRecord($newbank);
		}
		return $bank_id;
	}
	function getCorp($con, $corporate_name){
		$corpobj = new DbTables($con, 'corporate');
		$corporate_id = $corpobj->idLookUp('corporate_name', $corporate_name, 'corporate_id');
		if($corporate_id == NULL){
			$newcorp['corporate_name'] = $corporate_name;
			$corporate_id = $corpobj->insertRecord($newcorp);
		}
		return $corporate_id;
	}
	function init($fd, $con){
		$name = $fd['pname'];
		$contct = $fd['tnumber'];
		$flight_no = $fd['fnumber'];
		$flight_date = $fd['dtravel'];
		$flight_time = $fd['ttravel'];
		$no_of_passangers = $fd['pnumber'];
		$arrival_departure = $fd['direction'];

		$req_id = 'NULL';
		if(isset($fd['req'])){
			$requirement = $fd['req'];
			$req_id = get_requirement_sql($requirement, $con);
		}
		$bank_id = 'NULL';
		$card_no = 'NULL';
		if(isset($fd['bank_id'])){
			$bank_id = getBank($con, $fd['bank_id']);
			$card_no = $fd['card_no'];
		}
		$corporate_id = 'NULL';
		if(isset($fd['corporate_id'])){
			$corporate_id = getCorp($con, $fd['corporate_id']);
		}
		$amount = $fd['amount'];
		$mode_of_payment = $fd['payment'];
		$client_type = $fd['ctype'];
		$paid = 0;
		if($client_type == 1)
			$paid = 1;
		$assigned_to = $fd['provider'];
		$reqarr = array('name'=>$name,
						'contact'=>$contct,
						'flight_no'=>$flight_no,
						'flight_date'=>$flight_date,
						'flight_time'=>$flight_time,
						'no_of_passengers'=>$no_of_passangers,
						'arrival_departure'=>$arrival_departure,
						'requirements'=>$req_id,
						'amount'=>$amount,
						'mode_of_payment'=>$mode_of_payment,
						'bank_id'=>$bank_id,
						'card_no'=>$card_no,
						'client_type'=>$client_type,
						'corporate_id'=>$corporate_id,
						'assigned_to'=>$assigned_to,
						'paid'=>$paid
						);
		$reqObj = new DbTables($con, 'requests');
		monthUpdated($con, $flight_date, '1');
		return $reqObj->insertRecord($reqarr);

	}

	function get_requirement_sql($requirement, $con){
		$req_sql = "INSERT INTO `requirements` (`req_id`";
		$req_val = "VALUE (NULL";
		foreach($requirement as $reqname=>$val){
			$req_sql .= ",`$reqname`";
			if($val == 'on') $req_val.= ", '1'";
			else $req_val.= ", '$val'";
		}
		$req_sql .= ")";
		$req_val .= ")";
		$req_sql = $req_sql." ".$req_val;
		$con->query($req_sql);
		$last_id = $con->insert_id;
		return $last_id;
	}
	require_once("utils/db_close.php");
?>
