<?php
if(count(get_included_files()) ==1) 
	include("index.php");
?>
<div class="w3-display-container w3-blue-gray w3-small" style="height:100%;">
	<div class="w3-display-middle"><?php echo $msg;?></div>
</div>