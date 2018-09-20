<?php
if(count(get_included_files()) ==1)
	include("index.php");
function formatInvoiceTab($dr, $ctype){
//	if($ctype == -1){
		$uninsum = array(); // uninvoiced summary
		for($i=0;$i<count($dr);$i++){
			$fd = $dr[$i]['flight_date'];
			$fm = date("Y-m",strtotime($fd));
			if($dr[$i]['corporate_id'] != NULL){
				if(isset($uninsum[$fm]['corp'][$dr[$i]['corporate_name']])){
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['count']++;
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['uninvoiced']['amount']+=$dr[$i]['amount'];
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['uninvoiced']['pax']+=$dr[$i]['no_of_passengers'];
				}

				else {
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['id'] = $dr[$i]['corporate_id'];
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['count'] = 1;
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['uninvoiced']['amount']=$dr[$i]['amount'];
					$uninsum[$fm]['corp'][$dr[$i]['corporate_name']]['uninvoiced']['pax']=$dr[$i]['no_of_passengers'];
				}
			}
			if($dr[$i]['bank_id'] != NULL){
				if(isset($uninsum[$fm]['bank'][$dr[$i]['bank_code']])){
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['count']++;
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['uninvoiced']['amount']+=$dr[$i]['amount'];
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['uninvoiced']['pax']+=$dr[$i]['no_of_passengers'];
				}

				else {
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['id'] = $dr[$i]['bank_id'];
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['count'] = 1;
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['uninvoiced']['amount']=$dr[$i]['amount'];
					$uninsum[$fm]['bank'][$dr[$i]['bank_code']]['uninvoiced']['pax']=$dr[$i]['no_of_passengers'];
				}
			}
		}
		$corptab = "";
		$banktab = "";
		foreach($uninsum as $uninmon=>$data){
			$datehead = date("M, Y",strtotime($uninmon));
			$fromd = $uninmon."-01";
			$tod = date("Y-m-t",strtotime($uninmon));
			if(isset($uninsum[$uninmon]['corp'])){
				$corptab .= "<div class=\"w3-white w3-card-4 w3-round\" style=\"overflow:hidden;margin-bottom:8px\">";
				$corptab .= "<div class=\"w3-row w3-center w3-tiny w3-light-blue	\">
					Uninvoiced <strong>CORPORATES</strong> : <strong>$datehead</strong></div>";

				$corptab  .= "
							<table class='w3-tiny w3-center' width='100%' style='border-collapse:collapse'>";
				$corptab  .= "
							  <tr class='w3-light-gray'>
								<th>Corporate</th>
								<th>Records</th>
								<th>Action</th>
							  </tr>";
				$corpdata = $uninsum[$uninmon]['corp'];
				foreach($corpdata as $corpname=>$values){
					$corptab  .= "
							  <tr style='border-bottom:1px solid rgba(0,0,0,0.2)'>
								<td>$corpname</td>
								<td>".$values['count']."</td>
								<td class='w3-center'><a
											href='javascript:void(0)'
											class='nodec w3-light-blue dot'
											onclick=\"genInvoice(3, '$corpname', '$fromd', '$tod')\"
											title=\"Generate Invoice\"
											style=\"font-size:8px;\">&#x279C;</a></td>
							  </tr>";
				}
				$corptab .="</table>";
				$corptab .="</div>";
			}
			if(isset($uninsum[$uninmon]['bank'])){
				$banktab .= "<div class=\"w3-white w3-card-4 w3-round\" style=\"overflow:hidden;margin-bottom:8px\">";
				$banktab .= "<div class=\"w3-row w3-center w3-tiny w3-lime	\">
					Uninvoiced <strong>BANKS</strong> : <strong>$datehead</strong></div>";
				$banktab  .= "
							<table class='w3-tiny w3-center' width='100%' style='border-collapse:collapse'>";
				$banktab  .= "
							  <tr class='w3-light-gray'>
								<th>Code</th>
								<th>Records</th>
								<th>Action</th>
							  </tr>";
				$bankdata = $uninsum[$uninmon]['bank'];
				foreach($bankdata as $bankcode=>$values){
					$banktab  .= "
							  <tr style='border-bottom:1px solid rgba(0,0,0,0.2)'>
								<td>$bankcode</td>
								<td>".$values['count']."</td>
								<td class='w3-center'><a
											href='javascript:void(0)'
											class='nodec w3-lime dot'
											onclick=\"genInvoice(2, '$bankcode', '$fromd', '$tod')\"
											title=\"Generate Invoice\"
											style=\"font-size:8px;\">&#x279C;</a></td>
							  </tr>";
				}
				$banktab .="</table>";
				$banktab .="</div>";
			}
		}
		$outputtab['corptab'] = $corptab;
		$outputtab['banktab'] = $banktab;
		$outputtab['uninsum'] = $uninsum;
		return $outputtab;
}
?>
