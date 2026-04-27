<?php
include "db.php";

$expression = $_POST['expression'];
$result = $_POST['result'];

$sql = "INSERT INTO history (expression, result) VALUES ('$expression','$result')";
mysqli_query($conn, $sql);
?>