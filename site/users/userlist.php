<?php
if(!isset($_SERVER['HTTP_REFERER']) && ((count(get_included_files()) ==1))){
		header ('Location:index.php');
}
else if(isset($_SERVER['HTTP_REFERER']) && ((count(get_included_files()) ==1))){
	require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
	require_once(UTILSDIR."/essentials_open.php");
}
//else {
//	require_once($_SERVER['DOCUMENT_ROOT']."/mga/config/serverconfig.php");
//	require_once(UTILSDIR."/essentials_open.php");
//}
$userObj = new DBTables($con, 'users');
$sql = "SELECT * FROM `users`";
$users = $userObj->getSqlResult($sql);
$userTab = "<table class='w3-table w3-striped w3-tiny'>
				<tr>
					<th>Username</th>
					<th>User Level</th>
					<th>User Action</th>
				</tr>";
$userTabSmall = "<table class='w3-table-all w3-tiny'>";
for($i=0;$i<count($users);$i++){
	if($users[$i]['table_id'] == $_SESSION['table_id']) continue;
	$userTab .= "<tr>
					<td>".$users[$i]['uid']."</td>
					<td>
						<input 
							type='number' 
							id='il-$i' 
							max=10 min=1 
							value='".$users[$i]['level']."' 
							style='width:40px;' 
							onkeypress=\"changeLevel('il-$i','orig-il-$i',".$users[$i]['table_id'].",event)\">
						<input type='hidden' id='orig-il-$i' value='".$users[$i]['level']."'>
					</td>
					<td > 
						<a href='javascript:void(0)' onclick=\"deleteUser('".$users[$i]['table_id']."')\">Delete</a>
						<a href='javascript:void(0)' onclick=\"userAjaxFunction('resetpass','".$users[$i]['table_id']."','')\">Reset Password</a>
					</td>
				</tr>";
	$userTabSmall .= "<tr>
						<td>User:</td>
						<td>".$users[$i]['uid']."</td>
					  </tr>
					  <tr>
						<td>Level:</td>
						<td>
							<input type='number' id='il-$i' max=10 min=1 value='".$users[$i]['level']."' style='width:40px;' onkeypress=\"changeLevel('il-$i',".$users[$i]['table_id'].")\">
							<input type='hidden' id='orig-il-$i' value='".$users[$i]['level']."'>
						</td>
					  </tr>
					  <tr>
						<td>Action:</td>
						<td>
							<a href='javascript:void(0)' onclick=\"deleteUser('".$users[$i]['table_id']."')\">Delete</a>
							<a href='javascript:void(0)' onclick='ajaxFunction(".$users[$i]['table_id'].")'>Reset Password</a>	
						</td>
					  </tr>";
}
$userTabSmall .="</table>";
$userTab .= "</table>";

echo "<div class='usertable-big'>".$userTab."</div>";
//echo "<div class='usertable-small'>".$userTabSmall."</div>";
?>