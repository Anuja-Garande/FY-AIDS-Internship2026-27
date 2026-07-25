<?php


$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "inventory_management"
);



if(!$conn)
{
    die("Database Connection Failed");
}




if($_SERVER["REQUEST_METHOD"] != "POST")
{
    header("Location: register.php");
    exit();
}




$email = trim($_POST['email']);

$username = trim($_POST['username']);

$password = password_hash(
    $_POST['password'],
    PASSWORD_DEFAULT
);



// IMPORTANT
// Nobody can register as Admin

$role = "Staff";





// Check duplicate username/email

$check = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE username=? OR email=?"
);


mysqli_stmt_bind_param(
    $check,
    "ss",
    $username,
    $email
);


mysqli_stmt_execute($check);


$result = mysqli_stmt_get_result($check);



if(mysqli_num_rows($result) > 0)
{

    header("Location: register.php?error=exists");
    exit();

}





// Insert user


$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users 
    (username,email,password,role)
    VALUES (?,?,?,?)"
);



mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $username,
    $email,
    $password,
    $role
);




if(mysqli_stmt_execute($stmt))
{

    header("Location: login.php?registered=1");
    exit();

}

else
{

    echo "Registration Failed: " . mysqli_error($conn);

}




mysqli_close($conn);


?>