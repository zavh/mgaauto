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
.summaryTable{
	width:100%;
	//border-collapse: collapse;
	background-color: #eee;
}
.summaryTable tr th{
	border-top: 1px solid #ccc;
	border-bottom: 1px solid #ccc;
	text-align:left;
	background-color: #ddd;
	font-size: 10px;
}
.summaryTable tr.card{
	border-top: 1px solid #aaa;
	border-bottom: 1px solid #aaa;
	color: rgba(10,129,20,1);
	vertical-align: middle;
}
.summaryTable tr.cardDetailsShow{
	color: rgba(0,0,0,0.8);
}
.summaryTable tr.corpDetailsShow{
	border-top: 1px solid #aaa;
	border-bottom: 1px solid #aaa;
	color: rgba(129,20,20,0.9);
}
.summaryTable tr.cardMonthDetailsShow{
	border-top: 1px solid #aaa;
	border-bottom: 1px solid #aaa;
	color: rgba(20,170,50,0.9);
}
.summaryTable tr.corpMonthDetailsShow{
	border-top: 1px solid #aaa;
	border-bottom: 1px solid #aaa;
	color: rgba(89,70,250,0.8);
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
/*
		$menuitems[2]['classes']  = "darkestmenu";
	 	$menuitems[2]['details']  = "<form method=\"POST\" action=\"\" class=\"w3-center\">";
	 	$menuitems[2]['details'] .= "<label>Report of : </label>";
	 	$menuitems[2]['details'] .= "<input type=\"date\" name=\"reportdate\" id=\"reportdate\" required value=\"$reportdate\">";
	 	$menuitems[2]['details'] .= "&nbsp;<input type=\"submit\" value=\"go\" style=\"height:20px;\">";
	 	$menuitems[2]['details'] .= "</form>";
*/
	 	include(TEMPLATEDIR."/topmenu.php");
	 ?>
	 <!-- Top Panel Ends-->
		<!-- Top menu ends-->

		<!-- Main Body starts-->
		<div class='w3-row w3-left' style='margin:2px 0px 2px 8px'>
			<form method="POST" action="" class="w3-center">
				<label>Report of : </label>
				<input type="date" name="reportdate" id="reportdate" required value="<?php echo $reportdate;?>">
				&nbsp;<input type="submit" value="go">
			</form>
		</div>
		<div class="w3-row w3-center" style='margin-left:8px;margin-right:8px'>
			<div class=" w3-third " id="report">
				<div class="w3-responsive" id="dailyreportshow" style="width:100%;">
					<?php include("report_daily.php");
					?>
					<div id='reportcontainer' class='w3-row'>
					<div id='spot' class=' dayreport '><?php echo $catTabs[1];?></div>
					<div id='due' class=' dayreport '><?php echo $catTabs[2];?></div>
					<div id='card' class=' dayreport '><?php echo $catTabs[3];?></div>
					</div>
					<div id='summarycontainer'>
						<div id='summary'><?php echo $sumTab?></div>
					</div>
				</div>
			</div>
			<div class=" w3-twothird " id="record">
				<div class=" w3-card" style='margin:2px 4px 8px 4px'>
					<div class="w3-center w3-responsive entryreport" id="dailyrecordshow" style='background:#333'>
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
						<?php
						 	$otherid = "nrOther";
							include(TEMPLATEDIR."/formrequest.php")?>
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
<script src="<?php echo JSDIR;?>/dailyreport.js?version=0.4"></script>
<script>
	var jscorporates = [<?php echo $corpjvar;?>];
	var jsbanks = [<?php echo $bankjvar;?>];

	autocomplete(document.getElementById("corporate_id"), jscorporates);
	autocomplete(document.getElementById("bank_id"), jsbanks);
</script>
