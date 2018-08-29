// Specil requirement (Bill request form) Description show/hide
function toggle(){
	var others = document.getElementById("others").checked;
	if (others)
	{
		document.getElementById("hide").style.display = "block";
		document.getElementById("other_description").disabled = false;
	}
	else
	{
		document.getElementById("hide").style.display = "none";
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
    document.getElementsByName('req[other]')[0].style.display = 'none';
}

function editAssigned(divid, e){
	var x = e.which;
	if(x == 13){
		var curdiv = document.getElementById(divid);
		curdiv.blur();
		var content = curdiv.textContent;
		ajaxFunction("changeassigned", content, divid);
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
			ajaxRequest.open("GET", "utils/update.php" + queryString, true);
			ajaxRequest.send();
		}
}

function w3_open() {
    document.getElementById("mySidebar").style.display = "block";
    document.getElementById("myOverlay").style.display = "block";
}
function w3_close() {
    document.getElementById("mySidebar").style.display = "none";
    document.getElementById("myOverlay").style.display = "none";
}

function upActivityCounter(){
    var d = new Date();
    document.getElementById("lastActiveTime").value=d.getTime();
}
//var myVar = setInterval(checkActivity, 300000);

function checkActivity(){
    var lastActivity = document.getElementById("lastActiveTime").value;
    var d = new Date();
    var nowTimeStamp = d.getTime();
    var diff = nowTimeStamp - lastActivity;
    if(diff>600000){
        if (typeof iam !== 'undefined') {
            window.close();
        }
    }
    if(diff>900000){
        window.location.assign("http://localhost:8080/mga/logout.php")}
    console.log(lastActivity+" - "+nowTimeStamp+" And difference is "+diff);
}
