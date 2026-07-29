<?php
include("config/db.php");

if($conn){
    echo "<h1 style='color:green'>Database Connected Successfully ✅</h1>";
}else{
    echo "Connection Failed";
}
?>