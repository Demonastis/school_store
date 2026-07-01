-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2026 at 05:15 PM
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
(4, 3, 'Create Purchase Order', 'Inventory Module', 'Generated purchase order #PO-4 for 2 Box(s) of \'Notebook Blue 80 Leaves\' at ₱15.00 per unit from vendor ABC School Supplies Inc..', '2026-07-01 22:09:59');

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
(2, 1, '2026-06-21 14:15:00', 3, 'Partial');

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
(1, 1, 150, 20, '2026-06-21 08:55:49'),
(2, 2, 45, 15, '2026-06-21 08:55:49'),
(3, 3, 12, 20, '2026-06-21 08:55:49'),
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
(1, 1, 1, 5, 25.00, 125.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `transaction_id`, `payment_method`, `amount`, `payment_status`) VALUES
(1, 1, 'cash', 125.00, 'Paid'),
(2, 2, 'e-wallet', 750.00, 'Paid');

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

INSERT INTO `purchase_orders` (`purchase_order_id`, `supplier_id`, `order_date`, `expected_delivery_date`, `status`, `total_amount`, `created_by`, `Box_unit`) VALUES
(1, 1, '2026-06-15', '2026-06-22', 'Pending', 15450.00, 'manager_user', NULL),
(2, 2, '2026-06-10', '2026-06-18', 'Received', 42000.00, 'manager_user', NULL),
(3, 3, '2026-06-20', '2026-06-27', 'Cancelled', 520.50, 'admin_user', NULL),
(4, 1, '2026-07-01', '2026-07-04', 'Pending', 30.00, 'manager_user', 2);

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
(1, 'ABC School Supplies Inc.', 'John Doe', 'sales@abcsupplies.com', '+63 917 123 4567', 'Manila, Philippines', '2026-06-25 10:09:16'),
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
(3, 5, '2026-06-21 11:45:00', 400.00, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'System', 'Admin', 'admin@store.com', 'admin_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Admin', '2026-06-21 08:55:49'),
(2, 'Store', 'Owner', 'owner@store.com', 'owner_user', '$2y$10$7R3vXmG8v79yYg7wX7G4eO6S2K3Xm6N5H9z8Cg7r5F5w2K2U2b2u2', 'Owner', '2026-06-21 08:55:49'),
(3, 'Alice', 'Manager', 'manager@store.com', 'manager_user', '$2a$12$ydSVh6akdiwcT6UVNRKlp.cCj0Fo0blsHxH5Lrscje7VKMQ75D6Pa', 'Manager', '2026-06-21 08:55:49'),
(4, 'Bob', 'Custodian', 'custodian@store.com', 'custodian_user', '$2y$10$7R3vXmG8v79yYg7wX7G4eO6S2K3Xm6N5H9z8Cg7r5F5w2K2U2b2u2', 'Custodian', '2026-06-21 08:55:49'),
(5, 'Charlie', 'Cashier', 'cashier@store.com', 'cashier_user', '$2y$10$7R3vXmG8v79yYg7wX7G4eO6S2K3Xm6N5H9z8Cg7r5F5w2K2U2b2u2', 'Cashier', '2026-06-21 08:55:49'),
(6, 'John', 'Doe', 'customer@store.com', 'customer_user', '$2y$10$7R3vXmG8v79yYg7wX7G4eO6S2K3Xm6N5H9z8Cg7r5F5w2K2U2b2u2', 'Customer', '2026-06-21 08:55:49');

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
  ADD KEY `transaction_id` (`transaction_id`);

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
  ADD KEY `fk_po_supplier` (`supplier_id`);

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
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `purchase_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  ADD CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
