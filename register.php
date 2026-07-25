<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>InventoryPro | Register</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/login.css">

</head>


<body>


<div class="container vh-100 d-flex justify-content-center align-items-center">


    <div class="login-card">


        <h2>Create Account</h2>

        <p>Register your InventoryPro account</p>



        <form action="register_process.php" method="POST">


            <!-- Email -->

            <div class="mb-3">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter Email Address"
                    required>

            </div>




            <!-- Username -->

            <div class="mb-3">

                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="Create Username"
                    required>

            </div>





            <!-- Password -->

            <div class="mb-3">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    class="form-control"
                    placeholder="Create Password"
                    required>

            </div>





            <!-- Account Type -->

            <div class="mb-3">

                <label>Account Type</label>

              <input
    type="text"
    class="form-control"
    value="Staff"
    readonly>

                <small class="color:#dbeafe;">
                    New accounts are created as Staff accounts.
                </small>

            </div>





            <button 
                type="submit" 
                class="btn btn-primary w-100">

                Create Account

            </button>



        </form>





        <div class="text-center mt-3">


            Already have an account?

            <a href="login.php">
                Login
            </a>


        </div>



    </div>


</div>



</body>

</html>