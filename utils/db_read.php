<?php
$servername = "localhost";
$username = "mga";
$password = "@lpha7MGA1";
$dbname = "mga";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>

