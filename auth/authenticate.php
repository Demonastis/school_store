<?php
session_start();
// Adjusted path to look one folder up for config.php since authenticate is inside the auth/ folder
require_once '../config/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        header("Location: ../auth/login.php?error=Username and password are required.");
        exit();
    }

    $query = "SELECT user_id, first_name, last_name, email, password_hash, role FROM Users WHERE username = ?";
    
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $result = $stmt->get_result(); 
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['username']   = $username;
                $_SESSION['role']       = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name']  = $user['last_name'];
                
                $stmt->close(); 
                
                switch ($user['role']) {
                    case 'Admin':
                        header("Location: ../dashboards/admin/admin_dashboard.php");
                        break;
                    case 'Owner':
                        header("Location: ../dashboards/owner/owner_dashboard.php");
                        break;
                    case 'Manager':
                        header("Location: ../dashboards/manager/manager_dashboard.php");
                        break;
                    case 'Custodian':
                        header("Location: ../dashboards/custodian/custodian_dashboard.php");
                        break;
                    case 'Cashier':
                        header("Location: ../dashboards/cashier/cashier_dashboard.php");
                        break;
                    case 'Customer':
                        header("Location: ../dashboards/customer/customer_dashboard.php");
                        break;
                    default:
                        header("Location: ../auth/login.php?error=Role assignment mismatch.");
                        exit();
                }
                exit();
            }
        }
        $stmt->close();
    }
    
    header("Location: ../auth/login.php?error=Invalid username or password.");
    exit();
} else {
    header("Location: ../auth/login.php");
    exit();
}
?>
