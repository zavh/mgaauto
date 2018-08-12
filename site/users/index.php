<?php
	include($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	include(TEMPLATEDIR."/header.php");
	include(TEMPLATEDIR."/mainmenu.php");
?>
<div class="w3-gray w3-row" style="min-height:100vh">
	<!-- Top Menu Starts-->
	<?php
		$pagetitle = "User Management";
		include(TEMPLATEDIR."/topmenu.php");
	?>
	<!-- Top Menu Ends-->
	<div class="w3-row w3-center w3-margin">
		<div class="w3-col m12 l4 w3-tiny">
		<!-- New User Creation Form Starts-->
		<?php if($_SESSION['level']>9) {?>
			<form class="w3-margin w3-card w3-round-xxlarge w3-white" style="overflow:hidden" method="POST" action="userpost.php">
			<div class="w3-container w3-sand w3-center">
			  <p>Create New User</p>
			</div>
			  <!-- <label>Username</label></p> -->
			  <input class="w3-input w3-padding-large" type="email" name="uid" placeholder="Insert Username" required>
			  <p>
				<!-- <label>Password</label> -->
				<input
					class="w3-input w3-padding-large"
					type="password"
					name="pwd"
					placeholder="Insert Password" required
					pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
					onfocus='showMsgDiv()'
					onblur = 'hideMsgDiv()'
					onkeyup = 'validatePassCriteria(this)'
					>
			  </p>
			  <p>
				<!-- <label>Password</label> -->
				<input class="w3-input w3-padding-large" type="number" name="level" placeholder="Insert User Level" min='1' max='10' required>
			  </p>
			  <p class="w3-row w3-center w3-tiny"><button type="submit" >GO</button></p>
			  <input type='hidden' name='command' value='newuser'>
			</form>
		<?php }?>
		<!-- New User Creation Form Ends-->
		<!-- Change Password Form Starts-->
			<form class="w3-margin w3-card w3-round-xxlarge w3-white" style="overflow:hidden" method="POST" action="userpost.php" id='changePassForm' onsubmit="return passChangeValidate()">
			<div class="w3-container w3-sand w3-center">
			  <p>Change Password</p>
			</div>
			  <!-- <label>Username</label></p> -->
			  <input class="w3-input w3-padding-large" type="password" name="existing_password" placeholder="Insert Current Password" required>
			  <p>
				<!-- <label>Password</label> -->
				<input
					class="w3-input w3-padding-large"
					type="password"
					name="newpassword"
					placeholder="Insert New Password" required
					id='newpassword'
					pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
					onfocus='showMsgDiv()'
					onblur = 'hideMsgDiv()'
					onkeyup = 'validatePassCriteria(this)'
					>
			  </p>
			  <p>
				<!-- <label>Password</label> -->
				<input class="w3-input w3-padding-large" type="password" name="verifynew" placeholder="Re-enter New Password" required id='verifynew'>
			  </p>
			  <p class="w3-row w3-center w3-tiny"><button type='submit'>GO</button></p>
			  <input type='hidden' name='command' value='changepasswd'>
			</form>
			<div id="message" class="w3-margin w3-card w3-round" style="overflow:hidden;display:none;text-align:left" >
			<div class="w3-container w3-center">
			  Password must contain the following:
			</div>
				<p id="letter" class="invalid">A <b>lowercase</b> letter</p>
				<p id="capital" class="invalid">A <b>capital (uppercase)</b> letter</p>
				<p id="number" class="invalid">A <b>number</b></p>
				<p id="length" class="invalid">Minimum <b>8 characters</b></p>
			</div>
			<!-- Change Password Form Ends-->
		</div>
		<?php if($_SESSION['level']>9){?>
		<div class="w3-col m12 l4 w3-tiny">
			<div class="w3-margin w3-card w3-round-xxlarge" style="overflow:hidden">
					<div class="w3-container w3-pale-red w3-center">
					  <p>Manage Users</p>
					</div>
				<div id='userlist'>
					<?php include("userlist.php")?>
				</div>
			</div>
		</div>
	<?php }?>
	</div>
	<input type='hidden' id='errmsg' value='0'>
	<div id="snackbar"></div>
			<?php
				if(isset($_GET['response'])){
						switch ($_GET['response']) {
							case '100':
								$errmgs = "User \"".$_GET['user']."\" created successfully";
								echo "<input type='hidden' id='errmsg' value='$errmgs|100'>";
								break;
							case '101':
								$errmgs = "Password for User \"".$_GET['user']."\" changed successfully";
								echo "<input type='hidden' id='errmsg' value='$errmgs|101'>";
								break;
							case '102':
								$errmgs = "User removed successfully";
								echo "<input type='hidden' id='errmsg' value='$errmgs|101'>";
								break;
							case '1':
								$errmgs = "User creation failed for User: ".$_GET['user'];
								echo "<input type='hidden' id='errmsg' value='$errmgs|1'>";
								break;
							case '2':
								$errmgs = "Password did not match for User: ".$_GET['user'];
								echo "<input type='hidden' id='errmsg' value='$errmgs|2'>";
								break;
							case '3':
								$errmgs = "Your User Level is not allowed perform this operation";
								echo "<input type='hidden' id='errmsg' value='$errmgs|2'>";
								break;
							default:
								echo $_GET['response'];
						}

				}
			?>
</div>
<?php
	include(TEMPLATEDIR."/footer.php");
?>
<script>
function passChangeValidate(){
	if(!reEnterVerify()) return false;
}
function reEnterVerify(){
	var newpass = document.getElementById("newpassword").value;
	var verpass = document.getElementById("verifynew").value;

	if(newpass == verpass)
		document.getElementById("changePassForm").submit();
	else {
		alert("New password and Re-entered password didn't match");
		return false;
	}
}


var letter = document.getElementById("letter");
var capital = document.getElementById("capital");
var number = document.getElementById("number");
var length = document.getElementById("length");

function showMsgDiv(){
	document.getElementById("message").style.display = "block";
}
function hideMsgDiv(){
	document.getElementById("message").style.display = "none";
}

function validatePassCriteria(passfield) {
  // Validate lowercase letters
  var lowerCaseLetters = /[a-z]/g;
  if(passfield.value.match(lowerCaseLetters)) {
    letter.classList.remove("invalid");
    letter.classList.add("valid");
  } else {
    letter.classList.remove("valid");
    letter.classList.add("invalid");
  }

  // Validate capital letters
  var upperCaseLetters = /[A-Z]/g;
  if(passfield.value.match(upperCaseLetters)) {
    capital.classList.remove("invalid");
    capital.classList.add("valid");
  } else {
    capital.classList.remove("valid");
    capital.classList.add("invalid");
  }

  // Validate numbers
  var numbers = /[0-9]/g;
  if(passfield.value.match(numbers)) {
    number.classList.remove("invalid");
    number.classList.add("valid");
  } else {
    number.classList.remove("valid");
    number.classList.add("invalid");
  }

  // Validate length
  if(passfield.value.length >= 8) {
    length.classList.remove("invalid");
    length.classList.add("valid");
  } else {
    length.classList.remove("valid");
    length.classList.add("invalid");
  }
}
function deleteUser(table_id){
	var userActionForm = document.createElement("form");
	userActionForm.target = "";
	userActionForm.method = "POST";
	userActionForm.action = "userpost.php";

	var commandInput = document.createElement("input");
    commandInput.type = "hidden";
    commandInput.name = "command";
    commandInput.value = "deleteuser";
    userActionForm.appendChild(commandInput);
	document.body.appendChild(userActionForm);

	var idInput = document.createElement("input");
    idInput.type = "hidden";
    idInput.name = "table_id";
    idInput.value = table_id;
    userActionForm.appendChild(idInput);
	document.body.appendChild(userActionForm);

	userActionForm.submit();
}

function changeLevel(input, originput, table_id, e){
	var x = e.which;
	if(x == 13){
		origval = parseInt(document.getElementById(originput).value);
		newval = parseInt(document.getElementById(input).value);
		if(origval == newval) {
			alert('No change in User Level!');
			return;
		}
		else if(newval<1 || newval>10 || !Number.isInteger(newval)){
			alert('User Level should be between 1 and 20');
			return;
		}
		else {
			userAjaxFunction('changelevel', newval+"|"+table_id+"|"+input, 'userlist');
		}
	}
}

function userAjaxFunction(instruction, execute_id, divid){
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
						document.getElementById("errmsg").value = ajaxRequest.responseText;
						msgInASnackbar();
						var status = ajaxRequest.responseText.split("|");
						if(parseInt(status[1])<100){
							document.getElementById(config[2]).value = document.getElementById("orig-"+config[2]).value;
							return;
						}
						else userAjaxFunction('refreshList', '', 'userlist');
                    }
					if(instruction=="resetpass"){
						document.getElementById("errmsg").value = ajaxRequest.responseText;
						msgInASnackbar();
						return;
					}
				    var ajaxDisplay = document.getElementById(divid);
				    ajaxDisplay.innerHTML = ajaxRequest.responseText;
				}
	   }
        if(instruction == "changelevel"){
			var config = execute_id.split("|");
			execute_id = config[0]+"|"+config[1];
            ajaxRequest.open("POST", "userpost.php", true);
            ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxRequest.send("command=changelevel&config="+execute_id);
        }
        if(instruction == "resetpass"){
            ajaxRequest.open("POST", "userpost.php", true);
            ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            ajaxRequest.send("command=resetpass&table_id="+execute_id);
        }
		if(instruction == "refreshList"){
			ajaxRequest.open("POST", "userlist.php", true);
			ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajaxRequest.send();
		}
}

</script>
<script src="<?php echo JSDIR;?>/snackbar.js"></script>
<style>
/* Style all input fields */

/* The message box is shown when the user clicks on the password field */
#message {
    display:none;
    background: #f1f1f1;
    color: #000;
    position: relative;
    padding: 0px;
    margin-top: 0px;
}

#message p {
    //padding: 10px 10px;
    font-size: 10px;
	margin-left:50px;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
    color: green;
}

.valid:before {
    position: relative;
    left: -10px;
    content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
    color: red;
}

.invalid:before {
    position: relative;
    left: -10px;
    content: "✖";
}
</style>
