function publishInvoice(dbsave){
	var invdate = document.getElementById("inv-date").innerHTML;
    var invref = document.getElementById("inv-ref").innerHTML;
	var invaddr = document.getElementById("inv-addressing").innerHTML;
    var invsub = document.getElementById("inv-sub").innerHTML;
    var invfrd = document.getElementById("inv-forwarding").innerHTML;
    var invftr = document.getElementById("inv-footer").innerHTML;
    var invname = document.getElementById("inv-name").value;
    var invfromd = document.getElementById("inv-fromd").value;
    var invtod = document.getElementById("inv-tod").value;
    var invctype = document.getElementById("inv-ctype").value;
    var invcname = document.getElementById("inv-cname").value;
    var invSumPax = document.getElementById("inv-sumpax").innerHTML;
    var invSumAm = document.getElementById("inv-sumam").innerHTML;
    var invSumWord = document.getElementById("inv-aminword").innerHTML;

    var th = document.getElementsByTagName("th");
    var objInvTab = {
	       "tabheads" : [],
    	   "tabvals": [],
           "tabsumpax":invSumPax,
           "tabsumam":invSumAm,
           "tabsumword":invSumWord,
           "requestids":reqidjvar,
           "invdate":"",
           "invref":"",
           "invaddr":"",
           "invsub":"",
           "invfrd":"",
           "invfrd":"",
           "invname":invname,
           "invfromd":invfromd,
           "invtod":invtod,
           "invctype":invctype,
           "invcname":invcname
    } 

    for(var i=0;i<th.length;i++){
        objInvTab.tabheads.push(th[i].innerHTML);
    }
    
    var invdat = document.getElementsByClassName("inv-dat");
    for(i=0;i<invdat.length;i++){
            var tempObj = {
                "rowdata":[]
            };
        invrowdat = invdat[i].getElementsByTagName("td");
        for(var j=0;j<invrowdat.length;j++){
            tempObj.rowdata.push(invrowdat[j].innerHTML);
        }
        objInvTab.tabvals.push(tempObj) ;
    }
        
	var invJSON = JSON.stringify(objInvTab);

    var invoiceForm = document.createElement("form");
	invoiceForm.target = "Invoice";
	invoiceForm.method = "POST";
	invoiceForm.action = "invprocessor.php";
	
	var invdInput = document.createElement("input");
    invdInput.type = "hidden";
    invdInput.name = "invdate";
    invdInput.value = invdate;
    invoiceForm.appendChild(invdInput);
	document.body.appendChild(invoiceForm);

	var refInput = document.createElement("input");
    refInput.type = "hidden";
    refInput.name = "invref";
    refInput.value = invref;
    invoiceForm.appendChild(refInput);
	document.body.appendChild(invoiceForm);
    
	var addrInput = document.createElement("input");
    addrInput.type = "hidden";
    addrInput.name = "invaddr";
    addrInput.value = invaddr;
    invoiceForm.appendChild(addrInput);
	document.body.appendChild(invoiceForm);

	var subInput = document.createElement("input");
    subInput.type = "hidden";
    subInput.name = "invsub";
    subInput.value = invsub;
    invoiceForm.appendChild(subInput);
	document.body.appendChild(invoiceForm);
    
    var frdInput = document.createElement("input");
    frdInput.type = "hidden";
    frdInput.name = "invfrd";
    frdInput.value = invfrd;
    invoiceForm.appendChild(frdInput);
	document.body.appendChild(invoiceForm);
    
    var dataInput = document.createElement("input");
    dataInput.type = "hidden";
    dataInput.name = "invdat";
    dataInput.value = invJSON;
    invoiceForm.appendChild(dataInput);
	document.body.appendChild(invoiceForm);    
    
    var footerInput = document.createElement("input");
    footerInput.type = "hidden";
    footerInput.name = "invftr";
    footerInput.value = invftr;
    invoiceForm.appendChild(footerInput);
	document.body.appendChild(invoiceForm);

    var dbsaveInput = document.createElement("input");
    dbsaveInput.type = "hidden";
    dbsaveInput.name = "invsav";
    dbsaveInput.value = dbsave;
    invoiceForm.appendChild(dbsaveInput);
	document.body.appendChild(invoiceForm);

    invoiceForm.submit();
    window.print();
    window.opener.location.reload(false);
    window.close();
}