<?php
include "db.php";

$result = mysqli_query($conn, "SELECT * FROM history ORDER BY id DESC");

while($row = mysqli_fetch_assoc($result)){
  echo "<div class='history-item'>";
  echo $row['expression']." = <b>".$row['result']."</b>";
  echo "</div>";
}
?>