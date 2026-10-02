-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 04:00 PM
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
-- Database: `pointly_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_holders`
--

CREATE TABLE `account_holders` (
  `account_holder_id` bigint(20) UNSIGNED NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_type` enum('owner','staff','guest','customer','other') NOT NULL DEFAULT 'other',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_holders`
--

INSERT INTO `account_holders` (`account_holder_id`, `account_name`, `account_type`, `is_active`, `created_at`) VALUES
(1, 'Justin Bilbao', 'owner', 1, '2026-09-21 13:01:11'),
(2, 'Katrina Bilbao', 'owner', 1, '2026-09-21 13:01:11'),
(3, 'Justin Limjap', 'owner', 1, '2026-09-21 13:01:11'),
(4, 'Em Limjap', 'owner', 1, '2026-09-21 13:01:11'),
(5, 'Ramon Khey', 'owner', 1, '2026-09-21 13:01:11'),
(6, 'Ramon Torres', 'customer', 1, '2026-09-21 13:01:11'),
(7, 'Brandon Vargas', 'customer', 1, '2026-09-21 13:01:11'),
(8, 'Sir Rico', 'customer', 1, '2026-09-21 13:01:11'),
(9, 'Jet Torres', 'customer', 1, '2026-09-21 13:01:11'),
(10, 'VM Derek Palanca', 'customer', 1, '2026-09-21 13:01:11'),
(11, 'Sir Nicolas A.', 'customer', 1, '2026-09-21 13:01:11'),
(12, 'Sir Andrew', 'customer', 1, '2026-09-21 13:01:11'),
(13, 'Sir Kim Damasco', 'customer', 1, '2026-09-21 13:01:11'),
(14, 'Sir Bong', 'customer', 1, '2026-09-21 13:01:11'),
(15, 'Councilors', 'customer', 1, '2026-09-21 13:01:11'),
(16, 'EJ Velez', 'customer', 1, '2026-09-21 13:01:11'),
(17, 'Micholo Ong', 'customer', 1, '2026-09-21 13:01:11');

-- --------------------------------------------------------

--
-- Table structure for table `daily_reports`
--

CREATE TABLE `daily_reports` (
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `report_date` date NOT NULL,
  `shift_name` varchar(50) DEFAULT 'Regular',
  `week_number` tinyint(3) UNSIGNED NOT NULL,
  `cashier_id` bigint(20) UNSIGNED NOT NULL,
  `total_tables` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_pax` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `telegram_declared_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `pos_sales_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','submitted','approved','reopened','voided') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `discount_types`
--

CREATE TABLE `discount_types` (
  `discount_type_id` bigint(20) UNSIGNED NOT NULL,
  `discount_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discount_types`
--

INSERT INTO `discount_types` (`discount_type_id`, `discount_name`, `is_active`) VALUES
(1, 'PWD Discount', 1),
(2, 'Senior Discount', 1),
(3, 'Special Customer Discount', 1),
(4, 'Owner\'s Discount', 1);

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `expense_category_id` bigint(20) UNSIGNED NOT NULL,
  `category_name` varchar(120) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`expense_category_id`, `category_name`, `is_active`) VALUES
(1, 'Marketing Expenses F&B', 1),
(2, 'DJ Ronald F&B', 1),
(3, 'EJ Velez F&B', 1),
(4, 'Guest DJ F&B', 1),
(5, 'Others', 1),
(6, 'Marketing Expenses Sales Deductions', 1),
(7, 'Owner\'s Discount', 1),
(8, 'Food & Beverage Summary', 1),
(9, 'DJ Talent Fee', 1),
(10, 'Bouncer Fee', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` bigint(20) UNSIGNED NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `method_group` enum('cash','card','ewallet','bank_transfer','gift_check','cheque','other') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `method_name`, `method_group`, `is_active`, `display_order`, `created_at`) VALUES
(1, 'Cash', 'cash', 1, 1, '2026-09-21 13:01:11'),
(2, 'GCash + QR PH', 'ewallet', 1, 2, '2026-09-21 13:01:11'),
(3, 'PayMaya', 'ewallet', 1, 3, '2026-09-21 13:01:11'),
(4, 'AMEX', 'card', 1, 4, '2026-09-21 13:01:11'),
(5, 'Visa', 'card', 1, 5, '2026-09-21 13:01:11'),
(6, 'Mastercard', 'card', 1, 6, '2026-09-21 13:01:11'),
(7, 'BancNet', 'card', 1, 7, '2026-09-21 13:01:11'),
(8, 'JCB', 'card', 1, 8, '2026-09-21 13:01:11'),
(9, 'Direct BT BPI Nooma', 'bank_transfer', 1, 9, '2026-09-21 13:01:11'),
(10, 'Direct BT EastWest Nooma', 'bank_transfer', 1, 10, '2026-09-21 13:01:11'),
(11, 'Nooma Gift Check', 'gift_check', 1, 11, '2026-09-21 13:01:11'),
(12, 'Cheque Payment', 'cheque', 1, 12, '2026-09-21 13:01:11');

-- --------------------------------------------------------

--
-- Table structure for table `remittance_types`
--

CREATE TABLE `remittance_types` (
  `remittance_type_id` bigint(20) UNSIGNED NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `remittance_types`
--

INSERT INTO `remittance_types` (`remittance_type_id`, `type_name`, `is_active`, `display_order`) VALUES
(1, 'Cash Remitted', 1, 1),
(2, 'Card Remits - Maya Terminal', 1, 2),
(3, 'Direct BT BPI Nooma', 1, 3),
(4, 'Direct BT EastWest Nooma', 1, 4),
(5, 'Gift Check', 1, 5),
(6, 'Cheque Payment', 1, 6),
(7, 'Other Payments', 1, 7);

-- --------------------------------------------------------

--
-- Table structure for table `report_accounts`
--

CREATE TABLE `report_accounts` (
  `report_account_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `account_holder_id` bigint(20) UNSIGNED NOT NULL,
  `account_category` enum('owners_account','unpaid_account','paid_account','paid_previous_year','paid_current_year','advance_payment') NOT NULL,
  `transaction_type` enum('charge','payment','adjustment') NOT NULL DEFAULT 'charge',
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_adjustments`
--

CREATE TABLE `report_adjustments` (
  `adjustment_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_type` enum('shortage','overage','correction') NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `explanation` varchar(255) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_audit_logs`
--

CREATE TABLE `report_audit_logs` (
  `audit_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` enum('created','updated','submitted','approved','reopened','voided') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_deductions`
--

CREATE TABLE `report_deductions` (
  `deduction_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `discount_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deduction_type` enum('discount','refund','other') NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_expenses`
--

CREATE TABLE `report_expenses` (
  `expense_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `expense_category_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `receipt_reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_payments`
--

CREATE TABLE `report_payments` (
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method_id` bigint(20) UNSIGNED NOT NULL,
  `payment_source` enum('sales','account_collection','advance_payment','other_payment') NOT NULL DEFAULT 'sales',
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_remittances`
--

CREATE TABLE `report_remittances` (
  `remittance_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `remittance_type_id` bigint(20) UNSIGNED NOT NULL,
  `expected_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `actual_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_number` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_sales`
--

CREATE TABLE `report_sales` (
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `report_id` bigint(20) UNSIGNED NOT NULL,
  `sales_type` enum('kitchen','bar','corkage','service_charge','tip','grab_gross','other') NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','cashier') NOT NULL DEFAULT 'cashier',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Nooma Administrator', 'admin', 'admin123', 'admin', 1, '2026-09-21 13:24:09', '2026-09-22 01:18:47'),
(2, 'Nooma Manager', 'manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCk5M9QwJvZ9Z3L7O2y', 'manager', 1, '2026-09-21 13:24:09', '2026-09-22 01:19:00'),
(3, 'Nooma Cashier', 'cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCk5M9QwJvZ9Z3L7O2y', 'cashier', 1, '2026-09-21 13:24:09', '2026-09-22 01:19:09');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_daily_report_totals`
-- (See below for the actual view)
--
CREATE TABLE `vw_daily_report_totals` (
`report_id` bigint(20) unsigned
,`report_number` varchar(50)
,`report_date` date
,`week_number` tinyint(3) unsigned
,`cashier_id` bigint(20) unsigned
,`status` enum('draft','submitted','approved','reopened','voided')
,`kitchen_sales` decimal(34,2)
,`bar_sales` decimal(34,2)
,`corkage` decimal(34,2)
,`service_charge` decimal(34,2)
,`tip_collected` decimal(34,2)
,`grab_sales_gross` decimal(34,2)
,`total_sales` decimal(36,2)
,`total_payments` decimal(34,2)
,`total_accounts` decimal(34,2)
,`total_discounts` decimal(34,2)
,`total_refunds` decimal(34,2)
,`total_expenses` decimal(34,2)
,`total_remitted` decimal(34,2)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_daily_report_totals`
--
DROP TABLE IF EXISTS `vw_daily_report_totals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_daily_report_totals`  AS SELECT `dr`.`report_id` AS `report_id`, `dr`.`report_number` AS `report_number`, `dr`.`report_date` AS `report_date`, `dr`.`week_number` AS `week_number`, `dr`.`cashier_id` AS `cashier_id`, `dr`.`status` AS `status`, coalesce(`s`.`kitchen_sales`,0) AS `kitchen_sales`, coalesce(`s`.`bar_sales`,0) AS `bar_sales`, coalesce(`s`.`corkage`,0) AS `corkage`, coalesce(`s`.`service_charge`,0) AS `service_charge`, coalesce(`s`.`tip`,0) AS `tip_collected`, coalesce(`s`.`grab_gross`,0) AS `grab_sales_gross`, coalesce(`s`.`kitchen_sales`,0) + coalesce(`s`.`bar_sales`,0) + coalesce(`s`.`corkage`,0) AS `total_sales`, coalesce(`p`.`total_payments`,0) AS `total_payments`, coalesce(`a`.`total_accounts`,0) AS `total_accounts`, coalesce(`d`.`total_discounts`,0) AS `total_discounts`, coalesce(`d`.`total_refunds`,0) AS `total_refunds`, coalesce(`e`.`total_expenses`,0) AS `total_expenses`, coalesce(`r`.`total_remitted`,0) AS `total_remitted` FROM ((((((`daily_reports` `dr` left join (select `report_sales`.`report_id` AS `report_id`,sum(case when `report_sales`.`sales_type` = 'kitchen' then `report_sales`.`amount` else 0 end) AS `kitchen_sales`,sum(case when `report_sales`.`sales_type` = 'bar' then `report_sales`.`amount` else 0 end) AS `bar_sales`,sum(case when `report_sales`.`sales_type` = 'corkage' then `report_sales`.`amount` else 0 end) AS `corkage`,sum(case when `report_sales`.`sales_type` = 'service_charge' then `report_sales`.`amount` else 0 end) AS `service_charge`,sum(case when `report_sales`.`sales_type` = 'tip' then `report_sales`.`amount` else 0 end) AS `tip`,sum(case when `report_sales`.`sales_type` = 'grab_gross' then `report_sales`.`amount` else 0 end) AS `grab_gross` from `report_sales` group by `report_sales`.`report_id`) `s` on(`s`.`report_id` = `dr`.`report_id`)) left join (select `report_payments`.`report_id` AS `report_id`,sum(`report_payments`.`amount`) AS `total_payments` from `report_payments` group by `report_payments`.`report_id`) `p` on(`p`.`report_id` = `dr`.`report_id`)) left join (select `report_accounts`.`report_id` AS `report_id`,sum(`report_accounts`.`amount`) AS `total_accounts` from `report_accounts` group by `report_accounts`.`report_id`) `a` on(`a`.`report_id` = `dr`.`report_id`)) left join (select `report_deductions`.`report_id` AS `report_id`,sum(case when `report_deductions`.`deduction_type` = 'discount' then `report_deductions`.`amount` else 0 end) AS `total_discounts`,sum(case when `report_deductions`.`deduction_type` = 'refund' then `report_deductions`.`amount` else 0 end) AS `total_refunds` from `report_deductions` group by `report_deductions`.`report_id`) `d` on(`d`.`report_id` = `dr`.`report_id`)) left join (select `report_expenses`.`report_id` AS `report_id`,sum(`report_expenses`.`amount`) AS `total_expenses` from `report_expenses` group by `report_expenses`.`report_id`) `e` on(`e`.`report_id` = `dr`.`report_id`)) left join (select `report_remittances`.`report_id` AS `report_id`,sum(`report_remittances`.`actual_amount`) AS `total_remitted` from `report_remittances` group by `report_remittances`.`report_id`) `r` on(`r`.`report_id` = `dr`.`report_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_holders`
--
ALTER TABLE `account_holders`
  ADD PRIMARY KEY (`account_holder_id`),
  ADD UNIQUE KEY `uq_account_name` (`account_name`);

--
-- Indexes for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD UNIQUE KEY `report_number` (`report_number`),
  ADD UNIQUE KEY `uq_cashier_date_shift` (`report_date`,`cashier_id`,`shift_name`),
  ADD KEY `idx_report_date` (`report_date`),
  ADD KEY `idx_report_status` (`status`),
  ADD KEY `cashier_id` (`cashier_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `discount_types`
--
ALTER TABLE `discount_types`
  ADD PRIMARY KEY (`discount_type_id`),
  ADD UNIQUE KEY `discount_name` (`discount_name`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`expense_category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

--
-- Indexes for table `remittance_types`
--
ALTER TABLE `remittance_types`
  ADD PRIMARY KEY (`remittance_type_id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- Indexes for table `report_accounts`
--
ALTER TABLE `report_accounts`
  ADD PRIMARY KEY (`report_account_id`),
  ADD KEY `idx_account_report` (`report_id`),
  ADD KEY `idx_account_holder` (`account_holder_id`),
  ADD KEY `idx_account_category` (`account_category`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `report_adjustments`
--
ALTER TABLE `report_adjustments`
  ADD PRIMARY KEY (`adjustment_id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `report_audit_logs`
--
ALTER TABLE `report_audit_logs`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `idx_audit_report` (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `report_deductions`
--
ALTER TABLE `report_deductions`
  ADD PRIMARY KEY (`deduction_id`),
  ADD KEY `idx_deduction_report` (`report_id`),
  ADD KEY `discount_type_id` (`discount_type_id`);

--
-- Indexes for table `report_expenses`
--
ALTER TABLE `report_expenses`
  ADD PRIMARY KEY (`expense_id`),
  ADD KEY `idx_expense_report` (`report_id`),
  ADD KEY `expense_category_id` (`expense_category_id`);

--
-- Indexes for table `report_payments`
--
ALTER TABLE `report_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_report` (`report_id`),
  ADD KEY `idx_payment_method` (`payment_method_id`);

--
-- Indexes for table `report_remittances`
--
ALTER TABLE `report_remittances`
  ADD PRIMARY KEY (`remittance_id`),
  ADD KEY `idx_remittance_report` (`report_id`),
  ADD KEY `remittance_type_id` (`remittance_type_id`);

--
-- Indexes for table `report_sales`
--
ALTER TABLE `report_sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `idx_sales_report_type` (`report_id`,`sales_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_holders`
--
ALTER TABLE `account_holders`
  MODIFY `account_holder_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `discount_types`
--
ALTER TABLE `discount_types`
  MODIFY `discount_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `expense_category_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `remittance_types`
--
ALTER TABLE `remittance_types`
  MODIFY `remittance_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `report_accounts`
--
ALTER TABLE `report_accounts`
  MODIFY `report_account_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_adjustments`
--
ALTER TABLE `report_adjustments`
  MODIFY `adjustment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_audit_logs`
--
ALTER TABLE `report_audit_logs`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_deductions`
--
ALTER TABLE `report_deductions`
  MODIFY `deduction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_expenses`
--
ALTER TABLE `report_expenses`
  MODIFY `expense_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_payments`
--
ALTER TABLE `report_payments`
  MODIFY `payment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_remittances`
--
ALTER TABLE `report_remittances`
  MODIFY `remittance_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_sales`
--
ALTER TABLE `report_sales`
  MODIFY `sale_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD CONSTRAINT `daily_reports_ibfk_1` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `daily_reports_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `report_accounts`
--
ALTER TABLE `report_accounts`
  ADD CONSTRAINT `report_accounts_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_accounts_ibfk_2` FOREIGN KEY (`account_holder_id`) REFERENCES `account_holders` (`account_holder_id`),
  ADD CONSTRAINT `report_accounts_ibfk_3` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Constraints for table `report_adjustments`
--
ALTER TABLE `report_adjustments`
  ADD CONSTRAINT `report_adjustments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_adjustments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `report_adjustments_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `report_audit_logs`
--
ALTER TABLE `report_audit_logs`
  ADD CONSTRAINT `report_audit_logs_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`),
  ADD CONSTRAINT `report_audit_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `report_deductions`
--
ALTER TABLE `report_deductions`
  ADD CONSTRAINT `report_deductions_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_deductions_ibfk_2` FOREIGN KEY (`discount_type_id`) REFERENCES `discount_types` (`discount_type_id`);

--
-- Constraints for table `report_expenses`
--
ALTER TABLE `report_expenses`
  ADD CONSTRAINT `report_expenses_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_expenses_ibfk_2` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`expense_category_id`);

--
-- Constraints for table `report_payments`
--
ALTER TABLE `report_payments`
  ADD CONSTRAINT `report_payments_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Constraints for table `report_remittances`
--
ALTER TABLE `report_remittances`
  ADD CONSTRAINT `report_remittances_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `report_remittances_ibfk_2` FOREIGN KEY (`remittance_type_id`) REFERENCES `remittance_types` (`remittance_type_id`);

--
-- Constraints for table `report_sales`
--
ALTER TABLE `report_sales`
  ADD CONSTRAINT `report_sales_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `daily_reports` (`report_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
