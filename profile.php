<?php
<<<<<<< HEAD
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: authentication/login.php"); exit(); }
require_once("config/db.php");
$user_id = $_SESSION['user_id'];
$msg = ""; $error = "";

// Fetch current user first so we know the existing photo (needed to clean it up on replace)
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// UPDATE PROFILE
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_profile'])) {
    $name     = trim($_POST['full_name']);
    $phone    = trim($_POST['phone']);
    $currency = $_POST['currency'];

    $imagePath = null;

    // Only touch the upload logic if a file was actually selected
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            // Common causes: file too big (UPLOAD_ERR_INI_SIZE / FORM_SIZE), partial upload, no tmp dir
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => "The image is larger than this server's upload limit.",
                UPLOAD_ERR_FORM_SIZE  => "The image is larger than the form's upload limit.",
                UPLOAD_ERR_PARTIAL    => "The image was only partially uploaded. Try again.",
                UPLOAD_ERR_NO_TMP_DIR => "Server is missing a temporary upload folder.",
                UPLOAD_ERR_CANT_WRITE => "Server failed to write the file to disk.",
                UPLOAD_ERR_EXTENSION  => "A server extension blocked the upload.",
            ];
            $error = $uploadErrors[$file['error']] ?? "Upload failed (error code {$file['error']}).";
        } else {
            $allowedExt  = ['jpg','jpeg','png','gif','webp'];
            $allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            // Verify the real MIME type (don't trust the file extension alone)
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);

            if (!in_array($ext, $allowedExt) || !in_array($mime, $allowedMime)) {
                $error = "Invalid image format. Please use JPG, PNG, GIF or WEBP.";
            } elseif ($file['size'] > 5 * 1024 * 1024) {
                $error = "Image is too large. Please keep it under 5MB.";
            } elseif (!getimagesize($file['tmp_name'])) {
                $error = "That file doesn't look like a valid image.";
            } else {
                $uploadDir = __DIR__ . "/uploads/";

                // Auto-create the uploads folder if it's missing
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                if (!is_writable($uploadDir)) {
                    $error = "Server error: the 'uploads' folder isn't writable. Check its permissions (see steps below).";
                } else {
                    $filename    = "profile_" . $user_id . "_" . time() . "." . $ext;
                    $destination = $uploadDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $imagePath = "uploads/" . $filename;

                        // Clean up the old custom photo so uploads/ doesn't fill up
                        if (!empty($user['profile_image']) && $user['profile_image'] !== 'default.png') {
                            $oldFile = __DIR__ . "/" . $user['profile_image'];
                            if (is_file($oldFile)) { @unlink($oldFile); }
                        }
                    } else {
                        $error = "Failed to save the uploaded image. Check the 'uploads' folder permissions.";
                    }
                }
            }
        }
    }

    if (!$error) {
        if ($imagePath) {
            $u = $conn->prepare("UPDATE users SET full_name=?, phone=?, currency=?, profile_image=? WHERE id=?");
            $u->bind_param("ssssi", $name, $phone, $currency, $imagePath, $user_id);
        } else {
            $u = $conn->prepare("UPDATE users SET full_name=?, phone=?, currency=? WHERE id=?");
            $u->bind_param("sssi", $name, $phone, $currency, $user_id);
        }
        $u->execute();
        $_SESSION['full_name'] = $name;
        $msg = "Profile updated successfully!";

        // Re-fetch so the page reflects the new data/photo immediately
        $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profile | NeoFinance</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..900&family=Manrope:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<?php include("includes/sidebar.php"); ?>
<?php include("includes/topbar.php"); ?>

<div class="dashboard-content">
    <div class="page-header">
        <div class="page-eyebrow">Ledger · Identity</div>
        <h2 class="page-title"><span class="icon-badge"><i class="fa-solid fa-user"></i></span> Profile</h2>
    </div>

    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="dashboard-card text-center">
                <?php $hasCustomPhoto = !empty($user['profile_image']) && $user['profile_image'] !== 'default.png'; ?>
                <?php if ($hasCustomPhoto): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_image']) . '?v=' . time(); ?>"
                         onerror="this.style.display='none'; document.getElementById('avatarFallback').style.display='flex';"
                         class="rounded-circle mb-3" width="140" height="140" style="object-fit:cover;">
                    <div id="avatarFallback" class="rounded-circle mb-3 mx-auto" style="display:none; width:140px; height:140px; align-items:center; justify-content:center; background:#C6A15B; color:#0E2A1C; font-size:48px; font-weight:700;">
                        <?php echo htmlspecialchars(strtoupper(substr($user['full_name'], 0, 1) ?: 'N')); ?>
                    </div>
                <?php else: ?>
                    <!-- No custom photo yet: local initials avatar instead of an external service, so it never shows a broken image icon -->
                    <div class="rounded-circle mb-3 mx-auto" style="width:140px; height:140px; display:flex; align-items:center; justify-content:center; background:#C6A15B; color:#0E2A1C; font-size:48px; font-weight:700;">
                        <?php echo htmlspecialchars(strtoupper(substr($user['full_name'], 0, 1) ?: 'N')); ?>
                    </div>
                <?php endif; ?>
                <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="text-muted small">Member since <?php echo date("d M Y", strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="form-card" style="max-width:100%;">
                <h4 class="mb-3">Edit Details</h4>
                <form method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Email (read-only)</label>
                            <input type="text" name="email_display" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled readonly autocomplete="off" data-lpignore="true" data-1p-ignore data-bwignore>
                        </div>
                        <div class="col-md-6">
                            <label>Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Preferred Currency</label>
                            <select name="currency" class="form-select">
                                <?php foreach (['INR','USD','EUR','GBP'] as $c): ?>
                                    <option <?php echo $user['currency']===$c?'selected':''; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label>Profile Photo</label>
                            <input type="file" name="profile_image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">
                            <small class="text-muted">JPG, PNG, GIF or WEBP — max 5MB.</small>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-info text-white fw-bold mt-4">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
=======

session_start();
include("database/connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student details
$sql = "SELECT * FROM students WHERE user_id='$user_id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result)==0){
    die("Student profile not found.");
}

$row = mysqli_fetch_assoc($result);


// ================= UPDATE PROFILE =================

if(isset($_POST['update']))
{

    $full_name = mysqli_real_escape_string($conn,$_POST['full_name']);
    $email     = mysqli_real_escape_string($conn,$_POST['email']);
    $mobile    = mysqli_real_escape_string($conn,$_POST['mobile']);
    $gender    = mysqli_real_escape_string($conn,$_POST['gender']);
    $dob       = $_POST['dob'];
    $address   = mysqli_real_escape_string($conn,$_POST['address']);
    $city      = mysqli_real_escape_string($conn,$_POST['city']);
    $state     = mysqli_real_escape_string($conn,$_POST['state']);
    $pincode   = mysqli_real_escape_string($conn,$_POST['pincode']);

    $update="UPDATE students SET

    full_name='$full_name',
    email='$email',
    mobile='$mobile',
    gender='$gender',
    dob='$dob',
    address='$address',
    city='$city',
    state='$state',
    pincode='$pincode'

    WHERE user_id='$user_id'";

    if(mysqli_query($conn,$update))
    {

        echo "<script>alert('Profile Updated Successfully');</script>";
        echo "<script>window.location='profile.php';</script>";
        exit();

    }
    else
    {

        echo "<script>alert('Update Failed');</script>";

    }

}


// ================= DELETE PROFILE =================

if(isset($_POST['delete']))
{

    $delete="DELETE FROM students WHERE user_id='$user_id'";

    if(mysqli_query($conn,$delete))
    {

        session_destroy();

        echo "<script>alert('Profile Deleted Successfully');</script>";
        echo "<script>window.location='login.php';</script>";
        exit();

    }

    else
    {

        echo "<script>alert('Delete Failed');</script>";

    }

}

?>
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Student Profile</title>

<meta name="viewport" content="width=device-width,initial-scale=1">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins,sans-serif;
}

body{

background:#eef2f7;

display:flex;

}

/* ================= SIDEBAR ================= */

.sidebar{

width:240px;

height:100vh;

background:#2563eb;

position:fixed;

left:0;

top:0;

padding:25px;

}

.sidebar h2{

color:#fff;

margin-bottom:40px;

}

.sidebar a{

display:block;

padding:14px;

margin-bottom:10px;

text-decoration:none;

color:#fff;

border-radius:8px;

transition:.3s;

}

.sidebar a:hover{

background:rgba(255,255,255,.15);

}

/* ================= MAIN ================= */

.main{

margin-left:240px;

width:calc(100% - 240px);

padding:35px;

}

.header{

background:white;

padding:25px;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

margin-bottom:30px;

}

.header h1{

color:#2563eb;

}

.card{

background:white;

padding:30px;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

}
.main h2{
color:#2563eb;
margin-bottom:25px;
}

.profile-photo{

text-align:center;
margin-bottom:30px;

}

.profile-photo img{

width:130px;
height:130px;
border-radius:50%;
border:4px solid #2563eb;
object-fit:cover;

}

table{

width:100%;

}

table td{

padding:12px;

vertical-align:top;

}

table td:first-child{

font-weight:600;
width:180px;

}

input,
textarea,
select{

width:100%;
padding:11px;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;

}

textarea{

height:90px;
resize:none;

}

.btn{

padding:12px 25px;
border:none;
border-radius:8px;
cursor:pointer;
font-size:15px;
margin:10px;

}

.update{

background:#2563eb;
color:white;

}

.delete{

background:#dc3545;
color:white;

}

.back{

background:#6c757d;
color:white;
text-decoration:none;
display:inline-block;

}

.btn:hover{

opacity:.9;

}

@media(max-width:768px){

.sidebar{

width:100%;
height:auto;
position:relative;

}

.main{

margin-left:0;
width:100%;

}

body{

display:block;

}

table,
tr,
td{

display:block;
width:100%;

}

}
</style>

</head>

<body>

<div class="sidebar">

<h2>Student Portal</h2>

<a href="dashboard.php">
<i class="fa-solid fa-house"></i>
 Dashboard
</a>

<a href="profile.php">
<i class="fa-solid fa-user"></i>
 Student Profile
</a>

<a href="project.php">
<i class="fa-solid fa-folder"></i>
 My Project
</a>

<a href="task.php">
<i class="fa-solid fa-list-check"></i>
 Task List
</a>

<a href="team.php">
<i class="fa-solid fa-users"></i>
 Team Members
</a>

<a href="submit.php">
<i class="fa-solid fa-upload"></i>
 Submit Work
</a>

<a href="logout.php">
<i class="fa-solid fa-right-from-bracket"></i>
 Logout
</a>

</div>

<div class="main">

<div class="header">

<h1>

Welcome,

<?php echo $row['full_name']; ?>

👋

</h1>

<p>

Student Profile Management

</p>

</div>

<div class="card">

<h2>

Student Profile

</h2>

<form method="post">

<div class="profile-photo">

<img src="uploads/<?php echo $row['profile_photo']; ?>">

</div>

<table>

<tr>

<td>Full Name</td>

<td>

<input
type="text"
name="full_name"
value="<?php echo $row['full_name']; ?>">

</td>

</tr>

<tr>

<td>Roll Number</td>

<td>

<input
type="text"
value="<?php echo $row['roll_no']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Email</td>

<td>

<input
type="email"
name="email"
value="<?php echo $row['email']; ?>">

</td>

</tr>

<tr>

<td>Mobile</td>

<td>

<input
type="text"
name="mobile"
value="<?php echo $row['mobile']; ?>">

</td>

</tr>

<tr>

<td>Gender</td>

<td>

<select name="gender">

<option value="Male" <?php if($row['gender']=="Male") echo "selected"; ?>>Male</option>

<option value="Female" <?php if($row['gender']=="Female") echo "selected"; ?>>Female</option>

<option value="Other" <?php if($row['gender']=="Other") echo "selected"; ?>>Other</option>

</select>

</td>

</tr>

<tr>

<td>Date of Birth</td>

<td>

<input
type="date"
name="dob"
value="<?php echo $row['dob']; ?>">

</td>

</tr>

<tr>

<td>Department</td>

<td>

<input
type="text"
value="<?php echo $row['department']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Year</td>

<td>

<input
type="text"
value="<?php echo $row['year']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Division</td>

<td>

<input
type="text"
value="<?php echo $row['division']; ?>"
readonly>

</td>

</tr>

<tr>

<td>Address</td>

<td>

<textarea
name="address"><?php echo $row['address']; ?></textarea>

</td>

</tr>

<tr>

<td>City</td>

<td>

<input
type="text"
name="city"
value="<?php echo $row['city']; ?>">

</td>

</tr>

<tr>

<td>State</td>

<td>

<input
type="text"
name="state"
value="<?php echo $row['state']; ?>">

</td>

</tr>

<tr>

<td>Pincode</td>

<td>

<input
type="text"
name="pincode"
value="<?php echo $row['pincode']; ?>">

</td>

</tr>
<tr>

<td colspan="2" align="center">

<button
type="submit"
name="update"
class="btn update">

<i class="fa-solid fa-floppy-disk"></i>

 Update Profile

</button>

<button
type="submit"
name="delete"
class="btn delete"
onclick="return confirm('Are you sure you want to delete your profile?');">

<i class="fa-solid fa-trash"></i>

 Delete Profile

</button>

<a href="dashboard.php" class="btn back">

<i class="fa-solid fa-arrow-left"></i>

 Dashboard

</a>

</td>

</tr>

</table>

</form>

</div>

</div>

</body>

</html>
>>>>>>> 3b6323a39d716a2b44dd6448aa482f5c3c4bbcca
