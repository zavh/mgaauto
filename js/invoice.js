function dateCompare(fromd, tod){
    var std = Date.parse(fromd);
    var end = Date.parse(tod);

	if((end - std)>0)
        return true;
    else return false;
}

 function invCondition() {
    var selectBox = document.getElementById("client_type");
    var selectedValue = selectBox.options[selectBox.selectedIndex].value;
	var x = document.getElementsByClassName("spel");
	var y = document.getElementsByClassName("els");
	for(i=0;i<(x.length);i++){
		x[i].style.display = "none";
		y[i].disabled = true;
	}
	var el = document.getElementById("el-"+selectedValue);
	var tr = document.getElementById("spel-"+selectedValue);
	el.disabled = false;
	tr.style.display = "";
}

function showReportSelector(arrow){

	if(arrow == "down"){
		document.getElementById("reportform").style.top = "0";
    showQuerySelector('up');
	}
	else if(arrow == "up"){
		document.getElementById("reportform").style.top = "-300px";

    var x = document.getElementsByClassName("spel");
    var y = document.getElementsByClassName("els");
    var selectBox = document.getElementById("client_type");
    selectBox.selectedIndex = 0;
    for(i=0;i<(x.length);i++){
      x[i].style.display = "none";
      y[i].disabled = true;
    }
	}
}

function showQuerySelector(arrow){
  if(arrow == "down"){
		document.getElementById("paidinvquery").style.top = "0";
    showReportSelector('up');
	}
  else if(arrow == "up"){
		document.getElementById("paidinvquery").style.top = "-300px";
  }
}

function viewInvoice(archive, mode, invid){
	var invoiceForm = document.createElement("form");
	invoiceForm.target = "Invoice";
	invoiceForm.method = "POST";
	invoiceForm.action = "invoiceparser.php";

	var archiveInput = document.createElement("input");
    archiveInput.type = "hidden";
    archiveInput.name = "archive";
    archiveInput.value = archive;
    invoiceForm.appendChild(archiveInput);

  var modeInput = document.createElement("input");
    modeInput.type = "hidden";
    modeInput.name = "mode";
    modeInput.value = mode;
    invoiceForm.appendChild(modeInput);

  var idInput = document.createElement("input");
    idInput.type = "hidden";
    idInput.name = "invid";
    idInput.value = invid;
    invoiceForm.appendChild(idInput);

	var comInput = document.createElement("input");
    comInput.type = "hidden";
    comInput.name = "command";
    comInput.value = "archive";
    invoiceForm.appendChild(comInput);
	document.body.appendChild(invoiceForm);

	invoice = window.open("", "Invoice", "status=0,title=0,height=842,width=595,scrollbars=1");

	if (invoice) {
		invoiceForm.submit();
	} else {
		alert('You must allow popups to view invoice.');
	}

}

function genInvoice(ctype, ctype_id, fromd, tod){
	var invoiceForm = document.createElement("form");
	invoiceForm.target = "Invoice";
	invoiceForm.method = "POST";
	invoiceForm.action = "invoiceparser.php";

	var ctypeInput = document.createElement("input");
    ctypeInput.type = "hidden";
    ctypeInput.name = "ctype";
    ctypeInput.value = ctype;
    invoiceForm.appendChild(ctypeInput);
	document.body.appendChild(invoiceForm);

	var idInput = document.createElement("input");
    idInput.type = "hidden";
    idInput.name = "name";
    idInput.value = ctype_id;
    invoiceForm.appendChild(idInput);
	document.body.appendChild(invoiceForm);

	var fromdInput = document.createElement("input");
    fromdInput.type = "hidden";
    fromdInput.name = "fromd";
    fromdInput.value = fromd;
    invoiceForm.appendChild(fromdInput);
	document.body.appendChild(invoiceForm);

	var todInput = document.createElement("input");
    todInput.type = "hidden";
    todInput.name = "tod";
    todInput.value = tod;
    invoiceForm.appendChild(todInput);
	document.body.appendChild(invoiceForm);

	var comInput = document.createElement("input");
    comInput.type = "hidden";
    comInput.name = "command";
    comInput.value = "new";
    invoiceForm.appendChild(comInput);
	document.body.appendChild(invoiceForm);

	invoice = window.open("", "Invoice", "status=0,title=0,height=842,width=595,scrollbars=1");

	if (invoice) {
		invoiceForm.submit();
	} else {
		alert('You must allow popups for this map to work.');
	}
}
function processInvoice(){
    var fromd = document.getElementById('startdate').value;
    var tod = document.getElementById('enddate').value;
    if(!dateCompare(fromd, tod)){
        alert("From date should be earlier than To Date");
        return;
    }

    var selectBox = document.getElementById("client_type");
    var ctype = selectBox.options[selectBox.selectedIndex].value;
    var ctype_id = '';
    if(ctype==3){
        ctype_id = document.getElementById("el-2").value;
        if(ctype_id == ''){
            alert("Corporate Name is Empty!");
            return;
        }
    }
    if(ctype==2){
        ctype_id = document.getElementById("el-3").value;paydiv
        if(ctype_id == ''){
            alert("Bank Code is Empty!");
            return;
        }
    }
    if(ctype == 2 || ctype == 3){
        genInvoice(ctype, ctype_id, fromd, tod);
    }
}
function deleteInvoice(id, ref, command){
    var txt;
    var r = confirm("Confirm "+command+" of "+ref);
    if (r == true) {
        var invoiceForm = document.createElement("form");
        invoiceForm.target = "";
        invoiceForm.method = "POST";
        invoiceForm.action = "invoiceactions.php";

        var invoiceInput = document.createElement("input");
        invoiceInput.type = "hidden";
        invoiceInput.name = "archive";
        invoiceInput.value = id;
        invoiceForm.appendChild(invoiceInput);
        document.body.appendChild(invoiceForm);

        var commandInput = document.createElement("input");
        commandInput.type = "hidden";
        commandInput.name = "command";
        commandInput.value = command;
        invoiceForm.appendChild(commandInput);
        document.body.appendChild(invoiceForm);

        invoiceForm.submit();
    } else {
        return;
    }
}
function markAsPaidInvoice(aElm, id){
    var paydivs = document.getElementsByClassName("payday");
    for(var i=0;i<paydivs.length;i++){
        paydivs[i].style.display = 'none';
    }
    var targetPayDiv = document.getElementById("payid-"+id);
    targetPayDiv.style.display='block';
    targetPayDiv.addEventListener('dblclick', function(event){
        targetPayDiv.style.display='none';
    })
}

function searchInv(e, el){
  var x = e.which;
  if(x == 13){
    var searchInput = document.getElementById(el).value;
    searchInput = searchInput.trim();
    if(searchInput==''){
      alert('No search criteria provided');
      return;
    }
    else if(el == 'invref'){
      var criteria = searchInput.split(' ');
      if(criteria.length < 2)
        alert('At least two Search criteria required. Example: 2018 July or 2018 July SCB');
      else {
        ajaxInvFunction('searchByRef', criteria, 'paidInvoiceDiv');
      }
    }
    else if(el == 'payperiod'){
      ajaxInvFunction('searchByPeriod', searchInput, 'paidInvoiceDiv');
    }
  }
}

//Main Ajax function
function ajaxInvFunction(instruction, execute_id, divid){
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
		ajaxRequest.onreadystatechange = function(){
				if(ajaxRequest.readyState == 4 && ajaxRequest.status == 200){
				    var ajaxDisplay = document.getElementById(divid);
				    ajaxDisplay.innerHTML = ajaxRequest.responseText;
				}
	   }
		if(instruction == "searchByRef"){
			var criteria = JSON.stringify(execute_id);
			var queryString = "?search=byref&criteria="+criteria;
			ajaxRequest.open("GET", "invoicePAIDarchive.php" + queryString, true);
			ajaxRequest.send();
		}
    if(instruction == "searchByPeriod"){
			var queryString = "?search=bydate&datestring="+execute_id;
			ajaxRequest.open("GET", "invoicePAIDarchive.php" + queryString, true);
			ajaxRequest.send();
		}
    if(instruction == "resetSearchArea"){
			ajaxRequest.open("GET", "invoicePAIDarchive.php", true);
			ajaxRequest.send();
		}
}

function payStage(id, stage, dir){
  var nextstage = stage + 1;
  var backstage = stage - 1;
  if(stage==1){
    if(validatePay(id)){
      document.getElementById('input-'+stage+'-'+id).style.display = 'none';
      document.getElementById('input-'+nextstage+'-'+id).style.display = '';
    }
  }
  else {
    document.getElementById('input-'+stage+'-'+id).style.display = 'none';
    if(dir == 'next')
      document.getElementById('input-'+nextstage+'-'+id).style.display = '';
    else if(dir == 'back')
      document.getElementById('input-'+backstage+'-'+id).style.display = '';

  }
}
function validatePay(id){
  var am = Number(document.getElementById('paidamount-'+id).value);
  if(isNaN(am) || am == ''){
    alert('Amount must be a number');
    return false;
  }
  var ar = Number(document.getElementById('inv_arrear-'+id).value);
  if(am>ar){
    alert('Payment exceeding arrear ('+ar+') not acceptable');
    return false;
  }
  var pd = document.getElementById("paydate-"+id).value;
  var pdate = new Date(pd);
  if(pdate == 'Invalid Date'){
    alert('Payment date is invalid');
    return false;
  }
  return true;
}
