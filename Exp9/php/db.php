<?php
// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "calcdb";   // change if your DB name is different

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: set charset (good practice)
mysqli_set_charset($conn, "utf8");
?>