<?php
require_once '../../config/db.php';
require_once '../../auth/auth.php';
requirelogin();
requirerole(['Admin']); 

$user_id = $_SESSION['user_id'];
$user_query = "
    SELECT user_id, username, role, profile_picture
    FROM users
    WHERE user_id = ?
";

$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Supplies Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/style.css"/>
    <style>
        .avatar {
    width: 42px;
    height: 42px;
    min-width: 42px;
    min-height: 42px;
    border-radius: 50%;
    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: center;

    font-weight: 600;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
    </style>
</head>
<body>

    <?php include("adminsidebar.php");?>

    <!-- Main Section App Window -->
    <main>
        <header>
            <h2>User Management</h2>
        </header>

        <div class="content">
            <div class="content">
            <?php
            // Get the page from the URL, default to 'dashboard'
            $page = isset($_GET['page']) ? $_GET['page'] : 'users';

            // Create the path to the module file
            $module_path = "modules/" . $page . ".php";

            // Security check: only include if file exists
            if (file_exists($module_path)) {
                include($module_path);
            } else {
                echo "";
            }
            ?>


    
            <script src="../../js/getSupplier.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
            <!-- monitor deliveries-->

            
