<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");
	include(UTILSDIR."/autocompletedata.php");
	include(UTILSDIR."/commons.php");
	require_once(CLASSDIR."/class_month_data.php");

	//Setting the default report date
	if(!isset($_SESSION['dailyreportdate'])){
		$_SESSION['dailyreportdate'] = date("Y-m-d");
	}
	$reportdate = $_SESSION['dailyreportdate']; //Report date is the default unless other date is posted
	if(isset($_POST['reportdate'])){ //POSTed date found, default and reportdate both will be changed
		$reportdate = $_POST['reportdate'];
		$_SESSION['dailyreportdate'] = $reportdate;
	}
?>
<style>
.entryAction{
}
@media print {
	#dayreportmenu {
      display :  none;
    }
		.entryAction{
			display :  none;
		}
}
</style>
	<!-- Page background or Page Wrapper -->
	<div class="w3-gray w3-row" style="min-height:100vh;height:100%">
	 <!-- Top Panel Starts-->
	 <?php
	 	$pagetitle = "Daily Performance Dashboard";
		$menuid = "dayreportmenu";

		$menuitems[0]['classes']  = "w3-small w3-center darkmenu";
	 	$menuitems[0]['details']  = "<a href=\"javascript:void(0)\" ";
	 	$menuitems[0]['details'] .= "onclick=\"document.getElementById('newbill').style.display='block'\" class=\"nodec\">New Bill/Request</a>";

		$menuitems[1]['classes']  = "w3-small w3-center darkermenu";
	 	$menuitems[1]['details']  = "Viewing Mode:";
	 	$menuitems[1]['details'] .= "<a href=\"javascript:void(0)\" id=\"viewmode-1\" class=\"viewmode active\" onclick=\"showViewMode(event, 1)\">&#x2776; </a>";
		$menuitems[1]['details'] .= "<a href=\"javascript:void(0)\" id=\"viewmode-2\" class=\"viewmode\" onclick=\"showViewMode(event, 2)\">&#x2777; </a>";
		$menuitems[1]['details'] .= "<a href=\"javascript:void(0)\" id=\"viewmode-3\" class=\"viewmode\" onclick=\"showViewMode(event, 3)\">&#x2778; </a>";
		$menuitems[1]['details'] .= "<a href=\"javascript:void(0)\" id=\"viewmode-3\" class=\"viewmode\" onclick=\"
			document.getElementById('servicereport').style.display='block'\">
			&#x2779; </a>";

		$menuitems[2]['classes']  = "darkestmenu";
	 	$menuitems[2]['details']  = "<form method=\"POST\" action=\"\" class=\"w3-center\">";
	 	$menuitems[2]['details'] .= "<label>Report of : </label>";
	 	$menuitems[2]['details'] .= "<input type=\"date\" name=\"reportdate\" id=\"reportdate\" required value=\"$reportdate\">";
	 	$menuitems[2]['details'] .= "&nbsp;<input type=\"submit\" value=\"go\" style=\"height:20px;\">";
	 	$menuitems[2]['details'] .= "</form>";

	 	include(TEMPLATEDIR."/topmenu.php");
	 ?>
	 <!-- Top Panel Ends-->
		<!-- Top menu ends-->

		<!-- Main Body starts-->
		<div class="w3-row w3-center w3-margin">
			<div class=" w3-third " id="report">
				<div class="w3-responsive" id="dailyreportshow" style="width:99%;">
					<?php include("report_daily.php");
					?>
					<div id='reportcontainer' class='w3-row'>
					<div id='spot' class=' dayreport '><?php echo $catTabs[1];?></div>
					<div id='due' class=' dayreport '><?php echo $catTabs[2];?></div>
					<div id='card' class=' dayreport '><?php echo $catTabs[3];?></div>
					</div>
					<div id='summarycontainer' class=''>
						<div id='summary'><?php echo $sumTab?></div>
					</div>
				</div>
			</div>
			<div class=" w3-twothird " id="record">
				<div class=" w3-card ">
					<div class="w3-center w3-responsive entryreport" id="dailyrecordshow" >
						<?php echo $entrtable; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
		<!-- New Bill/Request Modal part starts-->
		<div id="newbill" class="w3-modal">
			<div class="w3-modal-content">
				<div class="w3-container">
					<span onclick="resetRequestModal()" class="w3-button w3-display-topright">&times;</span>
					<div id='requestcontainer'>
						<?php include(TEMPLATEDIR."/formrequest.php")?>
					</div>
				</div>
			</div>
		</div>
		<!-- New Bill/Request part ends-->
		<div id="servicereport" class="w3-modal">
			<div class="w3-modal-content"  style="width:98vw;">
				<div class="w3-container">
					<span onclick="document.getElementById('servicereport').style.display='none'" class="w3-button w3-display-topright">&times;</span>
					<div id='requestcontainer'>
						<?php include(TEMPLATEDIR."/servicereport.php")?>
					</div>
				</div>
			</div>
		</div>
<?php
	include(TEMPLATEDIR."/footer.php");
?>

<script>
	var jscorporates = [<?php echo $corpjvar;?>];
	var jsbanks = [<?php echo $bankjvar;?>];

	autocomplete(document.getElementById("corporate_id"), jscorporates);
	autocomplete(document.getElementById("bank_id"), jsbanks);

	function loadDoc(ed) {
		//alert(ed['id']);
		var x = document.getElementById('asgn-'+ed['id']+'-changed');
		if(x != null)
			ed['assigned_to'] = x.value.trim();

		document.getElementById('predata').value = JSON.stringify(ed);
		document.getElementById('newbill').style.display='block'
		document.getElementsByName('pnumber')[0].value=ed['no_of_passengers'];
		document.getElementsByName('direction')[ed.arrival_departure].checked = true;
		document.getElementsByName('dtravel')[0].value=ed['flight_date'];
		document.getElementsByName('ttravel')[0].value=ed['flight_time'];
		document.getElementsByName('pname')[0].value=ed['name'];
		document.getElementsByName('tnumber')[0].value=ed['contact'];
		document.getElementsByName('fnumber')[0].value=ed['flight_no'];
		document.getElementsByName('provider')[0].value=ed['assigned_to'];
		document.getElementsByName('amount')[0].value=ed['amount'];
		document.getElementById('payment').options[ed['mode_of_payment']].selected = 'selected';
		document.getElementsByName('payment')[0].value = ed['mode_of_payment'];
		var ctypeSelect = parseInt(ed['client_type'])-1;
		document.getElementsByName('ctype')[ctypeSelect].checked = true;
		if(ed['client_type'] == 2){
			document.getElementById('corporate').style.display = 'inline';
			document.getElementById('corporate_id').disabled = false;
			document.getElementById('corporate_id').value = ed['corporate_name'];
		}
		if(ed['client_type'] == 3){
			document.getElementById('bank').style.display = 'inline';
			document.getElementById('bank_id').disabled = false;
			document.getElementById('card_no').disabled = false;
			document.getElementById('bank_id').value = ed['bank_code'];
			document.getElementById('card_no').value = ed['card_no'];
		}
		document.getElementById('editrequest').disabled = false;
		document.getElementById('predata').disabled = false;
		if(ed['requirements']){
			var reqObj = ed['req_item'];
			for(var i=0;i<reqObj.length;i++){
				if(reqObj[i]=='others'){
					document.getElementById("others").checked = true;
					document.getElementById("hide").style.display = 'inline';
					document.getElementById("other_description").value = reqObj[i+1];
					document.getElementById("other_description").disabled = false;
				}
				else
					document.getElementsByName("req["+reqObj[i]+"]")[0].checked = true;
			}
		}
	}
	function deleteEntry(json){
		var x = confirm("This will Delete the entry permanently. Confirm to proceed.");
		if(x == true){
			var deleteForm = document.createElement("form");
				deleteForm.method = "POST";
				deleteForm.action = "requestpost.php";

			var idInput = document.createElement("input");
		    idInput.type = "hidden";
		    idInput.name = "reqid";
		    idInput.value = json.id;
		    deleteForm.appendChild(idInput);

			var commandInput = document.createElement("input");
		    commandInput.type = "hidden";
		    commandInput.name = "deleterequest";
		    commandInput.value = true;
		    deleteForm.appendChild(commandInput);

			var dateInput = document.createElement("input");
		    dateInput.type = "hidden";
		    dateInput.name = "dtravel";
		    dateInput.value = json.flight_date;
		    deleteForm.appendChild(dateInput);

			var dataInput = document.createElement("input");
		    dataInput.type = "hidden";
		    dataInput.name = "reqdat";
		    dataInput.value = JSON.stringify(json);
		    deleteForm.appendChild(dataInput);

			document.body.appendChild(deleteForm);
			deleteForm.submit();
		}
	}
	function filterCtype() {

	  var sindex = document.getElementById("ctype_select").selectedIndex;

	  var input, filter, table, tr, td, i;
	  input = document.getElementById("ctype_select").options[sindex];
	  filter = input.value.toUpperCase();
	  table = document.getElementById("dayEntries");
	  tr = table.getElementsByTagName("tr");

	  if(filter=='ALL'){
		  for (i = 0; i < tr.length; i++) {
			  tr[i].style.display = "";
		  }
	  }
	  else {
		  for (i = 0; i < tr.length; i++) {
			td = tr[i].getElementsByTagName("td")[11];
			if (td) {
			  if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
				tr[i].style.display = "";
			  } else {
				tr[i].style.display = "none";
			  }
			}
		  }
		  //Always hide Total tr
		  tr[i-1].style.display = "none";
	  }
	}
</script>
