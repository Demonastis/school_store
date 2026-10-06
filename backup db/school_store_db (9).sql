-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 05:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_store_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `module`, `description`, `created_at`) VALUES
(1, 1, 'User Login', 'Login Module', 'Admin successfully logged in from IP ::1', '2026-06-21 08:55:50'),
(2, 3, 'Update Stock', 'Inventory Module', 'Manager updated stock level for Product ID 3 to 12 units', '2026-06-21 08:55:50'),
(3, 5, 'Process Payment', 'Cashier Module', 'Cashier verified payment and recorded Transaction ID 1', '2026-06-21 08:55:50'),
(4, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-4 for 2 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-01 22:09:59'),
(8, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-8 for 1 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-25 21:06:40'),
(9, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-9 for 1 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-25 21:06:43'),
(10, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-10 for 1 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-25 21:06:44'),
(11, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-11 for 1 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-25 21:06:45'),
(12, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-12 for 1 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-25 21:07:04'),
(13, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-13 for 2 Box(s) of \'Black Ballpoint Pen\' at ₱6.00 per unit from vendor ABC School Supplies Inc..', '2026-07-26 19:36:59'),
(14, 4, 'Inventory Reconciliation', 'Warehouse Module', 'Manual inventory reconciliation for item \'Black Ballpoint Pen\'. Count adjusted from 12 to 10 (Variance: -2 units). Reason: Damaged Goods Discard.', '2026-07-26 20:41:38'),
(15, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: Charlie Cashier (Username: \'cashier_user\').', '2026-07-28 21:21:32'),
(16, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-5 back to active directory status.', '2026-07-28 21:21:39'),
(17, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: Charlie Cashier (Username: \'cashier_user\').', '2026-07-28 21:55:50'),
(18, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: Bob Custodian (Username: \'custodian_user\').', '2026-07-28 21:55:53'),
(19, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: John Doe (Username: \'customer_user\').', '2026-07-28 21:55:54'),
(20, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: Alice Manager (Username: \'manager_user\').', '2026-07-28 21:55:57'),
(21, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: Store Owner (Username: \'owner_user\').', '2026-07-28 21:55:58'),
(22, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-3 back to active directory status.', '2026-07-28 21:57:04'),
(23, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-5 back to active directory status.', '2026-07-28 21:57:48'),
(24, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-4 back to active directory status.', '2026-07-28 21:57:49'),
(25, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-6 back to active directory status.', '2026-07-28 21:57:51'),
(26, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-2 back to active directory status.', '2026-07-28 21:57:52'),
(27, 1, 'Create User', 'Admin Directory Module', 'Admin provisioned a new user profile account for Llenard Kim Sacdalan (Username: \'hanz\', Role: \'cashier\').', '2026-07-28 21:58:28'),
(28, 1, 'Create User', 'Admin Directory Module', 'Admin provisioned a new user profile account for vus cus (Username: \'cus\', Role: \'custodian\').', '2026-07-28 22:04:01'),
(29, 1, 'Archive User', 'Admin Directory Module', 'Admin archived and disabled active user directory access for user: Charlie Cashier (Username: \'cashier_user\').', '2026-08-28 09:24:53'),
(30, 1, 'Restore User', 'Admin Archive Module', 'Admin restored archived user profile ID #USER-5 back to active directory status.', '2026-08-28 09:25:02'),
(31, 5, 'Create Payment Checkout', 'PayMongo Payment Module', 'Customer Charlie Cashier created PayMongo checkout for order #TXN-15 valued at ₱350.00. Payment is pending.', '2026-09-28 13:45:12'),
(32, 6, 'Create Payment Checkout', 'PayMongo Payment Module', 'Customer John Doe created PayMongo checkout for order #TXN-16 valued at ₱400.00. Payment is pending.', '2026-09-28 14:03:56'),
(33, 6, 'Create Payment Checkout', 'PayMongo Payment Module', 'Customer John Doe created PayMongo checkout for order #TXN-17 valued at ₱725.00. Payment is pending.', '2026-09-28 14:04:28'),
(34, 1, 'Assign Role', 'Admin Directory Module', 'Admin changed role for Charlie Cashier (Username: \'cashier_user\') from \'Cashier\' to \'Manager\'.', '2026-10-05 02:38:50'),
(35, 1, 'Assign Role', 'Admin Directory Module', 'Admin changed role for Charlie Cashier (Username: \'cashier_user\') from \'Manager\' to \'Cashier\'.', '2026-10-05 02:38:52'),
(36, 1, 'Assign Role', 'Admin Directory Module', 'Admin changed role for Charlie Cashier (Username: \'cashier_user\') from \'Cashier\' to \'Admin\'.', '2026-10-05 02:39:15'),
(37, 1, 'Assign Role', 'Admin Directory Module', 'Admin changed role for Charlie Cashier (Username: \'cashier_user\') from \'Admin\' to \'Cashier\'.', '2026-10-05 02:39:18'),
(38, 1, 'Revoke User Approval', 'Admin Directory Module', 'Admin changed approval status for vus cus (Username: \'cus\') to Pending.', '2026-10-05 02:39:27'),
(39, 1, 'Approve User', 'Admin Directory Module', 'Admin changed approval status for vus cus (Username: \'cus\') to Approved.', '2026-10-05 02:39:28'),
(40, 1, 'Revoke User Approval', 'User Approval Module', 'Admin changed approval for System Admin (@admin_user) to Pending.', '2026-10-05 02:56:31'),
(41, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for System Admin (@admin_user) to Approved.', '2026-10-05 02:56:33'),
(42, 1, 'Assign Role', 'Role Assignment Module', 'Admin changed Charlie Cashier (@cashier_user) from Cashier to Custodian.', '2026-10-05 19:49:13'),
(43, 1, 'Assign Role', 'Role Assignment Module', 'Admin changed Store Owner (@owner_user) from Owner to Manager.', '2026-10-05 19:49:31'),
(44, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for System Admin (@admin_user) to Approved.', '2026-10-05 19:49:43'),
(45, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for System Admin (@admin_user) to Approved.', '2026-10-05 19:49:44'),
(46, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for System Admin (@admin_user) to Approved.', '2026-10-05 19:49:45'),
(47, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for System Admin (@admin_user) to Approved.', '2026-10-05 19:49:45'),
(48, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for Charlie Cashier (@cashier_user) to Approved.', '2026-10-05 19:49:50'),
(49, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for vus cus (@cus) to Approved.', '2026-10-05 19:49:52'),
(50, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for Bob Custodian (@custodian_user) to Approved.', '2026-10-05 19:49:56'),
(51, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for Bob Custodian (@custodian_user) to Approved.', '2026-10-05 19:49:59'),
(52, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for Alice Manager (@manager_user) to Approved.', '2026-10-05 19:50:02'),
(53, 1, 'Create User', 'Admin Directory Module', 'Admin provisioned a new user profile account for ss sses (Username: \'sses\', Role: \'Cashier\').', '2026-10-05 20:30:26'),
(54, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for John Doe (@customer_user) to Approved.', '2026-10-05 20:33:44'),
(55, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for John Doe (@customer_user) to Approved.', '2026-10-05 20:39:43'),
(56, 1, 'Approve User', 'User Approval Module', 'Admin changed approval for John Doe (@customer_user) to Approved.', '2026-10-05 20:39:47'),
(57, 1, 'User account Rejected', 'User Management', 'Admin ID 1 changed account registration lifecycle status of User ID 9 to \'rejected\'.', '2026-10-05 21:44:18'),
(58, 1, 'User account Approved', 'User Management', 'Admin ID 1 changed account registration lifecycle status of User ID 9 to \'approved\'.', '2026-10-05 21:44:26'),
(59, 1, 'Assign Role', 'Role Assignment Module', 'Admin changed Charlie Cashier (@cashier_user) from Custodian to Manager.', '2026-10-05 21:45:24'),
(60, 1, 'Bulk User Archival', 'User Management', 'Admin ID 1 batch-archived accounts for User IDs: [9].', '2026-10-05 22:25:26'),
(61, 1, 'Bulk User Archival', 'User Management', 'Admin ID 1 batch-archived accounts for User IDs: [9].', '2026-10-05 22:26:16'),
(62, 1, 'Bulk User Recovery', 'User Manamentge', 'Admin ID 1 executed a [recover] operation for User IDs: [9].', '2026-10-05 22:43:56'),
(63, 1, 'Bulk User Archival', 'User Manamentge', 'Admin ID 1 executed a [archive] operation for User IDs: [9].', '2026-10-05 22:53:45'),
(64, 1, 'Bulk User Archival', 'User Manamentge', 'Admin ID 1 executed a [archive] operation for User IDs: [28].', '2026-10-05 23:01:47'),
(65, 1, 'Bulk User Archival', 'User Manamentge', 'Admin ID 1 executed a [archive] operation for User IDs: [15].', '2026-10-05 23:02:06'),
(66, 1, 'Bulk User Archival', 'User Manamentge', 'Admin ID 1 executed a [archive] operation for User IDs: [12, 20, 17, 11, 27].', '2026-10-05 23:02:43');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `received_date` datetime NOT NULL,
  `received_by` int(11) NOT NULL,
  `status` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `purchase_order_id`, `received_date`, `received_by`, `status`) VALUES
(1, 2, '2026-06-18 10:30:00', 4, 'Completed'),
(2, 1, '2026-06-21 14:15:00', 3, 'Partial'),
(3, 1, '2026-07-26 14:37:33', 1, 'Received'),
(4, 4, '2026-07-26 14:42:29', 1, 'Received'),
(5, 9, '2026-07-26 14:42:59', 1, 'Received'),
(6, 10, '2026-07-26 14:48:09', 1, 'Received'),
(7, 11, '2026-07-26 14:49:02', 1, 'Received'),
(8, 12, '2026-07-28 14:06:07', 1, 'Received'),
(9, 8, '2026-07-28 14:06:22', 1, 'Received');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `minimum_stock` int(11) NOT NULL,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `current_stock`, `minimum_stock`, `last_updated`) VALUES
(1, 1, 156, 20, '2026-07-28 20:06:22'),
(2, 2, 45, 15, '2026-06-21 08:55:49'),
(3, 3, 10, 20, '2026-07-26 20:41:38'),
(4, 4, 0, 10, '2026-06-21 08:55:49');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `transaction_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 5, 25.00, 125.00),
(12, 15, 2, 1, 350.00, 350.00),
(13, 16, 2, 1, 350.00, 350.00),
(14, 16, 1, 2, 25.00, 50.00),
(15, 17, 2, 2, 350.00, 700.00),
(16, 17, 1, 1, 25.00, 25.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `paymongo_checkout_session_id` varchar(100) DEFAULT NULL,
  `paymongo_payment_id` varchar(100) DEFAULT NULL,
  `paymongo_checkout_url` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `transaction_id`, `payment_method`, `amount`, `payment_status`, `paymongo_checkout_session_id`, `paymongo_payment_id`, `paymongo_checkout_url`, `paid_at`) VALUES
(1, 1, 'cash', 125.00, 'Paid', NULL, NULL, NULL, NULL),
(2, 2, 'e-wallet', 750.00, 'Paid', NULL, NULL, NULL, NULL),
(3, 15, 'PayMongo Hosted Checkout', 350.00, 'Pending', 'cs_4ebf8be63548f1045a3fb2de', NULL, 'https://checkout.paymongo.com/4ebf8be63548f1045a3fb2de', NULL),
(4, 16, 'PayMongo Hosted Checkout', 400.00, 'Pending', 'cs_5fcc1c90c9e475ac8e186fe1', NULL, 'https://checkout.paymongo.com/5fcc1c90c9e475ac8e186fe1', NULL),
(5, 17, 'PayMongo Hosted Checkout', 725.00, 'Pending', 'cs_3362999fa4391975ae250df4', NULL, 'https://checkout.paymongo.com/3362999fa4391975ae250df4', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `availability` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `price`, `stock_quantity`, `size`, `description`, `availability`, `created_at`) VALUES
(1, 'Notebook Blue 80 Leaves', 'School Supplies', 25.00, 150, NULL, 'Standard composition notebook.', 'Available', '2026-06-21 08:55:49'),
(2, 'School Uniform Polo', 'Uniform', 350.00, 45, 'Medium', 'White collared short sleeve polo shirt.', 'Available', '2026-06-21 08:55:49'),
(3, 'Black Ballpoint Pen', 'School Supplies', 12.50, 12, NULL, '0.5mm smooth ink pen.', 'Low Stock', '2026-06-21 08:55:49'),
(4, 'School Uniform Slacks', 'Uniform', 400.00, 0, 'Large', 'Black formal uniform trousers.', 'Out of Stock', '2026-06-21 08:55:49');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `purchase_order_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date NOT NULL,
  `status` varchar(30) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `Box_unit` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`purchase_order_id`, `supplier_id`, `product_id`, `order_date`, `expected_delivery_date`, `status`, `total_amount`, `created_by`, `Box_unit`) VALUES
(1, 1, NULL, '2026-06-15', '2026-06-22', 'Completed', 15450.00, 'manager_user', 1),
(2, 2, NULL, '2026-06-10', '2026-06-18', 'Received', 42000.00, 'manager_user', 1),
(3, 3, NULL, '2026-06-20', '2026-06-27', 'Cancelled', 520.50, 'admin_user', 1),
(4, 1, NULL, '2026-07-01', '2026-07-04', 'Completed', 30.00, 'manager_user', 2),
(8, 1, NULL, '2026-07-25', '2026-07-28', 'Completed', 15.00, 'manager_user', 1),
(9, 1, NULL, '2026-07-25', '2026-07-28', 'Completed', 15.00, 'manager_user', 1),
(10, 1, NULL, '2026-07-25', '2026-07-28', 'Completed', 15.00, 'manager_user', 1),
(11, 1, NULL, '2026-07-25', '2026-07-28', 'Completed', 15.00, 'manager_user', 1),
(12, 1, NULL, '2026-07-25', '2026-07-28', 'Completed', 15.00, 'manager_user', 1),
(13, 1, NULL, '2026-07-26', '2026-07-29', 'Pending', 12.00, 'manager_user', 2);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `contact_person` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `email`, `phone`, `address`, `created_at`) VALUES
(1, 'ABC School Supplies Inc.', 'John Doe', 'hanzllenardkima.sacdalan@gmail.com', '+63 917 123 4567', 'Manila, Philippines', '2026-06-25 10:09:16'),
(2, 'Global Garments Corp.', 'Jane Smith', 'info@globalgarments.ph', '+63 2 8888 1234', 'Cebu City, Philippines', '2026-06-25 10:09:16'),
(3, 'Star Stationery Wholesalers', 'Mark Lee', 'mark@starstationery.com', '+63 908 765 4321', 'Quezon City, Philippines', '2026-06-25 10:09:16');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_products`
--

CREATE TABLE `supplier_products` (
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty_per_unit` int(11) DEFAULT 1,
  `supplier_sku` varchar(50) DEFAULT NULL,
  `wholesale_cost` decimal(10,2) NOT NULL,
  `lead_time_days` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_products`
--

INSERT INTO `supplier_products` (`supplier_id`, `product_id`, `qty_per_unit`, `supplier_sku`, `wholesale_cost`, `lead_time_days`) VALUES
(1, 1, 1, 'SUP-NB-80L', 15.00, 3),
(1, 3, 1, 'SUP-PEN-BLK', 6.00, 3),
(2, 2, 1, 'UNI-POLO-M', 220.00, 14),
(2, 4, 1, 'UNI-SLACK-L', 260.00, 14),
(3, 1, 1, 'STAR-NB80', 16.50, 5);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `cashier_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `cashier_id`, `transaction_date`, `total_amount`, `status`) VALUES
(1, 5, '2026-06-21 09:15:00', 125.00, 'Completed'),
(2, 5, '2026-06-21 10:30:00', 750.00, 'Completed'),
(3, 5, '2026-06-21 11:45:00', 400.00, 'Pending'),
(15, 5, '2026-09-28 13:45:12', 350.00, 'Pending'),
(16, 6, '2026-09-28 14:03:55', 400.00, 'Pending'),
(17, 6, '2026-09-28 14:04:27', 725.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `profile_picture`, `email`, `username`, `password_hash`, `role`, `is_archived`, `approval_status`, `approved_by`, `approved_at`, `created_at`) VALUES
(1, 'System', 'Admin', 'default.jpg', 'admin@store.com', 'admin_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Admin', 0, 'approved', 1, '2026-10-05 19:49:45', '2026-06-21 08:55:49'),
(2, 'Store', 'Owner', 'default.jpg', 'owner@store.com', 'owner_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 0, 'approved', 1, '2026-06-21 08:55:49', '2026-06-21 08:55:49'),
(3, 'Alice', 'Manager', 'default.jpg', 'manager@store.com', 'manager_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 0, 'approved', 1, '2026-10-05 19:50:02', '2026-06-21 08:55:49'),
(4, 'Bob', 'Custodian', 'default.jpg', 'custodian@store.com', 'custodian_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 0, 'approved', 1, '2026-10-05 19:49:59', '2026-06-21 08:55:49'),
(5, 'Charlie', 'Cashier', 'default.jpg', 'cashier@store.com', 'cashier_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 0, 'approved', 1, '2026-10-05 19:49:50', '2026-06-21 08:55:49'),
(6, 'John', 'Doe', 'default.jpg', 'customer@store.com', 'customer_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Customer', 0, 'approved', 1, '2026-10-05 20:39:47', '2026-06-21 08:55:49'),
(7, 'Llenard Kim', 'Sacdalan', 'hanz.jpg', 'kim.player.unknown@outlook.com', 'hanz', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 0, 'approved', 1, '2026-07-28 21:58:28', '2026-07-28 21:58:28'),
(8, 'vus', 'cus', 'default.jpg', 'lus@gmail.com', 'cus', '$2y$10$mjsmRbCgvxNatTW1BeA0tO8quUsrbcS6UmJ3Y85hRmlKhdS1EHQR2', 'custodian', 0, 'approved', 1, '2026-10-05 19:49:52', '2026-07-28 22:04:01'),
(9, 'ss', 'sses', 'default.jpg', 'sses@gmail.com', 'sses', '$2y$10$Cine16phVFmoVyneWrpE/OUZmdjlO8KT1ebKm3dKwHCfl0axzF6RW', 'Cashier', 1, 'approved', 1, '2026-10-05 21:44:26', '2026-10-05 20:30:26'),
(10, 'David', 'Miller', 'default.jpg', 'david.miller@store.com', 'david_m', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 0, 'approved', 1, '2026-10-05 21:00:00', '2026-10-05 08:00:00'),
(11, 'Elena', 'Rostova', 'default.jpg', 'elena.r@store.com', 'elena_r', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 1, 'pending', NULL, NULL, '2026-10-05 21:15:00'),
(12, 'Franklin', 'Clinton', 'default.jpg', 'franklin@store.com', 'frank_c', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 1, 'pending', NULL, NULL, '2026-10-05 21:20:00'),
(13, 'Grace', 'Hopper', 'default.jpg', 'grace.h@store.com', 'grace_h', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Admin', 0, 'approved', 1, '2026-10-05 21:30:00', '2026-10-05 09:12:00'),
(14, 'Henry', 'Cavill', 'default.jpg', 'henry@store.com', 'henry_c', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 1, 'approved', 1, '2026-10-05 21:40:00', '2026-10-05 10:00:00'),
(15, 'Ivy', 'Watson', 'default.jpg', 'ivy.w@store.com', 'ivy_w', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 1, 'rejected', 1, '2026-10-05 21:45:00', '2026-10-05 11:30:00'),
(16, 'James', 'Smith', 'default.jpg', 'james.s@store.com', 'james_s', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 0, 'approved', 1, '2026-10-05 21:50:00', '2026-10-05 12:00:00'),
(17, 'Karen', 'Gillan', 'default.jpg', 'karen.g@store.com', 'karen_g', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 1, 'pending', NULL, NULL, '2026-10-05 22:00:00'),
(18, 'Leo', 'Messi', 'default.jpg', 'leo.m@store.com', 'leo_m', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 0, 'approved', 1, '2026-10-05 22:05:00', '2026-10-05 14:15:00'),
(19, 'Mia', 'Khalil', 'default.jpg', 'mia.k@store.com', 'mia_k', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 1, 'approved', 1, '2026-10-05 22:10:00', '2026-10-05 15:00:00'),
(20, 'Nathan', 'Drake', 'default.jpg', 'nathan.d@store.com', 'nate_d', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 1, 'pending', NULL, NULL, '2026-10-05 22:15:00'),
(21, 'Olivia', 'Rodrigo', 'default.jpg', 'olivia.r@store.com', 'olivia_r', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 0, 'approved', 1, '2026-10-05 22:20:00', '2026-10-05 16:45:00'),
(22, 'Peter', 'Parker', 'default.jpg', 'peter.p@store.com', 'spidey', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 0, 'rejected', 1, '2026-10-05 22:25:00', '2026-10-05 17:00:00'),
(23, 'Quinn', 'Harley', 'default.jpg', 'quinn.h@store.com', 'quinn_h', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 1, 'pending', NULL, NULL, '2026-10-05 22:30:00'),
(24, 'Ryan', 'Reynolds', 'default.jpg', 'ryan.r@store.com', 'deadpool', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 0, 'approved', 1, '2026-10-05 22:35:00', '2026-10-05 18:20:00'),
(25, 'Sophia', 'Loren', 'default.jpg', 'sophia.l@store.com', 'sophia_l', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 0, 'approved', 1, '2026-10-05 22:40:00', '2026-10-05 19:10:00'),
(26, 'Thomas', 'Shelby', 'default.jpg', 'tommy.s@store.com', 'by_order_of', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Admin', 0, 'approved', 1, '2026-10-05 22:42:00', '2026-10-05 19:30:00'),
(27, 'Uma', 'Thurman', 'default.jpg', 'uma.t@store.com', 'beatrix', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Cashier', 1, 'pending', NULL, NULL, '2026-10-05 22:45:00'),
(28, 'Victor', 'Von-Doom', 'default.jpg', 'victor.d@store.com', 'dr_doom', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', 1, 'rejected', 1, '2026-10-05 22:50:00', '2026-10-05 20:02:00'),
(29, 'Wendy', 'Darling', 'default.jpg', 'wendy.d@store.com', 'wendy_d', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Custodian', 1, 'approved', 1, '2026-10-05 22:55:00', '2026-10-05 20:15:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `received_by` (`received_by`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payments_paymongo_checkout_session` (`paymongo_checkout_session_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`purchase_order_id`),
  ADD KEY `fk_po_supplier` (`supplier_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD PRIMARY KEY (`supplier_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `cashier_id` (`cashier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_users_approval_status` (`approval_status`),
  ADD KEY `fk_users_approved_by` (`approved_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`purchase_order_id`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`received_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD CONSTRAINT `supplier_products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
