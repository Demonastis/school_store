<?php
session_start();
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        header("Location: ../auth/login.php?error=Username and password are required.");
        exit();
    }

    /*
     * Get the user first.
     *
     * We intentionally do NOT filter out archived/pending users
     * in the SQL query so we can give the appropriate response.
     */
    $query = "
        SELECT
            user_id,
            username,
            password_hash,
            role,
            first_name,
            last_name,
            is_archived,
            approval_status
        FROM users
        WHERE username = ?
        LIMIT 1
    ";

    if ($stmt = $conn->prepare($query)) {

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {

            $user = $result->fetch_assoc();

            /*
             * Check password first.
             */
            if (password_verify($password, $user['password_hash'])) {

                /*
                 * -----------------------------------------
                 * CHECK IF ACCOUNT IS ARCHIVED
                 * -----------------------------------------
                 */
                if ((int)$user['is_archived'] === 1) {
                    $stmt->close();

                    header("Location: ../auth/login.php?error=Your account has been archived.");
                    exit();
                }

                /*
                 * -----------------------------------------
                 * CHECK IF ACCOUNT IS APPROVED
                 * -----------------------------------------
                 */
                if ($user['approval_status'] !== 'approved') {

                    $stmt->close();

                    if ($user['approval_status'] === 'pending') {
                        header("Location: ../auth/login.php?error=Your account is awaiting approval.");
                    } else {
                        header("Location: ../auth/login.php?error=Your account has not been approved.");
                    }

                    exit();
                }

                /*
                 * -----------------------------------------
                 * LOGIN SUCCESSFUL
                 * -----------------------------------------
                 */

                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']         = $user['user_id'];
                $_SESSION['username']        = $user['username'];
                $_SESSION['role']            = $user['role'];
                $_SESSION['first_name']      = $user['first_name'];
                $_SESSION['last_name']       = $user['last_name'];
                $_SESSION['is_archived']     = $user['is_archived'];
                $_SESSION['approval_status'] = $user['approval_status'];

                $stmt->close();

                /*
                 * -----------------------------------------
                 * REDIRECT BASED ON ROLE
                 * -----------------------------------------
                 */

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
                        session_unset();
                        session_destroy();

                        header("Location: ../auth/login.php?error=Role assignment mismatch.");
                        exit();
                }

                exit();
            }
        }

        $stmt->close();
    }

    /*
     * Generic login error.
     */
    header("Location: ../auth/login.php?error=Invalid username or password.");
    exit();

} else {

    header("Location: ../auth/login.php");
    exit();
}