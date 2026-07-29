<?php

$conn = mysqli_connect("localhost", "root", "", "library_management_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>