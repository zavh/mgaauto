<?php
if(count(get_included_files()) ==1)
	include("index.php");
?>
	<div class="w3-row w3-gray">
		<div class="w3-quarter">
			<div class="w3-margin w3-dark-gray w3-card-4 w3-round" style="overflow:hidden">
			<div class="w3-row w3-center w3-tiny dashwidgethead">MONTH SUMMARY FOR <?php echo strtoupper($tdReadable);?></div>
			<div class="w3-tiny w3-padding-small">
				<h4 class="w3-wide dashtot">
					Total Amount:
					<span class="w3-text-light-green">
						<?php echo $monthSummary['total'];?>
					</span>
				</h4>
				<div class="w3-row" style="overflow:auto">
				<?php
					$trTitle = array("Spot Cash","Due/Invoice",
										"Recovered Amount","Unrecovered Amount",
										"Invoices Raised","Invoice Paid",
										"Invoice Unpaid","Uninvoiced Records",
										"Total Pax Served","Total Bill/Request");
					$trVal = array("cash","invoice",
										"recovered","pending_payment",
										"invoice_raised","invoice_paid",
										"invoice_unpaid","uninv_records",
										"total_pax","total_rec");
					$trUnit = array("BDT","BDT","BDT","BDT","Nos","Nos","Nos","Records","Passangers","Records");
					$summaryTab = "<table style=\"width:100%;border-collapse:collapse;\">";
					$border_bottom = "border-bottom:1px solid #999;";
					for($i=0;$i<count($trTitle);$i++){
						if($i == (count($trTitle)-1)) $border_bottom = '';
						$summaryTab .= "<tr style=\"$border_bottom\">";
						$summaryTab .= "<th style=\"text-align:right\">".$trTitle[$i]."</th>";
						$summaryTab .= "<td style=\"text-align:left\" class=\"w3-text-lime\">:
							".$monthSummary[$trVal[$i]]."</td>";
						$summaryTab .= "<td style=\"text-align:left\" class=\"w3-text-orange\">".$trUnit[$i]."</td>";
					}
					$summaryTab .= "</table>";
					echo $summaryTab;
				?>
				</div>
				</div>
			</div>

			<div class="w3-margin w3-blue-gray w3-card-4 w3-round" style="overflow:hidden">
			<div class="w3-row w3-center w3-tiny" style="background:rgba(0,0,0,0.4)">STREAM WISE SUMMARY</div>
			<div class="w3-tiny">
				<div class="w3-row w3-tiny" style="overflow:auto">
					<table style="width:100%;border-collapse:collapse;">
						<tr style="border-bottom:1px solid #fff">
							<th class="w3-text-sand" style="text-align:right">Streams : </th>
							<th class="w3-text-amber" style="text-align:left">Cash</th>
							<th class="w3-text-lime" style="text-align:left">Bank</th>
							<th class="w3-text-light-blue" style="text-align:left">Corporte</th>
						</tr>
						<?php
							$stTrTitle = array("Amount", "Recovered", "Pax", "No of Requests","Number of Clients","Raised Invoice","Invoice Paid","Uninvoiced Records");
							$stTrValue = array("amount", "recovered", "pax","request","noclients","raised_invoice","invoice_paid","uninv_records");
							$stClassNames = array("w3-text-amber","w3-text-lime","w3-text-light-blue");

							$streamTab = "";
							for($i=0;$i<count($stTrTitle);$i++){
								$streamTab .= "<tr style=\"border-bottom:1px solid rgba(255,255,255,0.1)\">";
								$streamTab .= "<th style=\"text-align:right\" class=\"w3-text-sand\">".$stTrTitle[$i]." : </th>";
								for($j=1;$j<4;$j++){
									$streamTab .= "<td class=\"".$stClassNames[($j-1)]."\">".$monthSummary['stream'][$j][$stTrValue[$i]]."</td>";
								}
							}
							echo $streamTab;
						?>
					</table>
				</div>
				</div>
			</div>

		</div>
		<div class="w3-threequarter">
		<div class="w3-row"> <!-- Upper Layer Container-->
		<div class="w3-third">
			<div class="w3-margin w3-dark-gray w3-card-4 w3-round" style="overflow:hidden">
			<div class="w3-row w3-center w3-tiny" style="background:rgba(139,106,7,0.8)">INVOICE STATUS : </div>

				<div class="w3-row w3-tiny" style="overflow:auto">
				  <div style="min-height:250px;position:relative;background:#ffc107;">
					<canvas id="bankInvoice"></canvas>
						<script>
							var ctx = document.getElementById("bankInvoice");
							var bankInvoice = new Chart(ctx, {
								type: 'doughnut',
								data: {
									labels: ["Invoiced","Uninvoiced","Paid"],
									datasets: [{

										data: [	<?php echo $monthSummary['donut'];?>
										],
										backgroundColor: [
											'rgba(0, 0, 0, 0.4)',
											'rgba(0, 0, 0, 0.6)',
											'rgba(0, 0, 0, 0.8)',
										],
										borderColor: [
											'rgba(0,0,0,1)',
											'rgba(0,0,0,1)',
											'rgba(0,0,0,1)',
										],
										borderWidth: 0,
									}]
								},
								options: {
									maintainAspectRatio: false,
									legend: {
										display: true,
										labels: {
											fontColor: 'rgb(0, 0, 0, 0.7)',
											fontSize: 10,
											usePointStyle: true,
											padding:12,
										},
										position: 'bottom',
									},
									layout: {
										padding: {
											left: 4,
											right: 4,
											top: 12,
											bottom: 2
										}
									},
								}
							});
						</script>
				  </div>
				</div>
			</div>
		</div>
		<div class="w3-third">
			<div class="w3-margin w3-dark-gray w3-card-4 w3-round" style="overflow:hidden">
			<div class="w3-row w3-center w3-tiny" style="background:rgba(182,54,74,0.9)">PAYMENT STATUS : </div>

				<div class="w3-row w3-tiny" style="overflow:auto">
				  <div style="min-height:250px;position:relative;background:#f86c6b;">
			<canvas id="bankRecovary"></canvas>
				<script>
					var ctx = document.getElementById("bankRecovary");
					var bankRecovary = new Chart(ctx, {
						type: 'pie',
						data: {
							labels: ["Cash","Paid","Pending"],
							datasets: [{

								data: [<?php echo $monthSummary['pie'];?>],
								backgroundColor: [
									'rgba(155, 0, 0, 0.4)',
									'rgba(0, 15, 0, 0.4)',
									'rgba(0, 0, 15, 0.5)',
								],
								borderColor: [
									'rgba(255,255,255,0.5)',
									'rgba(255,255,255,0.5)',
									'rgba(255,255,255,0.5)',
								],
								borderWidth: 1,
							}]
						},
						options: {
							maintainAspectRatio: false,
							legend: {
								display: true,
								labels: {
									fontColor: 'rgb(0, 0, 0, 0.7)',
									fontSize: 10,
									usePointStyle: true,
									padding:12,
								},
								position: 'bottom',
							},
							layout: {
								padding: {
									left: 4,
									right: 4,
									top: 12,
									bottom: 2
								}
							},
						}
					});
				</script>
				  </div>
				</div>
			</div>
		</div>
		<!-- Widget Area Starts-->
		<div class="w3-third">
			<!-- ######REVENUE GENERATOR WIDGET###### -->
					<?php performanceWidget($monthSummary['stream'],'revgen','REVENUE GENERATOR');?>
			<!-- ######REQUEST GENERATOR WIDGET###### -->
					<?php performanceWidget($monthSummary['stream'],'reqgen','REQUEST GENERATOR');?>
			<!-- ######PAX GENERATOR WIDGET###### -->
					<?php performanceWidget($monthSummary['stream'],'paxgen','PAX GENERATOR');?>
		</div>
		<!-- Widget Area Ends-->
	</div> <!-- Upper Layer container ends-->
			<div style="min-height:200px;max-height:80%;max-width:100%;position:relative;margin:4px" class="w3-margin w3-card-4 w3-lime w3-round">
			<canvas id="bankSummary"></canvas>
				<script>
					var ctx = document.getElementById("bankSummary");
					var bankSummary = new Chart(ctx, {
						type: 'bar',
						data:{
							labels: [<?php echo $monthSummary['chartlable']?>],
							datasets: [{
								label: 'Spot Amount',
								stack: 'Stack 0',
								yAxisID: 'y-axis-0',
								data: [<?php echo $monthSummary['streamMonthDat'][1]['amString'];?>],
								backgroundColor: 'rgba(0,45,0,0.2)',
								fill:true,
								borderWidth: 0,
							},
							{
								label: 'Card Amount',
								stack: 'Stack 0',
								yAxisID: 'y-axis-0',
								data: [<?php echo $monthSummary['streamMonthDat'][2]['amString'];?>],
								backgroundColor: 'rgba(28, 148, 191, 0.3)',
								fill:true,
								borderWidth: 0,
							},
							{
								label: 'Due Amount',
								stack: 'Stack 0',
								yAxisID: 'y-axis-0',
								data: [<?php echo $monthSummary['streamMonthDat'][3]['amString'];?>],
								backgroundColor: 'rgba(28, 148, 191, 0.8)',
								fill:true,
								borderWidth: 0,
							},
							{
								label: 'Spot Pax',
								stack: 'Stack 1',
								yAxisID: 'y-axis-1',
								data: [<?php echo $monthSummary['streamMonthDat'][1]['paxString'];?>],
								backgroundColor: 'rgba(0,45,0,0.3)',
								fill:true,
								borderWidth: 0,
							},
							{
								label: 'Card Pax',
								stack: 'Stack 1',
								yAxisID: 'y-axis-1',
								data: [<?php echo $monthSummary['streamMonthDat'][2]['paxString'];?>],
								backgroundColor: 'rgba(28, 148, 191, 0.6)',
								fill:true,
								borderWidth: 0,
							},
							{
								label: 'Due Pax',
								stack: 'Stack 1',
								yAxisID: 'y-axis-1',
								data: [<?php echo $monthSummary['streamMonthDat'][3]['paxString'];?>],
								backgroundColor: 'rgba(28, 148, 191, 0.9)',
								fill:true,
								borderWidth: 0,
							},
						]
						},
						options: {
							maintainAspectRatio: false,
							legend: {
								display: false
							},
							layout: {
								padding: {
									left: 4,
									right: 4,
									top: 12,
									bottom: 2
								}
							},
							scales: {
								xAxes: [{
									stacked:true,
									scaleLabel:{
										display: true
									},
									gridLines:{
										display: true,
										drawBorder: true,
									},
									ticks:{
										display:true
									}

								}],
								yAxes: [{
									type:'linear',
									display: true,
									position: 'left',
									id: 'y-axis-0',
									stacked:true,
									gridLines:{
										display: false,
										drawBorder: false,
									},
									ticks:{
										display:true,
										beginAtZero: true
									}
								},{
									type:'linear',
									display: true,
									position: 'right',
									id: 'y-axis-1',
									stacked:true,
									gridLines:{
										display: true,
										drawBorder: false,
									},
									ticks:{
										display:true,
										beginAtZero: true
									}
								}
							]
							}
						}
					});
				</script>
			</div>
	</div>
	</div>
