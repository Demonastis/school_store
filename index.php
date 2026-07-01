<?php
session_start();

if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'Admin':
            header("Location: dashboards/admin_dashboard.php");
            break;
        case 'Owner':
            header("Location: dashboards/owner_dashboard.php");
            break;
        case 'Manager':
            header("Location: dashboards/manager_dashboard.php");
            break;
        case 'Custodian':
            header("Location: dashboards/custodian_dashboard.php");
            break;
        case 'Cashier':
            header("Location: dashboards/cashier_dashboard.php");
            break;
        case 'Customer':
            header("Location: dashboards/customer_dashboard.php");
            break;
        default:
            header("Location: auth/login.php");
    }
    exit();
} else {
    header("Location: auth/login.php");
    exit();
}
?>
