<?php
if(count(get_included_files()) ==1)
	include("index.php");

?>
		<form autocomplete="off" method = "post" action = "requestpost.php" id="billform">
			<div class="w3-row-padding w3-tiny" style="margin-top:32px">
				<div class="w3-quarter"><input class="w3-input" type="number" name="pnumber" min="1" placeholder="Number of person in party" required></div>
				<div class="w3-quarter w3-wide">
					<input type="radio" name="direction" value="0" required><label>Arrival</label><br>
					<input type="radio" name="direction" value="1" required><label>Departure</label>
				</div>
				<div class="w3-quarter">
					<label>Date of Travel</label>
					<input type = "date" name="dtravel" class="w3-input" required min="<?php //echo date("Y-m-d")?>">
				</div>
				<div class="w3-quarter">
					<label>Time of Travel</label>
					<input type = "time" name="ttravel" class="w3-input" required>
				</div>
			</div>
			<div class="w3-row w3-tiny">
				<div class="w3-twothird">
					<div class="w3-row-padding">
						<div class="w3-col">
						<input class="w3-input" type="text" name="pname" placeholder = "Passanger name/Group Leader" width="auto" required>
						</div>
					</div>
					<div class="w3-row-padding">
						<div class="w3-half"><input class="w3-input" type="number" name="tnumber" placeholder = "Telephone Number" required></div>
						<div class="w3-half"><input class="w3-input" type = "text" name="fnumber" placeholder = "Flight Number" required></div>
					</div>
					<div class="w3-row-padding">
						<div class="w3-col">
						<input class="w3-input" type = "text" name="provider" placeholder = "Service provided by" required>
						</div>
					</div>
					<div class="w3-row-padding">
						<div class="w3-half"><input class="w3-input" type="number" step = "0.01" name="amount" placeholder="Amount" required ></div>
						<div class="w3-half">
							<select class="w3-select" id="payment" required placeholder="Mode of Payment" disabled>
								<option disabled selected value="0"> Mode of Payment </option>
								<option value = "1" >Cash</option>
								<option value = "2" >Card</option>
								<option value = "3" >Due</option>
							</select>
							<input type='hidden' name="payment">
						</div>
					</div>
					<div class="w3-row-padding" style="margin-top:8px;">
						<div class="w3-quarter">Client Type</div>
						<div class="w3-quarter">
							<input type = "radio" name="ctype" value="1" required onclick = "addInfo(this.value,'','')">
							<label class="w3-wide">Spot</label>
						</div>
						<div class="w3-quarter">
							<input type = "radio" name="ctype" value="2" required onclick = "addInfo(this.value,'corporate','corporate_id')">
							<label>Corporate</label>
							<div class="hide spdiv autocomplete" id="corporate">
								<input class="spel w3-input" type="text" name="corporate_id" id="corporate_id" placeholder="Corporate" required disabled>
							</div>
						</div>
						<div class="w3-quarter">
							<input type = "radio" name="ctype" value="3" required onclick = "addInfo(this.value, 'bank', 'bank_id|card_no');">
							<label>Bank</label>
							<div class = "hide spdiv autocomplete" id = "bank">
								<input class="spel w3-input" type="text" name = "card_no" id="card_no" placeholder="Card Number" required disabled>
								<input class="spel w3-input" type="text" name = "bank_id" id="bank_id" placeholder="Bank Name" required disabled>
							</div>
						</div>
					</div>
				</div>
				<div class="w3-third w3-padding">
						<table class="w3-table-all w3-card-4" style=" table-layout: fixed;">
							<tr><th colspan=2>Special Requirements</th></tr>
							<tr><td>Hotel</td>			  <td><input type = "checkbox" name = "req[hotel]" ></td></tr>
							<tr><td>Transport to/from</td><td><input type = "checkbox" name = "req[transport]" ></td></tr>
							<tr><td>Extra Baggage	 </td><td><input type = "checkbox" name = "req[baggage]" ></td></tr>
							<tr><td>Baggage store	 </td><td><input type = "checkbox" name = "req[bag_store]" ></td></tr>
							<tr><td>Others			 </td><td><input type = "checkbox" id = "others" onclick = "toggle()"></td></tr>
							<tr><td colspan=2>
								<div class="hide" id="hide">
										<input class="w3-input" type = "text" required name = "req[other]" disabled id = "other_description">
									</div>
								</td></tr>
						</table>
				</div>
			</div>
			<div class="w3-row-padding w3-tiny" style="margin:16px 8px 16px 8px;">
					<button class="w3-button w3-block w3-blue">Submit</button>
			</div>
			<input type='hidden' name='editrequest' id='editrequest' value='true' disabled>
			<input type='hidden' name='predata' id='predata' disabled>
		</form>
