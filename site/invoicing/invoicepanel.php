<?php
if(count(get_included_files()) ==1) 
	include("index.php");
function formatInvoiceTab($dr, $ctype){
	$outputtab  = "<table class='w3-table-all' id='records'>";
	if($ctype == -1){
		$uninsum = array(); // uninvoiced summary
		for($i=0;$i<count($dr);$i++){
			$fd = $dr[$i]['flight_date'];
			$fm = date("Y-m",strtotime($fd));
			if($dr[$i]['corporate_id'] != NULL){
				if(isset($uninsum[$fm]['corp'][$dr[$i]['corporate_name']]))
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['count']++;
				else {
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['id'] = $dr[$i]['corporate_id'];
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['count'] = 1;
				}
			}
			if($dr[$i]['bank_id'] != NULL){
				if(isset($uninsum[$fm]['bank'][$dr[$i]['bank_code']]))
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['count']++;
				else {
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['id'] = $dr[$i]['bank_id'];
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['count'] = 1;
				}
			}
		}
		$corptab = "<div style='max-height:80vh;overflow:auto' class='w3-half'>\n";
		$banktab = "<div style='max-height:80vh;overflow:auto' class='w3-half'>\n";
		foreach($uninsum as $uninmon=>$data){
			$datehead = date("F, Y",strtotime($uninmon));
			$fromd = $uninmon."-01";
			$tod = date("Y-m-t",strtotime($uninmon));
			if(isset($uninsum[$uninmon]['corp'])){
				$corptab  .= "
							<table class='w3-table-all w3-tiny'>";
				$corptab  .= "
							  <tr class='w3-light-blue'>
								<td colspan=3 class='w3-center'>
									Uninvoiced <strong>CORPORATES</strong> for the month of <strong>$datehead</strong>
								</td>
							  </tr>";
				$corptab  .= "
							  <tr>
								<th>Corporate Name</th>
								<th>Number of records</th>
								<th>Action</th>
							  </tr>";
				$corpdata = $uninsum[$uninmon]['corp'];
				foreach($corpdata as $corpname=>$values){
					$corptab  .= "
							  <tr>
								<td>$corpname</td>
								<td>".$values['count']."</td>
								<td><a href='javascript:void(0)' onclick=\"genInvoice(2, '$corpname', '$fromd', '$tod')\">Generate Invoice</a></td>
							  </tr>";
				}
				$corptab .="
							</table>";
			}
			if(isset($uninsum[$uninmon]['bank'])){
				$banktab  .= "
							<table class='w3-table-all w3-tiny'>";
				$banktab  .= "
							  <tr class='w3-light-green'>
								<td colspan=3 class='w3-center'>
									Uninvoiced <strong>BANKS</strong> for the month of <strong>$datehead</strong>
								</td>
							  </tr>";
				$banktab  .= "
							  <tr>
								<th>Bank Code</th>
								<th>Number of records</th>
								<th>Action</th>
							  </tr>";
				$bankdata = $uninsum[$uninmon]['bank'];
				foreach($bankdata as $bankcode=>$values){
					$banktab  .= "
							  <tr>
								<td>$bankcode</td>
								<td>".$values['count']."</td>
								<td><a href='javascript:void(0)' onclick=\"genInvoice(3, '$bankcode', '$fromd', '$tod')\">Generate Invoice</a></td>
							  </tr>";
				}
				$banktab .="</table>";
			}			
		}
		$corptab .="
					</div>";
		$banktab .="
					</div>";
		$outputtab = $corptab.$banktab;
		return $outputtab;
	}
}
?>