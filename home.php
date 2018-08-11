<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");

	include(UTILSDIR."/autocompletedata.php");
	$reportdate = date("Y-m-d");
	if(isset($_POST['reportdate'])){
		$reportdate = $_POST['reportdate'];
	}
?>
<style>
@media print {
	#dayreportmenu {
        display :  none;
    }
}
</style>
	<!-- Top menu starts-->
	<div class="w3-gray w3-row" style="min-height:100vh">
	<div class="w3-row w3-dark-gray" id="dayreportmenu">
		  <div class="w3-tiny w3-third" style="background-color:rgba(0,0,0,0.1);height:20px;">
				<!-- HAMBURGER MENU-->
			<span style="float:left;padding-top:2px;padding-left:3px">&nbsp;<a href="javascript:void(0)" class="nodec" onclick="w3_open()">&#9776;</a>&nbsp;</span>
			<!-- Date Chooser Form-->
			<form method="POST" action="" class="w3-center">
				<label>Report of:</label>
				<input type="date" name="reportdate" id="reportdate" required value="<?php echo $reportdate;?>">
				<input type="submit" value="go" style="height:20px;">
			</form>
		  </div>
			<!-- New request -->
		  <div class="w3-small w3-third w3-center" style="background-color:rgba(0,0,0,0.5);height:20px">
			<a href="javascript:void(0)" onclick="document.getElementById('newbill').style.display='block'" class="nodec">New Bill/Request</a>
		  </div>
			<!-- View mode -->
		  <div class="w3-small w3-third w3-center" style="background-color:rgba(0,0,0,0.8);height:20px">
			Viewing Mode:
				<a href="javascript:void(0)" id="viewmode-1" class="viewmode active" onclick="showViewMode(event, 1)">&#x2776; </a>
				<a href="javascript:void(0)" id="viewmode-2" class="viewmode" onclick="showViewMode(event, 2)">&#x2777; </a>
				<a href="javascript:void(0)" id="viewmode-3" class="viewmode" onclick="showViewMode(event, 3)">&#x2778;</a>
		  </div>
	 </div>
		<!-- Top menu ends-->

		<!-- Main Body starts-->
		<div class="w3-row w3-center w3-margin">
			<div class=" w3-third " id="report">
				<div class="w3-responsive" id="dailyreportshow" style="width:99%;">
					<?php include("utils/report_daily01.php");
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
						<?php include("template/formrequest.php")?>
					</div>
				</div>
			</div>
		</div>
		<!-- New Bill/Request part ends-->
<?php
	include(TEMPLATEDIR."/footer.php");
?>

<script>
	var jscorporates = [<?php echo $corpjvar;?>];
	var jsbanks = [<?php echo $bankjvar;?>];

	autocomplete(document.getElementById("corporate_id"), jscorporates);
	autocomplete(document.getElementById("bank_id"), jsbanks);

	function loadDoc(ed) {
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
