<?php
if(count(get_included_files()) ==1) {
	include("index.php");
	exit();
}
?>
<div class="w3-card-4 w3-display-container report-selector w3-text-black w3-white" id="paidinvquery">
<div>
  <input id="invref" type="text" placeholder="&#x1F50D; Search By Invoice Ref" class="w3-input w3-pale-green" onkeypress="searchInv(event, 'invref');">
</div>
<div>
	<input id="invperiod" type="text" placeholder="&#x1F50D; Search By Period" class="w3-input w3-sand" onkeypress="searchInv(event, 'invperiod');">
</div>
<div class="w3-display-bottom-middle w3-center"><button class="report-selector-toggle" id="selectorshow" value="down" onclick="showQuerySelector('up')">&#8743;</button></div>
</div>
