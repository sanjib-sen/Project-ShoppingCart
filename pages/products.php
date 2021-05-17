<?php

$host = 'localhost';
$dbUsrname = 'root';
$dbPassword = '';
$dbname = 'project';
$conn = new mysqli($host, $dbUsrname, $dbPassword, $dbname);

$label = '';

$action = "";

session_start();

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
}

if (isset($_POST['add']) && $_SESSION['role'] == 'customer') {
    $action = "add";
    $sql_cart_count = "SELECT * FROM cart";
    $run_cart_count = mysqli_query($conn, $sql_cart_count);
    $cart_count = mysqli_num_rows($run_cart_count);

    if ($cart_count != 0) {
        $sql_2 = "SELECT * FROM cart ORDER BY cart_id DESC LIMIT 1";
        $run_2 = mysqli_query($conn, $sql_2);
        $fetch = $run_2->fetch_assoc();
        $cart_count = $fetch['cart_id'];
    }
    if (!isset($_SESSION['cart_count'])) {
        $_SESSION['cart_count'] = $cart_count + 1;
        $cart_id = $cart_count + 1;
    } else {
        $cart_id = $_SESSION['cart_count'];
    }


    if ($cart_id > $cart_count) {
        $sql_create = "INSERT INTO cart (cart_id) VALUES ('$cart_id')";
        $run_query_create = mysqli_query($conn, $sql_create);
    }
    $sql_product_count = "SELECT * FROM added_to WHERE (cart_id = $cart_id AND product_id= $product_id)";
    $run_product_count = mysqli_query($conn, $sql_product_count);
    $product_count = mysqli_num_rows($run_product_count);

    if ($product_count > 0) {
        $data = $run_product_count->fetch_assoc();
        $quantity_more = $data['quantity'] + 1;
        $sql_update = "UPDATE added_to SET quantity ='$quantity_more' WHERE (cart_id = $cart_id AND product_id= $product_id)";
        $run_update = mysqli_query($conn, $sql_update);
        $label = "Increased Quantity +1 = $quantity_more";
    } else {
        $sql_add = "INSERT INTO added_to (product_id, cart_id, quantity) VALUES ('$product_id','$cart_id', 1)";
        $run_add = mysqli_query($conn, $sql_add);
        $label = "Added to Cart";
    }
}


if (isset($_POST['update'])) {
    $action = "update";
    $update_sql = "SELECT * FROM products where product_id=$product_id";
    $run_query_update = mysqli_query($conn, $update_sql);
    $product = $run_query_update->fetch_assoc();

}

if (isset($_POST['create'])) {
    $action = "create";

}


if (isset($_POST['delete'])) {
    $action = "delete";
    $pro_sql = "SELECT * FROM products where product_id=$product_id";
    $run_query_pro = mysqli_query($conn, $pro_sql);
    $fetchpro = $run_query_pro->fetch_assoc();
    $pic = $fetchpro['image'];
    $file = "../uploads/$pic";
    if (file_exists($file)) {
        unlink($file);
    }
    $del_sql = "DELETE FROM products where product_id='$product_id'";
    $run_del_query = mysqli_query($conn, $del_sql);
    $label = "The product has been deleted.";
}


if (isset($_POST['product'])) {
    $action = "product";
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $stock = mysqli_real_escape_string($conn, $_POST['stock']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);


    if (!$_FILES['image']['name'] == "") {
        $filename = (uniqid($_FILES['image']['name'], true));
        move_uploaded_file($_FILES["image"]["tmp_name"], "../uploads/" . $filename);
    }

    if ($_POST['product'] == 'registered') {
        $sql_create = "INSERT INTO products (name,description, price, image, stock, category) VALUES ('$title','$description','$price','$filename', '$stock', '$category')";
        $run_query_create = mysqli_query($conn, $sql_create);

        $sql_2 = "SELECT * FROM products ORDER BY product_id DESC LIMIT 1";
        $run_2 = mysqli_query($conn, $sql_2);
        $fetch = $run_2->fetch_assoc();
        $product_id = $fetch['product_id'];
        $admin = $_SESSION['admin-id'];
        $sql3 = "INSERT INTO manages (product_id,user_id) VALUES ('$product_id','$admin')";
        $run_query3 = mysqli_query($conn, $sql3);
        $label = 'Product Registered.';
    }
    if ($_POST['product'] == 'updated') {

        $product_id = $_POST['product_id'];
        $sql_update = "UPDATE products SET name='$title',description='$description',price='$price',stock='$stock',category='$category' WHERE product_id = '$product_id'";
        $run_query_update = mysqli_query($conn, $sql_update);
        if (isset($filename) && !$filename == "") {
            $pro_sql = "SELECT * FROM products where product_id=$product_id";
            $run_query_pro = mysqli_query($conn, $pro_sql);
            $fetchpro = $run_query_pro->fetch_assoc();
            $pic = $fetchpro['image'];
            $file = "../uploads/$pic";
            if (file_exists($file)) {
                unlink($file);
            }
            $sql_image_update = "UPDATE products SET image='$filename' WHERE product_id = '$product_id'";
            $run_query_update_image = mysqli_query($conn, $sql_image_update);
        }
        $label = 'Product Updated';
    }
}

if (isset($_POST['fromlogin'])) {
    $label = $_POST['fromlogin'];
    $action = 'login';
}

$sql = "SELECT * FROM products";
$run_query = mysqli_query($conn, $sql);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <meta http-equiv="x-ua-compatible" content="ie=edge"/>
    <title>Products</title>
    <!-- MDB icon -->
    <link rel="icon" href="elements/img/bracu.ico" type="image/x-icon"/>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.2/css/all.css"/>
    <!-- Google Fonts Roboto -->
    <link
            rel="stylesheet"
            href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
    />
    <!-- MDB -->
    <link rel="stylesheet" href="elements/css/mdb.min.css"/>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <!-- Container wrapper -->
    <div class="container-fluid">
        <!-- Toggle button -->
        <button
                class="navbar-toggler"
                type="button"
                data-mdb-toggle="collapse"
                data-mdb-target="#navbarRightAlignExample"
                aria-controls="navbarRightAlignExample"
                aria-expanded="false"
                aria-label="Toggle navigation"
        >
            <i class="fas fa-bars"></i>
        </button>

        <!-- Collapsible wrapper -->
        <div class="collapse navbar-collapse " id="navbarRightAlignExample">
            <!-- Left links -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if (isset($_SESSION['cart_count'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="cart.php">Go to Cart</a>
                    </li>
                <?php } ?>
                <?php if (isset($_POST['create']) || isset($_POST['update'])) { ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Back to Products</a>
                    </li>
                <?php } ?>
                <?php if ($_SESSION['role'] == 'admin') { ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="login-register.php">Customer
                            Login</a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="customer-info.php">Customer Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout-customer.php">Logout-Customer</a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" href="logout-admin.php">Logout-Admin</a>
                </li>
            </ul>
            <!-- Left links -->
        </div>
        <!-- Collapsible wrapper -->
    </div>
    <!-- Container wrapper -->
</nav>
<!-- Navbar -->


<div class="border border-0 p-5">
    <div class="d-flex align-items-center h-100">
        <div class="container">
            <div class="row justify-content-center">


                <!-- Start your project here-->
                <?php if (!($action == 'update' || $action == 'create')) { ?>
                    <div class="col-xl-10">
                        <h1>Products</h1>
                        <?php if ($_SESSION['role'] != 'customer') { ?>
                            <p>

                            <form action="products.php" method="post">
                                <input type="hidden" name="create" value="update">
                                <input type="submit" class="btn btn-sm btn-success" value="Add Product">
                            </form>
                            <!--                        <a href="products.php" type="button" class="btn btn-sm btn-success">Add Product</a>-->

                            </p>

                        <?php } ?>


                        <?php if (!$action == '') { ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $label ?>
                            </div>
                        <?php } ?>


                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th scope="col">Image</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Stock</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($product = $run_query->fetch_assoc()) { ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image']): ?>
                                                <img src="../uploads/<?php echo $product['image'] ?>"
                                                     alt="<?php echo $product['name'] ?>" class="product-img"
                                                     height="20"
                                                     width="30">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $product['name'] ?></td>
                                        <td><?php echo $product['price'] ?></td>
                                        <td><?php echo $product['stock'] ?></td>
                                        <td><?php echo $product['category'] ?></td>
                                        <td>
                                            <form action="products.php" method="post">
                                                <input type="hidden" name="product_id"
                                                       value="<?php echo $product['product_id'] ?>">
                                                <?php if ($_SESSION['role'] == 'customer') { ?>
                                                    <input type="submit" class="btn btn-primary" value="add" name="add"
                                                           required/> <?php } ?>
                                                <?php if ($_SESSION['role'] != 'customer') { ?>
                                                    <input type="submit" class="btn btn-secondary" value="Edit"
                                                           name="update" required/>
                                                    <input type="submit" class="btn btn-danger" value="Delete"
                                                           name="delete" required/>
                                                <?php } ?>
                                            </form>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>


                        <?php if (isset($_SESSION['cart_count'])) { ?>
                            <br>
                            <br>
                            <div class="col text-center">
                                <a class="btn btn-primary" href="cart.php" role="button">Go to Cart</a>
                            </div>
                        <?php } ?>

                    </div>
                <?php } ?>
                <?php if ($action == 'update' || $action == 'create') { ?>
                    <div class="col-xl-4">

                        <form action="products.php" method="POST" enctype="multipart/form-data">

                            <?php if ($action == 'update') { ?>
                                <p class="text-center fw-bold ">Update Product</p>
                            <?php } else { ?>
                                <p class="text-center fw-bold ">Register Product</p>
                            <?php } ?>

                            <!--          Image Input-->
                            <?php if ($product['image'] ?? ""): ?>
                                <img src="../uploads/<?php echo $product['image'] ?? "" ?>"
                                     alt="<?php echo $product['name'] ?? "" ?>"
                                     class="product-img" height="40" width="60">
                            <?php endif; ?>
                            <div class="input-group mb-4">
                                <label class="input-group-text" for="image">Image</label>
                                <input type="file" id="image" class="form-control" name="image"
                                       aria-describedby="inputGroupFileAddon03"
                                       aria-label="Upload" value="<?php echo $product['image'] ?? "" ?>"/>
                            </div>

                            <!-- Name input -->
                            <div class="form-outline mb-4">
                                <input type="text" name="title" class="form-control"
                                       value="<?php echo $product['name'] ?? "" ?>"
                                       required/>
                                <label class="form-label" for="title">Name</label>
                            </div>

                            <!-- Phone input -->
                            <div class="form-outline mb-4">
                                <input type="text" name="description"
                                       value="<?php echo $product['description'] ?? "" ?>"
                                       class="form-control" required height=""/>
                                <label class="form-label" for="description">Description</label>
                            </div>

                            <!-- Password input -->
                            <div class="form-outline mb-4">
                                <input type="number" name="price" value="<?php echo $product['price'] ?? "" ?>"
                                       class="form-control" required/>
                                <label class="form-label" for="price">Price</label>
                            </div>

                            <div class="form-outline mb-4">
                                <input type="text" name="category" value="<?php echo $product['category'] ?? "" ?>"
                                       class="form-control" required height=""/>
                                <label class="form-label" for="category">Category</label>
                            </div>

                            <div class="form-outline mb-4">
                                <input type="number" name="stock" value="<?php echo $product['stock'] ?? "" ?>"
                                       class="form-control" required/>
                                <label class="form-label" for="price">Stock</label>
                            </div>

                            <!-- Submit button -->
                            <input type="hidden" name="product"
                                   value="<?php if ($action == 'update') echo "updated"; else echo "registered"; ?>">
                            <?php if ($action == 'update'): ?>
                                <input type="hidden" name="product_id"
                                       value="<?php echo $product['product_id'] ?? "" ?>">
                            <?php endif; ?>
                            <input type="submit" class="btn btn-primary btn-block mb-4"/>
                        </form>
                        <br>

                        <div class="col text-center">
                            <a class="btn btn-primary" href="products.php" role="button">Back to Products</a>
                        </div>
                    </div>

                <?php } ?>

            </div>
        </div>
    </div>
</div>


<!-- MDB -->
<script type="text/javascript" src="elements/js/mdb.min.js"></script>
<!-- Custom scripts -->
<script type="text/javascript"></script>
</body>
</html>