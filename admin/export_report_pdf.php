<?php
session_start();
require_once("../database/db.php");
require_once("../fpdf186/fpdf.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    die("Access Denied!");
}

$from = isset($_GET['from']) ? $_GET['from'] : "";
$to   = isset($_GET['to']) ? $_GET['to'] : "";

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',18);
$pdf->Cell(190,10,'Hotel Room Booking System',0,1,'C');

$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,10,'Booking Report',0,1,'C');

$pdf->SetFont('Arial','',10);

if($from != "" && $to != ""){
    $pdf->Cell(190,8,"Date: $from To $to",0,1,'C');
}

$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(15,10,'ID',1);
$pdf->Cell(45,10,'User',1);
$pdf->Cell(45,10,'Room',1);
$pdf->Cell(25,10,'Amount',1);
$pdf->Cell(30,10,'Status',1);
$pdf->Cell(30,10,'Created',1);

$pdf->Ln();

$sql = "SELECT
            bookings.id,
            users.full_name,
            rooms.room_name,
            bookings.total_amount,
            bookings.booking_status,
            DATE(bookings.created_at) AS created_date
        FROM bookings
        INNER JOIN users ON bookings.user_id = users.id
        INNER JOIN rooms ON bookings.room_id = rooms.id";

if($from != "" && $to != ""){
    $sql .= " WHERE DATE(bookings.created_at) BETWEEN '$from' AND '$to'";
}

$sql .= " ORDER BY bookings.id DESC";

$result = mysqli_query($conn, $sql);

$pdf->SetFont('Arial','',9);

while($row = mysqli_fetch_assoc($result)){

    $pdf->Cell(15,8,$row['id'],1);
    $pdf->Cell(45,8,substr($row['full_name'],0,22),1);
    $pdf->Cell(45,8,substr($row['room_name'],0,22),1);
    $pdf->Cell(25,8,"Rs.".$row['total_amount'],1);
    $pdf->Cell(30,8,$row['booking_status'],1);
    $pdf->Cell(30,8,$row['created_date'],1);

    $pdf->Ln();
}

$pdf->Output("I","Booking_Report.pdf");
exit();
?>