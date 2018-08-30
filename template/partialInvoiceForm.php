<?php
if(count(get_included_files()) ==1) {
	include("index.php");
	exit();
}
?>

<div class="w3-card-4 w3-display-container report-selector w3-text-black" id="reportform">
    <!-- Invoice Form Starts-->

    <table style="width:100%;height:100%;" class="w3-display-top-middle w3-tiny">
      <tr><th style="text-align:right;width:40%">From:</th>
        <td>
          <input type="date" name="startdate" class="w3-input" id="startdate" required>
        </td>
      </tr>
      <tr><th style="text-align:right">To:</th>
        <td>
          <input type="date" name="enddate" class="w3-input" id="enddate" required>
        </td>
      </tr>
      <tr><th style="text-align:right">Select Type:</th>
        <td>
        <select class="w3-select" id="client_type" name="client_type" onchange="invCondition()">
          <option value='0' selected disabled>All</option>
          <option value='2'>Card</option>
					<option value='3'>Due</option>
        </select>
        </td>
      </tr>
      <tr class="spel" id="spel-3" style="display:none">
        <th style="text-align:right">Corporate Name:</th>
        <td>
          <input type="text" class="w3-input els autocomplete" name="corp_id" id="el-3" placeholder="Corporate Name" required disabled size=12>
        </td>
      </tr>
      <tr class="spel" id="spel-2" style="display:none">
        <th style="text-align:right">Bank Name:</th>
        <td>
          <input type="text" class="w3-input els autocomplete" name="bank_id" id="el-2" placeholder="Bank Name" required disabled size=12>
        </td>
      </tr>
      <tr>
        <td colspan=2 style="text-align:center">
          <div><button class="report-selector-toggle w3-light-blue" onclick="processInvoice()">SHOW</button></div>
        </td>
      </tr>
    </table>
    
    <div class="w3-display-bottom-middle w3-center"><button class="report-selector-toggle" id="selectorshow" onclick="showReportSelector('up')">&#8743;</button></div>
    <!-- Invoice Form Ends-->
</div>
