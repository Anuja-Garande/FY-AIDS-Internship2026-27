<?php

$conn = mysqli_connect("localhost","root","","inventory_management");

if(!$conn){
    die("Connection Failed : ".mysqli_connect_error());
}

if(!isset($_GET['id'])){
    header("Location:sales.php");
    exit();
}

$id = intval($_GET['id']);

$query = mysqli_query($conn,"
SELECT
sales.id,
sales.quantity,
sales.selling_price,
sales.total_price,
sales.sale_date,

products.product_name,

customers.customer_name,
customers.phone,
customers.email,
customers.address

FROM sales

INNER JOIN products
ON sales.product_id = products.id

INNER JOIN customers
ON sales.customer_id = customers.id

WHERE sales.id='$id'
");

$data = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>

Invoice #<?php echo $data['id']; ?>

</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{

background:#eef2f7;
padding:40px;

}

.invoice{

max-width:900px;
margin:auto;

background:#ffffff;

border-radius:20px;

overflow:hidden;

box-shadow:
0 20px 60px rgba(15,23,42,.15);

}

.invoice-header{

background:
linear-gradient(135deg,#2563eb,#1d4ed8);

padding:40px;

color:white;

display:flex;

justify-content:space-between;

align-items:center;

}

.company h1{

font-size:34px;

font-weight:700;

margin-bottom:8px;

}

.company p{

margin:2px 0;

opacity:.9;

font-size:14px;

}

.invoice-title{

text-align:right;

}

.invoice-title h2{

font-size:38px;

font-weight:700;

margin-bottom:10px;

letter-spacing:2px;

}

.invoice-title p{

margin:4px 0;

font-size:15px;

}

.invoice-body{

padding:40px;

}

.info-box{

display:flex;

justify-content:space-between;

gap:30px;

margin-bottom:35px;

}

.card-info{

flex:1;

background:#f8fafc;

border:1px solid #e2e8f0;

border-radius:16px;

padding:22px;

}

.card-info h5{

font-size:18px;

font-weight:600;

margin-bottom:18px;

color:#2563eb;

}

.card-info p{

margin-bottom:8px;

color:#475569;

font-size:15px;

}

.table{

margin-top:25px;

}

.table thead{

background:#2563eb;

color:white;

}

.table thead th{

padding:16px;

}

.table td{

padding:18px;

vertical-align:middle;

}

.total-box{

margin-top:35px;

display:flex;

justify-content:flex-end;

}

.total-card{

width:320px;

background:#2563eb;

color:white;

padding:25px;

border-radius:18px;

text-align:center;

}

.total-card h6{

opacity:.85;

margin-bottom:8px;

}

.total-card h2{

font-size:34px;

font-weight:700;

margin:0;

}

.footer{

margin-top:60px;

display:flex;

justify-content:space-between;

align-items:flex-end;

}

.signature{

text-align:center;

width:250px;

}

.signature .line{

border-top:2px solid #94a3b8;

margin-bottom:8px;

}

.thankyou{

font-size:18px;

font-weight:600;

color:#2563eb;

}

.print-btn{

position:fixed;

right:35px;

bottom:35px;

padding:14px 28px;

font-size:16px;

border:none;

border-radius:50px;

background:#2563eb;

color:white;

box-shadow:
0 10px 30px rgba(37,99,235,.35);

transition:.3s;

}

.print-btn:hover{

background:#1d4ed8;

transform:translateY(-3px);

}

@media print{

body{

background:white;

padding:0;

}

.print-btn{

display:none;

}

.invoice{

box-shadow:none;

border-radius:0;

max-width:100%;

}

}

</style>

</head>

<body>

<div class="invoice">

<div class="invoice-header">

<div class="company">

<h1>

<i class="bi bi-box-seam"></i>

InventoryPro

</h1>

<p>Inventory Management System</p>

<p>InventoryPro Solutions Pvt. Ltd.</p>

<p>Email : support@inventorypro.com</p>

<p>Phone : +91 98765 43210</p>

</div>

<div class="invoice-title">

<h2>INVOICE</h2>

<p><strong>Invoice No :</strong>

INV-<?php echo str_pad($data['id'],5,"0",STR_PAD_LEFT); ?>

</p>

<p><strong>Date :</strong>

<?php echo date("d M Y",strtotime($data['sale_date'])); ?>

</p>

</div>

</div>

<div class="invoice-body">

<!-- =========================
CUSTOMER & BILL INFORMATION
========================= -->

<div class="info-box">

    <div class="card-info">

        <h5>
            <i class="bi bi-person-circle"></i>
            Bill To
        </h5>

        <p>
            <strong>Customer :</strong>
            <?php echo htmlspecialchars($data['customer_name']); ?>
        </p>

        <p>
            <strong>Phone :</strong>
            <?php echo !empty($data['phone']) ? htmlspecialchars($data['phone']) : "N/A"; ?>
        </p>

        <p>
            <strong>Email :</strong>
            <?php echo !empty($data['email']) ? htmlspecialchars($data['email']) : "N/A"; ?>
        </p>

        <p>
            <strong>Address :</strong><br>
            <?php echo !empty($data['address']) ? nl2br(htmlspecialchars($data['address'])) : "N/A"; ?>
        </p>

    </div>

    <div class="card-info">

        <h5>
            <i class="bi bi-receipt"></i>
            Invoice Details
        </h5>

        <p>
            <strong>Invoice No :</strong>
            INV-<?php echo str_pad($data['id'],5,"0",STR_PAD_LEFT); ?>
        </p>

        <p>
            <strong>Invoice Date :</strong>
            <?php echo date("d M Y",strtotime($data['sale_date'])); ?>
        </p>

        <p>
            <strong>Payment Status :</strong>

            <span class="badge bg-success">
                Paid
            </span>

        </p>

        <p>
            <strong>Generated :</strong>
            <?php echo date("d M Y h:i A"); ?>
        </p>

    </div>

</div>


<!-- =========================
PRODUCT TABLE
========================= -->

<table class="table table-bordered">

    <thead>

        <tr>

            <th width="8%">#</th>

            <th>Product</th>

            <th width="15%">Quantity</th>

            <th width="18%">Unit Price</th>

            <th width="20%">Total</th>

        </tr>

    </thead>

    <tbody>

        <tr>

            <td class="text-center">

                1

            </td>

            <td>

                <strong>

                    <?php echo htmlspecialchars($data['product_name']); ?>

                </strong>

            </td>

            <td class="text-center">

                <?php echo $data['quantity']; ?>

            </td>

            <td class="text-end">

                ₹ <?php echo number_format($data['selling_price'],2); ?>

            </td>

            <td class="text-end fw-bold">

                ₹ <?php echo number_format($data['total_price'],2); ?>

            </td>

        </tr>

    </tbody>

</table>


<!-- =========================
TOTAL SECTION
========================= -->

<div class="total-box">

    <div class="total-card">

        <h6>

            Grand Total

        </h6>

        <h2>

            ₹ <?php echo number_format($data['total_price'],2); ?>

        </h2>

        <small>

            Inclusive of all applicable taxes

        </small>

    </div>

</div>

<!-- =========================
NOTES & SIGNATURE
========================= -->

<div class="footer">

    <div>

        <h5 class="thankyou">

            Thank You!

        </h5>

        <p class="mt-3 text-secondary">

            We sincerely appreciate your business.

        </p>

        <p class="text-secondary">

            If you have any questions regarding this invoice,
            please contact our support team.

        </p>

        <p class="text-secondary">

            Email :
            support@inventorypro.com

        </p>

        <p class="text-secondary">

            Phone :
            +91 98765 43210

        </p>

    </div>


    <div class="signature">

        <div style="height:70px;"></div>

        <div class="line"></div>

        <strong>

            Authorized Signature

        </strong>

        <br>

        <small class="text-muted">

            InventoryPro

        </small>

    </div>

</div>


<hr class="my-5">


<div class="text-center text-muted">

    <small>

        This is a computer generated invoice and does not require a physical signature.

    </small>

</div>

</div>

</div>


<!-- =========================
PRINT BUTTON
========================= -->

<button
class="print-btn"
onclick="window.print()">

<i class="bi bi-printer-fill"></i>

Print Invoice

</button>


<script>

window.onload = function(){

    document.title = "Invoice_<?php echo $data['id']; ?>";

};

</script>


</body>

</html>

<?php

mysqli_close($conn);

?>