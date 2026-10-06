<?php
require_once '../../config/db.php';
require '../../auth/auth.php';

// Check if user is logged in
requireLogin();
requireRole(['Custodian']); // Only allow users with the 'custodian' role

?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Dashboard - School Supplies Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/style.css"/>
</head>
<body>

    <?php include("custodiansidebar.php");?>

    <!-- Main Section App Window -->
    <main>
        

        <div class="content">
            <?php
            // 1. Define allowed modules explicitly to prevent path traversal / LFI
            $allowed_pages = [
                'dashboard',
                'deliveries',
                'stock-count',
                'labels'
            ];

            // 2. Fetch parameter and fallback safely if it's empty or invalid
            $page = isset($_GET['page']) ? $_GET['page'] : 'users';

            if (in_array($page, $allowed_pages)) {
                $module_path = "modules/" . $page . ".php";
                
                if (file_exists($module_path)) {
                    include($module_path);
                } else {
                    echo "<div class='alert alert-danger'>Module file missing.</div>";
                }
            } else {
                // If an attacker tries '?page=../../etc/passwd', they get caught here
                echo "<div class='alert alert-warning'>Access Denied: Invalid module target.</div>";
            }
            ?>
        </div>

    
            <script src="../../js/getSupplier.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
            <!-- monitor deliveries-->

            
