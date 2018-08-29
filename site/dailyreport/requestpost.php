<?php
if(!isset($_SERVER['HTTP_REFERER'])){
	header ('Location:index.php');
	exit;
}

	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	require_once(TEMPLATEDIR."/header.php");
	require_once(UTILSDIR."/commons.php");
	require_once(CLASSDIR."/class_month_data.php");
	############################ MONTH REPORT INITIALIZATION #############################
	# [Stage 1]: Check if monthreport for target month exists. If not, create one from te#
	# -mplate file kept in TEMPLATEDIR. Global variable EMPTYMONTHTEMPLATE holds the path#
	# [Stage 2]: Instantiate the month_data object class. Set filename where altered data#
	# will be saved after execution. Set the month variable if it's a new file.
	# [Stage 3]: Retrieve last month's arrear. Every month's arrear consists of that mont#
	# 'h's arrear combined with it's previous month's arrear. This is retrieved from db  #
	# table 'monthreport' where it is saved stream-wise in json format.
	# [Stage 4]: Changes the chartLabel which changes according to the days of the month #
	# and change the chartLabel of the template file.
	######################################################################################

	############ [Stage 1] ############
	$mrFileName = PERFORMANCEREPORTDIR."/"."monthreport-".getMonth($_POST['dtravel']).".json";
	$newFileNeeded = false; // Checks if first time file, default false
	if(file_exists($mrFileName)){ // If file exists, then first time flag remains false
		$mrdata = getJSONobj($mrFileName); // Reads existing data
	}
	else {
		$mrdata = getJSONobj(EMPTYMONTHTEMPLATE); // Creates a new file from empty template
		$newFileNeeded = true; //Sets first time flag to true
	}
	############ [Stage 2] ############
	$thismonth = date("Y-m-01", strtotime($_POST['dtravel']));
	$mdObj = new month_data($mrdata); //Object instantiated
	$mdObj->setRepository($mrFileName); //Setting up filename where data is saved at the end of execution.
	############ [Stage 3] ############
	//function getLastMonthArrear resides in utils/commons.php
	$lastMonthData = getLastMonthArrear($con, $thismonth);
	if(count($lastMonthData) == 0){
		$lastMonthArrear = array('1'=>0,'2'=>0,'3'=>0); // If no last month data available, set it all to zero
	}
	else $lastMonthArrear = json_decode($lastMonthData[0]['arrear'], true);//else data loaded from database table 'monthreport'
	############ [Stage 4] ############
	$mdObj->setPrevArrear($lastMonthArrear);
	if($newFileNeeded) {
		$mdObj->setMonth($thismonth); //Setting up the month
		$mdObj->setChartLabel($_POST['dtravel']); // changing chart label
		initDBMonthTable($thismonth, "monthreport-".getMonth($_POST['dtravel']).".json", $con);
	}
	$muFlag = false; //Flag for updates/changes when monthupdate will be executed
	################# EDIT REQUEST HANDLER STARTS #################
	if(isset($_POST['editrequest'])){
		$upDat = array();
		$preObj = json_decode($_POST['predata']);
		if($_POST['pnumber'] != $preObj->no_of_passengers) {
			$upDat['no_of_passengers'] = $_POST['pnumber'];
			$muFlag = true;
		}
		if($_POST['direction'] != $preObj->arrival_departure) $upDat['arrival_departure'] = $_POST['direction'];
		if($_POST['dtravel'] != $preObj->flight_date){
			$upDat['flight_date'] = $_POST['dtravel'];
			$muFlag = true;
		}
		if($_POST['ttravel'] != $preObj->flight_time) $upDat['flight_time'] = $_POST['ttravel'];
		if($_POST['pname'] != $preObj->name) $upDat['name'] = $_POST['pname'];
		if($_POST['tnumber'] != $preObj->contact) $upDat['contact'] = $_POST['tnumber'];
		if($_POST['fnumber'] != $preObj->flight_no) $upDat['flight_no'] = $_POST['fnumber'];
		if($_POST['provider'] != $preObj->assigned_to) $upDat['assigned_to'] = $_POST['provider'];
		if($_POST['amount'] != $preObj->amount){
			$upDat['amount'] = $_POST['amount'];
			$muFlag = true;
		}
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
			$muFlag = true;
		}

		if($_POST['ctype'] == 2){ //Check if Corporate is newly inserted or changed
			if(!isset($preObj->corporate_name)){ //Newly inserted Corporate
				$upDat['corporate_id'] = getCorp($con, $_POST['corporate_id']);
				$muFlag = true;
			}
			else if($_POST['corporate_id'] != $preObj->corporate_name){ // Just changed from one corporate to another
				$upDat['corporate_id'] = getCorp($con, $_POST['corporate_id']);
				$muFlag = true;
			}
		}
		if($_POST['ctype'] == 3){
			if(!isset($preObj->bank_code)){
				$upDat['bank_id'] = getBank($con, $_POST['bank_id']);
				$upDat['card_no'] = $_POST['card_no'];
				$muFlag = true;//Monthly report needs to be updated
			}
			else if($_POST['bank_id'] != $preObj->bank_code){
				$upDat['bank_id'] = getBank($con, $_POST['bank_id']);
				$upDat['card_no'] = $_POST['card_no'];
				$muFlag = true;//Monthly report needs to be updated
			}
			else if($_POST['card_no'] != $preObj->card_no){
				$upDat['card_no'] = $_POST['card_no'];//Only card number changed, no need to change monthly report
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
						$upReq[$key] = NULL;
					}
					else if(isset($_POST['req']['other'])){
						if($value != $_POST['req']['other']){
							$upReq[$key] = $_POST['req']['other'];
						}
					}
					continue;
				}
				if($value>0 && isset($_POST['req'][$key])){
					continue;
				}
				else if($value>0 && !isset($_POST['req'][$key])){
					$upReq[$key] = '0';
				}
				else if($value==0 && isset($_POST['req'][$key])){
					$upReq[$key] = '1';
				}
			}
			if($upReq != NULL){
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
			if($muFlag){
				$predata = json_decode($_POST['predata'], true);
				$mdObj->delete($_POST['predata']);
				if($predata["mode_of_payment"] == 2) $mdObj->setClientId($predata['bank_id']);
				else if($predata["mode_of_payment"] == 3) $mdObj->setClientId($predata['corporate_id']);
				$mdObj->add($_POST);
			}
		}
	}
	################# EDIT REQUEST HANDLER ENDS #################
	######### DELETE ENTRY/Bill REQUEST HANDLER STARTS ##########
	else if(isset($_POST['deleterequest'])){
		$reqObj = new DbTables($con, 'requests');
		$mdObj->delete($_POST['reqdat']);
		$reqObj->deleteRecord('id', $_POST['reqid']);
		$muFlag = true;
	}
	######### DELETE ENTRY/Bill REQUEST HANDLER ENDS ##########
	########## NEW ENTRY/BILL REQUEST INSERT STARTS ###########
	else {
		$sql = init($_POST, $con, $mdObj);
		if($sql>0) {
			$mdObj->add($_POST);
		}
		$muFlag = true;
		$_SESSION['dailyreportdate'] = date("Y-m-d",strtotime($_POST['dtravel']));
	}
	########## NEW ENTRY/BILL REQUEST INSERT STARTS ###########
	if($muFlag){
		monthArrearUpdate($con, $_POST['dtravel'], $mdObj->getArrearArr()); //Update this month's arrears
		updateConsucutiveMonths($con, $mdObj);
	}
	header ('Location:'.$_SERVER['HTTP_REFERER']);


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
	function init($fd, $con, $mdObj){
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
			$mdObj->setClientId($bank_id);
		}
		$corporate_id = 'NULL';
		if(isset($fd['corporate_id'])){
			$corporate_id = getCorp($con, $fd['corporate_id']);
			$mdObj->setClientId($corporate_id);
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
		//monthUpdated($con, $flight_date, '1');
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

	function initDBMonthTable($tmonth, $repname, $con){
		$value['report_month'] = $tmonth;
		$value['repository_name'] = $repname;
		$value['arrear'] = "{\"1\":0,\"2\":0,\"3\":0}";
		$value['update_status'] = 0;
		$mrObj = new DbTables($con, 'monthreport');
		$mrObj->insertRecord($value);
	}
	require_once(TEMPLATEDIR."/footer.php");
?>
