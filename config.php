<?php
$servername = "localhost"; 
$dbusername = "root"; 
$dbpassword = ""; 
$dbname = "plag_check"; 

// Create connection
$mysqli = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
return $mysqli;
?>
