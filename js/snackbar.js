
function msgInASnackbar() {
    if(document.getElementById("errmsg").value == 0) 
        return; 
    var rawMsg = document.getElementById("errmsg").value.split("|");
	var msg = rawMsg[0];
    var status = rawMsg[1];
	if (msg === null){}
	else {
    var x = document.getElementById("snackbar");
    if(status>99)x.style.backgroundColor = "#085c1b";
    x.className = "show";
	x.innerHTML = msg;
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 10000);
	}
} 
msgInASnackbar();