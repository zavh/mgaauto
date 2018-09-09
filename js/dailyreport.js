// Special requirement (Bill request form) Description show/hide
function toggle(){
	var others = document.getElementById("others").checked;
	if (others)
	{
		//document.getElementById("hide").style.display = "inline";
		document.getElementById("other_description").disabled = false;
	}
	else
	{
		//document.getElementById("hide").style.display = "none";
		document.getElementById("other_description").disabled = true;
	}
}
//Toggle show/hide Bill request form [Bank and Corporate]
function addInfo(ctype, div, fields)
{
    if(ctype==1){
        document.getElementsByName('payment')[0].value = 1;
        document.getElementById('payment').selectedIndex = 1;
    }
    if(ctype==2){
        document.getElementsByName('payment')[0].value = 3;
        document.getElementById('payment').selectedIndex = 3;
    }
    if(ctype==3){
        document.getElementsByName('payment')[0].value = 2;
        document.getElementById('payment').selectedIndex = 2;
    }

	var i = 0;
	var divs = document.getElementsByClassName("spdiv");
	var els  = document.getElementsByClassName("spel");
    //First Disabling all DIVs those are normally hidden
	for(i=0;i<divs.length;i++){
		divs[i].style.display = "none";
		els[i].disabled = true;
	}
    //Second Disabling all Input types which are normally disabled
	for(i=0;i<els.length;i++){
		els[i].disabled = true;
	}
    //Now enabling which needs to be enabled according to selection
	var el = fields.split("|");
	document.getElementById(div).style.display = "block";
	for(j=0;j<el.length;j++){
		document.getElementById(el[j]).disabled = false;
	}
}
//Bill request form Rest
function resetRequestModal() {
	document.getElementById('newbill').style.display='none';
  document.getElementById('billform').reset();
  document.getElementById('bank').style.display = 'none';
  document.getElementById('bank_id').disabled = true;
  document.getElementById('card_no').disabled = true;
  document.getElementById('corporate').style.display = 'none';
  document.getElementById('corporate_id').disabled = true;
  document.getElementById('editrequest').disabled = true;
  document.getElementById('predata').disabled = true;
  document.getElementById('other_description').disabled = true;
  //document.getElementsByName('req[other]')[0].style.display = 'none';
}

function editAssigned(divid, e){
	var x = e.which;
	if(x == 13){
		var curdiv = document.getElementById(divid);
		curdiv.blur();
		var content = curdiv.textContent.trim();
		ajaxFunction("changeassigned", content, divid);

		y = document.getElementById(divid+'-changed');
		if(y == null){
			var x = document.createElement("INPUT");
	    x.setAttribute("type", "hidden");
	    x.setAttribute("value", content);
	    x.setAttribute("id", divid+'-changed');
	    document.body.appendChild(x);
		}
		else {
			document.getElementById(divid+'-changed').value = content;
		}
	}
}

function showViewMode(evt, mode){
	var x = document.getElementsByClassName('viewmode');
	var i;
	var panelleft = document.getElementById("report");
	var panelright = document.getElementById("record");

	var reportcontainer = document.getElementById("reportcontainer");
	var spot = document.getElementById('spot');
	var card = document.getElementById('card');
	var due = document.getElementById('due');
	var summarycontainer = document.getElementById("summarycontainer");
	for(i=0;i<x.length;i++){
		x[i].className = x[i].className.replace(" active", "");
	}
	evt.currentTarget.className += " active";

	if(mode == 1){
		panelleft.style.display = "block";
		panelleft.className = panelleft.className.replace(" w3-rest ");
		panelleft.className += " w3-third ";
		panelright.style.display = "block";
		panelright.className = panelright.className.replace(" w3-rest ");
		panelright.className += " w3-twothird ";

		reportcontainer.className = reportcontainer.className.replace(" w3-row ");
		spot.className = spot.className.replace(" w3-third drfullheight ");
		card.className = card.className.replace(" w3-third drfullheight ");
		due.className = due.className.replace(" w3-third drfullheight ");
		summarycontainer.className = summarycontainer.className.replace(" w3-row ");
	}
	if(mode == 2){
		panelright.style.display = "none";
		panelright.className = panelright.className.replace(" w3-twothird ");
		panelleft.className = panelleft.className.replace(" w3-third ");
		panelleft.className += " w3-rest ";
		panelleft.style.display = "block";

		reportcontainer.className += " w3-row ";
		spot.className += " w3-third drfullheight ";
		card.className += " w3-third drfullheight ";
		due.className += " w3-third drfullheight ";
		summarycontainer.className += " w3-row ";
	}
	if(mode == 3){
		panelleft.style.display = "none";
		panelleft.className = panelleft.className.replace(" w3-third ");
		panelright.style.display = "block";
		panelright.className = panelright.className.replace(" w3-twothird ");
		panelright.className += " w3-rest ";

		reportcontainer.className = reportcontainer.className.replace(" w3-row ");
		spot.className = spot.className.replace(" w3-third drfullheight ");
		card.className = card.className.replace(" w3-third drfullheight ");
		due.className = due.className.replace(" w3-third drfullheight ");
		summarycontainer.className = summarycontainer.className.replace(" w3-row ");
	}
}
//Main Ajax function
function ajaxFunction(instruction, execute_id, divid){
	var ajaxRequest;  // The variable that makes Ajax possible!
		try{
				// Opera 8.0+, Firefox, Safari
				ajaxRequest = new XMLHttpRequest();
		} catch (e){
				// Internet Explorer Browsers
				try{
						ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
						try{
								ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
						} catch (e){
								// Something went wrong
								alert("Your browser broke!");
								return false;
						}
				}
		}
		// Create a function that will receive data sent from the server
		ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200){
                    if(instruction=="changelevel"){
                        alert(ajaxRequest.responseText);
                    }
				    var ajaxDisplay = document.getElementById(divid);
				    ajaxDisplay.innerHTML = ajaxRequest.responseText;
				}
	   }
//Changing the assigned service provider
		if(instruction == "changeassigned"){
			var id = divid.split("-").pop();
			var queryString = "?newassgn="+encodeURIComponent(execute_id);
			queryString += "&id="+id;
			//alert(queryString);
			ajaxRequest.open("GET", "../../utils/update.php" + queryString, true);
			ajaxRequest.send();
		}
}
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
        //document.getElementById("hide").style.display = 'inline';
        document.getElementById("other_description").value = reqObj[i+1];
        document.getElementById("other_description").disabled = false;
      }
      else
        document.getElementsByName("req["+reqObj[i]+"]")[0].checked = true;
    }
  }
}
function cancelEntry(json){
	var x = confirm("This will Cancel the Request. Confirm to proceed.");
	if(x == true){
		var cancelForm = document.createElement("form");
      cancelForm.method = "POST";
      cancelForm.action = "requestpost.php";

    var idInput = document.createElement("input");
      idInput.type = "hidden";
      idInput.name = "reqid";
      idInput.value = json.id;
      cancelForm.appendChild(idInput);

    var commandInput = document.createElement("input");
      commandInput.type = "hidden";
      commandInput.name = "cancelrequest";
      commandInput.value = true;
      cancelForm.appendChild(commandInput);

    var dateInput = document.createElement("input");
      dateInput.type = "hidden";
      dateInput.name = "dtravel";
      dateInput.value = json.flight_date;
      cancelForm.appendChild(dateInput);

    var dataInput = document.createElement("input");
      dataInput.type = "hidden";
      dataInput.name = "reqdat";
      dataInput.value = JSON.stringify(json);
      cancelForm.appendChild(dataInput);

    document.body.appendChild(cancelForm);
    cancelForm.submit();
	}
}
function undoCancel(json){
	var x = confirm("Please confirm Undoing the cancel.");
	if(x == true){
		var undocancelForm = document.createElement("form");
			undocancelForm.method = "POST";
			undocancelForm.action = "requestpost.php";

		var idInput = document.createElement("input");
      idInput.type = "hidden";
      idInput.name = "reqid";
      idInput.value = json.id;
      undocancelForm.appendChild(idInput);

		var commandInput = document.createElement("input");
      commandInput.type = "hidden";
      commandInput.name = "undocancel";
      commandInput.value = true;
      undocancelForm.appendChild(commandInput);

		var dateInput = document.createElement("input");
      dateInput.type = "hidden";
      dateInput.name = "dtravel";
      dateInput.value = json.flight_date;
      undocancelForm.appendChild(dateInput);

		var dataInput = document.createElement("input");
      dataInput.type = "hidden";
      dataInput.name = "reqdat";
      dataInput.value = JSON.stringify(json);
      undocancelForm.appendChild(dataInput);

    document.body.appendChild(undocancelForm);
    undocancelForm.submit();
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

function showPaymentDetails(el){
  id = el.id;
  var mode = el.innerText;
  var showFlag = '';
  if(mode == '+') {
    el.innerText = '-';
  }
  else if(mode == '-') {
    el.innerText = '+';
    showFlag = 'none';
  }
  var rows = document.getElementsByClassName(id);

  var rowcount = rows.length;
  for(i=0;i<rowcount;i++){
    rows[i].style.display=showFlag;
  }

}
