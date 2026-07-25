<?php

if(session_status() == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$isAdmin = ($_SESSION['role'] == "Admin");

?>


<div id="sidebar">


    <!-- BRAND -->

    <a href="<?= $isAdmin ? 'dashboard.php' : 'user_dashboard.php'; ?>" class="brand">

        <i class="bi bi-box-seam-fill text-info"></i>

        <span>InventoryPro</span>

    </a>



    <!-- DARK MODE BUTTON -->

 




    <ul>


        <!-- Dashboard -->

        <li>

            <a href="<?= $isAdmin ? 'dashboard.php' : 'user_dashboard.php'; ?>"
            
            class="<?= ($current_page=='dashboard.php' || $current_page=='user_dashboard.php') ? 'active' : '' ?>">


                <i class="bi bi-speedometer2"></i>

                <span>Dashboard</span>


            </a>

        </li>





        <?php if($isAdmin){ ?>


        <!-- Categories -->

        <li>

            <a href="categories.php"
            
            class="<?= $current_page=='categories.php'?'active':'' ?>">


                <i class="bi bi-grid"></i>

                <span>Categories</span>


            </a>

        </li>





        <!-- Suppliers -->

        <li>

            <a href="suppliers.php"
            
            class="<?= $current_page=='suppliers.php'?'active':'' ?>">


                <i class="bi bi-truck"></i>

                <span>Suppliers</span>


            </a>

        </li>






        <!-- Purchases -->

        <li>

            <a href="purchases.php"
            
            class="<?= $current_page=='purchases.php'?'active':'' ?>">


                <i class="bi bi-cart-plus"></i>

                <span>Purchases</span>


            </a>

        </li>



        <?php } ?>






        <!-- Products -->

        <li>

            <a href="products.php"
            
            class="<?= $current_page=='products.php'?'active':'' ?>">


                <i class="bi bi-box"></i>

                <span>Products</span>


            </a>

        </li>





        <!-- Customers -->

        <li>

            <a href="customers.php"
            
            class="<?= $current_page=='customers.php'?'active':'' ?>">


                <i class="bi bi-people"></i>

                <span>Customers</span>


            </a>

        </li>






        <!-- Sales -->

        <li>

            <a href="sales.php"
            
            class="<?= $current_page=='sales.php'?'active':'' ?>">


                <i class="bi bi-cart-check"></i>

                <span>Sales</span>


            </a>

        </li>






        <!-- Reports -->
<?php if($_SESSION['role'] == "Admin"){ ?>
        <li>

            <a href="reports.php"
            
            class="<?= $current_page=='reports.php'?'active':'' ?>">


                <i class="bi bi-bar-chart"></i>

                <span>Reports</span>


            </a>

        </li>
<?php } ?>





        <!-- Profile -->

<li>
    <a href="settings.php"
       class="<?= in_array($current_page, [
           'settings.php',
           'profile.php',
           'change_password.php'
       ]) ? 'active' : '' ?>">

        <i class="bi bi-gear-fill"></i>
        <span>Settings</span>

    </a>
</li>






        <!-- Logout -->

        <li class="mt-4">


            <a href="logout.php">


                <i class="bi bi-box-arrow-right"></i>


                <span>Logout</span>


            </a>


        </li>



    </ul>


</div>