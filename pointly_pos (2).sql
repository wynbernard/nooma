-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2026 at 04:02 AM
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

--
-- Dumping data for table `daily_reports`
--

INSERT INTO `daily_reports` (`report_id`, `report_number`, `report_date`, `shift_name`, `week_number`, `cashier_id`, `total_tables`, `total_pax`, `telegram_declared_total`, `pos_sales_total`, `status`, `notes`, `submitted_at`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(4, 'SR-20260923061047-830', '2026-09-14', 'Lunch', 38, 1, 3, 10, 5940.97, 5454.00, 'draft', '{\"saleDate\":\"2026-09-14\",\"pax\":\"10\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"kitchenSale\":\"4114.00\",\"barSale\":\"1340.00\",\"corkage\":\"\",\"totalSale\":\"5454.00\",\"serviceCharge\":\"486.97\",\"grandTotal\":\"5940.97\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1775.54\",\"mastercard\":\"4165.43\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"5,940.97\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"5,940.97\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"228.58\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"228.58\",\"grossSale\":\"6,169.55\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"228.58\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 04:10:47', '2026-09-23 04:24:58'),
(5, 'SR-20260923062306-347', '2026-09-14', 'Closing', 38, 1, 10, 49, 40757.74, 37418.39, 'draft', '{\"saleDate\":\"2026-09-14\",\"pax\":\"49\",\"tableNumber\":\"10\",\"saleType\":\"Closing\",\"kitchenSale\":\"18898.39\",\"barSale\":\"14220\",\"corkage\":\"4300\",\"totalSale\":\"37418.39\",\"serviceCharge\":\"3339.35\",\"grandTotal\":\"40757.74\",\"cashRemitted\":\"3379\",\"gcashQrph\":\"4466.07\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"27024.72\",\"mastercard\":\"446.61\",\"bancnet\":\"2587.05\",\"jcb\":\"2854.29\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"40,757.74\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"40,757.74\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"222.65\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"222.65\",\"grossSale\":\"40,980.39\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"222.65\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"3015\",\"onlineTips\":\"2200.95\",\"cashRemittedShortage\":\"3,379.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 04:23:06', '2026-09-23 04:23:06'),
(6, 'SR-20260923064535-790', '2026-09-15', 'Lunch', 38, 1, 4, 12, 15195.54, 13950.00, 'draft', '{\"saleDate\":\"2026-09-15\",\"pax\":\"12\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"kitchenSale\":\"11820.00\",\"barSale\":\"2130\",\"corkage\":\"\",\"totalSale\":\"13950.00\",\"serviceCharge\":\"1245.54\",\"grandTotal\":\"15195.54\",\"cashRemitted\":\"4139\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"9150\",\"bancnet\":\"1906.25\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"15,195.25\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"15,195.25\",\"reconShortOver\":\"0.29\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"15,195.25\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"4,139.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 04:45:35', '2026-09-23 04:45:35'),
(7, 'SR-20260923064959-399', '2026-09-15', 'Closing', 38, 1, 7, 22, 16033.49, 14875.00, 'draft', '{\"saleDate\":\"2026-09-15\",\"pax\":\"22\",\"tableNumber\":\"7\",\"saleType\":\"Closing\",\"kitchenSale\":\"8555.00\",\"barSale\":\"6320.00\",\"corkage\":\"\",\"totalSale\":\"14875.00\",\"serviceCharge\":\"1158.49\",\"grandTotal\":\"16033.49\",\"cashRemitted\":\"1241.00\",\"gcashQrph\":\"3469.38\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"7336.34\",\"mastercard\":\"2085.98\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"14,132.70\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"1900.00\",\"ownerNote_1\":\"paid\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"1,900.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"16,032.70\",\"reconShortOver\":\"0.79\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"424.11\",\"totalSalesDeduction\":\"424.11\",\"grossSale\":\"16,456.81\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"424.11\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"1,241.00\",\"ownerAccounts\":{\"1\":\"1900.00\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 04:49:59', '2026-09-23 10:05:03'),
(8, 'SR-20260923071241-675', '2026-09-16', 'Lunch', 38, 1, 2, 6, 3831.56, 3508.48, 'draft', '{\"saleDate\":\"2026-09-16\",\"pax\":\"6\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"kitchenSale\":\"3318.48\",\"barSale\":\"190\",\"corkage\":\"\",\"totalSale\":\"3508.48\",\"serviceCharge\":\"323.08\",\"grandTotal\":\"3831.56\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"3831.56\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"3,831.56\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"3,831.56\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"229.07\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"229.07\",\"grossSale\":\"4,060.63\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"229.07\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 05:12:41', '2026-09-23 05:12:41'),
(9, 'SR-20260923071653-697', '2026-09-16', 'Closing', 38, 1, 21, 74, 59415.26, 54533.55, 'draft', '{\"saleDate\":\"2026-09-16\",\"pax\":\"74\",\"tableNumber\":\"21\",\"saleType\":\"Closing\",\"kitchenSale\":\"37558.55\",\"barSale\":\"16975\",\"corkage\":\"\",\"totalSale\":\"54533.55\",\"serviceCharge\":\"4881.71\",\"grandTotal\":\"59415.26\",\"cashRemitted\":\"7059\",\"gcashQrph\":\"1755.18\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"2467.24\",\"mastercard\":\"43242.65\",\"bancnet\":\"4890.90\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"59,414.97\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"59,414.97\",\"reconShortOver\":\"0.29\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"294.66\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"294.66\",\"grossSale\":\"59,709.63\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"294.66\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"7,059.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 05:16:53', '2026-09-23 05:16:53'),
(10, 'SR-20260923073753-953', '2026-09-17', 'Lunch', 38, 1, 2, 10, 8141.21, 7466.06, 'draft', '{\"saleDate\":\"2026-09-17\",\"pax\":\"10\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6186.06\",\"barSale\":\"1280.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"7466.06\",\"serviceCharge\":\"675.15\",\"grandTotal\":\"8141.21\",\"cashRemitted\":\"8141.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"8,141.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"8,141.00\",\"reconShortOver\":\"0.21\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"199.33\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"199.33\",\"grossSale\":\"8,340.33\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"199.33\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"8,141.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 05:37:53', '2026-10-06 04:20:35'),
(11, 'SR-20260923074340-173', '2026-09-17', 'Closing', 38, 1, 8, 34, 29887.81, 27438.00, 'draft', '{\"saleDate\":\"2026-09-17\",\"pax\":\"34\",\"tableNumber\":\"8\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"7483.00\",\"barSale\":\"19955.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"27438.00\",\"serviceCharge\":\"2449.81\",\"grandTotal\":\"29887.81\",\"cashRemitted\":\"817.00\",\"gcashQrph\":\"2450.89\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"326.79\",\"mastercard\":\"18689.96\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"22,284.64\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"SIR NINO\",\"unpaidAccountAmount\":\"7603.21\",\"unpaidAccountNote\":\"paid\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherCashNote\":\"\",\"otherMayaTerminal\":\"\",\"otherMayaTerminalNote\":\"\",\"otherBpiNooma\":\"\",\"otherBpiNoomaNote\":\"\",\"otherEastwestNooma\":\"\",\"otherEastwestNoomaNote\":\"\",\"otherGiftCheck\":\"\",\"otherGiftCheckNote\":\"\",\"otherCheques\":\"\",\"otherChequesNote\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"7,603.21\",\"ownersDiscount\":\"\",\"paidAccounts\":\"7603.21\",\"totalSalesBasedOnPayment\":\"29,887.85\",\"reconShortOver\":\"-7,603.25\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"216.07\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"216.07\",\"grossSale\":\"30,103.92\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"216.07\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"11198.20\",\"grabSaleGross\":\"1180.00\",\"onlineTips\":\"861.40\",\"cashRemittedShortage\":\"-10,381.20\",\"unpaidAccountNote_0\":\"paid\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 05:43:40', '2026-09-29 09:06:52'),
(12, 'SR-20260923100647-191', '2026-09-18', 'Lunch', 38, 1, 4, 10, 9885.87, 9070.29, 'draft', '{\"saleDate\":\"2026-09-18\",\"pax\":\"10\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"8560.29\",\"barSale\":\"510.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"9070.29\",\"serviceCharge\":\"815.58\",\"grandTotal\":\"9885.87\",\"cashRemitted\":\"3050.00\",\"gcashQrph\":\"2489.02\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"2004.29\",\"bancnet\":\"2341.96\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"9,885.27\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"SIR NINO\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"9,885.27\",\"reconShortOver\":\"0.60\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"134.37\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"134.37\",\"grossSale\":\"10,019.64\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"134.37\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"3,050.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 08:06:47', '2026-10-06 04:22:56'),
(13, 'SR-20260923101227-906', '2026-09-18', 'Closing', 38, 1, 35, 96, 66702.59, 61213.31, 'draft', '{\"saleDate\":\"2026-09-18\",\"pax\":\"96\",\"tableNumber\":\"35\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"27253.31\",\"barSale\":\"33960.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"61213.31\",\"serviceCharge\":\"5489.28\",\"grandTotal\":\"66702.59\",\"cashRemitted\":\"13721.00\",\"gcashQrph\":\"21058.98\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"4444.28\",\"mastercard\":\"13628.34\",\"bancnet\":\"7313.56\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"60,166.16\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"SIR NINO\",\"unpaidAccountAmount\":\"6535.71\",\"unpaidAccountNote\":\"paid (09/22/26) Transfer Via BPI\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"6,535.71\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"60,166.16\",\"reconShortOver\":\"0.72\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"624.93\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"624.93\",\"grossSale\":\"60,791.09\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"624.93\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"13,721.00\",\"unpaidAccountNote_0\":\"paid (09/22/26) Transfer Via BPI\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 08:12:27', '2026-10-06 04:26:38'),
(14, 'SR-20260923101734-531', '2026-09-19', 'Lunch', 38, 1, 2, 4, 2718.22, 2570.00, 'draft', '{\"saleDate\":\"2026-09-19\",\"pax\":\"4\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"kitchenSale\":\"2345.00\",\"barSale\":\"225\",\"corkage\":\"\",\"totalSale\":\"2570.00\",\"serviceCharge\":\"148.22\",\"grandTotal\":\"2718.22\",\"cashRemitted\":\"910\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"1808.22\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"2,718.22\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"2,718.22\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"2,718.22\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"910.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 08:17:34', '2026-09-23 08:17:34'),
(15, 'SR-20260923102148-139', '2026-09-19', 'Closing', 38, 1, 40, 110, 63236.11, 58033.98, 'draft', '{\"saleDate\":\"2026-09-19\",\"pax\":\"110\",\"tableNumber\":\"40\",\"saleType\":\"Closing\",\"kitchenSale\":\"29383.98\",\"barSale\":\"28650\",\"corkage\":\"\",\"totalSale\":\"58033.98\",\"serviceCharge\":\"5202.13\",\"grandTotal\":\"63236.11\",\"cashRemitted\":\"27513\",\"gcashQrph\":\"7555.18\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"10762.27\",\"mastercard\":\"3654.57\",\"bancnet\":\"12106.07\",\"jcb\":\"1644.82\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"63,235.91\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"63,235.91\",\"reconShortOver\":\"0.20\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"478.76\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"478.76\",\"grossSale\":\"63,714.67\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"478.76\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"27,513.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 08:21:48', '2026-09-23 08:21:48'),
(16, 'SR-20260923102859-806', '2026-09-20', 'Lunch', 38, 1, 5, 23, 12939.00, 11848.51, 'draft', '{\"saleDate\":\"2026-09-20\",\"pax\":\"23\",\"tableNumber\":\"5\",\"saleType\":\"Lunch\",\"kitchenSale\":\"10638.51\",\"barSale\":\"1210\",\"corkage\":\"\",\"totalSale\":\"11848.51\",\"serviceCharge\":\"1090.49\",\"grandTotal\":\"12939\",\"cashRemitted\":\"2471.89\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"5220.02\",\"mastercard\":\"3428.99\",\"bancnet\":\"1818.10\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"12,939.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"12,939.00\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"760.31\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"760.31\",\"grossSale\":\"13,699.31\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"760.31\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"2550\",\"onlineTips\":\"1861.50\",\"cashRemittedShortage\":\"2,471.89\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 08:28:59', '2026-09-23 08:28:59'),
(17, 'SR-20260923104520-391', '2026-09-20', 'Closing', 38, 1, 16, 49, 31209.83, 28672.71, 'draft', '{\"saleDate\":\"2026-09-20\",\"pax\":\"49\",\"tableNumber\":\"16\",\"saleType\":\"Closing\",\"kitchenSale\":\"20887.71\",\"barSale\":\"7785\",\"corkage\":\"\",\"totalSale\":\"28672.71\",\"serviceCharge\":\"2537.12\",\"grandTotal\":\"31209.83\",\"cashRemitted\":\"5474\",\"gcashQrph\":\"2516.26\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"11995.15\",\"mastercard\":\"4356.79\",\"bancnet\":\"3691.83\",\"jcb\":\"3175.27\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"31,209.30\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"31,209.30\",\"reconShortOver\":\"0.53\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"654.55\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"654.55\",\"grossSale\":\"31,863.85\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"654.55\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"600\",\"onlineTips\":\"438.00\",\"cashRemittedShortage\":\"5,474.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-23 08:45:20', '2026-09-23 08:45:20'),
(18, 'SR-20260924053436-595', '2026-09-21', 'Lunch', 39, 1, 4, 30, 18448.56, 17084.99, 'draft', '{\"saleDate\":\"2026-09-21\",\"pax\":\"30\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"kitchenSale\":\"14219.99\",\"barSale\":\"2865\",\"corkage\":\"\",\"totalSale\":\"17084.99\",\"serviceCharge\":\"1363.57\",\"grandTotal\":\"18448.56\",\"cashRemitted\":\"5183\",\"gcashQrph\":\"5631.61\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"7633.71\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"18,448.32\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"\",\"ownerNote_1\":\"\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"18,448.32\",\"reconShortOver\":\"0.24\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"529.47\",\"specialCustomerDiscount\":\"413.38\",\"totalSalesDeduction\":\"942.85\",\"grossSale\":\"19,391.17\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"942.85\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"5,183.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-24 03:34:36', '2026-09-24 03:34:36'),
(19, 'SR-20260924053722-793', '2026-09-22', 'Lunch', 39, 1, 3, 7, 3460.94, 3176.21, 'draft', '{\"saleDate\":\"2026-09-22\",\"pax\":\"7\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"kitchenSale\":\"2791.21\",\"barSale\":\"385\",\"corkage\":\"\",\"totalSale\":\"3176.21\",\"serviceCharge\":\"284.73\",\"grandTotal\":\"3460.94\",\"cashRemitted\":\"545\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1467.19\",\"mastercard\":\"\",\"bancnet\":\"1448.75\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"3,460.94\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"\",\"ownerNote_1\":\"\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"3,460.94\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"139.73\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"139.73\",\"grossSale\":\"3,600.67\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"139.73\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"545.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-24 03:37:22', '2026-09-24 03:37:22'),
(20, 'SR-20260924064110-977', '2026-09-21', 'Closing', 39, 1, 12, 35, 32195.56, 29529.01, 'draft', '{\"saleDate\":\"2026-09-21\",\"pax\":\"35\",\"tableNumber\":\"12\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"22404.01\",\"barSale\":\"7125.00\",\"corkage\":\"\",\"totalSale\":\"29529.01\",\"serviceCharge\":\"2666.55\",\"grandTotal\":\"32195.56\",\"cashRemitted\":\"6084.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"2352.86\",\"mastercard\":\"18911.26\",\"bancnet\":\"4063.04\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"784.29\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"32,195.45\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"\",\"ownerNote_1\":\"\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"32,195.45\",\"reconShortOver\":\"0.11\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"700.61\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"700.61\",\"grossSale\":\"32,896.06\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"700.61\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"1255.60\",\"grabSaleGross\":\"860.00\",\"onlineTips\":\"627.80\",\"cashRemittedShortage\":\"4,828.40\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-24 04:41:10', '2026-09-24 08:30:31'),
(21, 'SR-20260924064717-745', '2026-09-22', 'Closing', 39, 1, 9, 34, 28057.50, 25752.99, 'draft', '{\"saleDate\":\"2026-09-22\",\"pax\":\"34\",\"tableNumber\":\"9\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"19837.99\",\"barSale\":\"5915.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"25752.99\",\"serviceCharge\":\"2304.51\",\"grandTotal\":\"28057.50\",\"cashRemitted\":\"3965.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"4754.73\",\"mastercard\":\"14032.95\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"22,752.68\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"Miguel Rivero\",\"unpaidAccountAmount\":\"5304.82\",\"unpaidAccountNote\":\" (paid)\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"5,304.82\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"22,752.68\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"120.01\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"120.01\",\"grossSale\":\"22,872.69\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"120.01\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"3,965.00\",\"unpaidAccountNote_0\":\" (paid)\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-24 04:47:17', '2026-10-06 04:28:38'),
(28, 'SR-20260925034027-472', '2026-09-23', 'Lunch', 39, 1, 2, 8, 2081.16, 2045.00, 'draft', '{\"saleDate\":\"2026-09-23\",\"pax\":\"8\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"1225.00\",\"barSale\":\"820.00\",\"corkage\":\"\",\"totalSale\":\"2045.00\",\"serviceCharge\":\"36.16\",\"grandTotal\":\"2081.16\",\"cashRemitted\":\"1640.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"441.16\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"2,081.16\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"\",\"ownerNote_1\":\"\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"2,081.16\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"366.07\",\"totalSalesDeduction\":\"366.07\",\"grossSale\":\"2,447.23\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"366.07\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"1830\",\"onlineTips\":\"1,335.90\",\"cashRemittedShortage\":\"1,640.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-25 01:40:27', '2026-09-25 02:22:27'),
(29, 'SR-20260925034642-864', '2026-09-23', 'Closing', 39, 1, 5, 10, 15902.75, 14596.69, 'draft', '{\"saleDate\":\"2026-09-23\",\"pax\":\"10\",\"tableNumber\":\"5\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"4611.69\",\"barSale\":\"9985.00\",\"corkage\":\"\",\"totalSale\":\"14596.69\",\"serviceCharge\":\"1306.06\",\"grandTotal\":\"15902.75\",\"cashRemitted\":\"2593.00\",\"gcashQrph\":\"370.36\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"10021.43\",\"mastercard\":\"2264.34\",\"bancnet\":\"653.57\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"15,902.70\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"\",\"ownerNote_1\":\"\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"15,902.70\",\"reconShortOver\":\"0.05\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"113.54\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"113.54\",\"grossSale\":\"16,016.24\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"113.54\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"1153.40\",\"grabSaleGross\":\"1580.00\",\"onlineTips\":\"1,153.40\",\"cashRemittedShortage\":\"1,439.60\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-25 01:46:42', '2026-09-25 02:21:25'),
(30, 'SR-20260925035048-192', '2026-09-24', 'Lunch', 39, 1, 3, 8, 5159.61, 4898.00, 'draft', '{\"saleDate\":\"2026-09-24\",\"pax\":\"8\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"3983.00\",\"barSale\":\"915\",\"corkage\":\"\",\"totalSale\":\"4898.00\",\"serviceCharge\":\"261.61\",\"grandTotal\":\"5159.61\",\"cashRemitted\":\"3191\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1968\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"5,159.00\",\"owner_4\":\"\",\"ownerNote_4\":\"\",\"owner_1\":\"\",\"ownerNote_1\":\"\",\"owner_3\":\"\",\"ownerNote_3\":\"\",\"owner_2\":\"\",\"ownerNote_2\":\"\",\"owner_5\":\"\",\"ownerNote_5\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"5,159.00\",\"reconShortOver\":\"0.61\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"439.30\",\"totalSalesDeduction\":\"439.30\",\"grossSale\":\"5,598.30\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"439.30\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"3,191.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-25 01:50:48', '2026-09-25 01:50:48'),
(31, 'SR-20260925035518-726', '2026-09-24', 'Closing', 39, 1, 8, 24, 27241.90, 24999.34, 'draft', '{\"saleDate\":\"2026-09-24\",\"pax\":\"24\",\"tableNumber\":\"8\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"11884.34\",\"barSale\":\"13115.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"24999.34\",\"serviceCharge\":\"2242.56\",\"grandTotal\":\"27241.90\",\"cashRemitted\":\"9103.00\",\"gcashQrph\":\"1525.00\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"6205.33\",\"mastercard\":\"5010.72\",\"bancnet\":\"5397.42\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"27,241.47\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"27,241.47\",\"reconShortOver\":\"0.43\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"244.16\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"244.16\",\"grossSale\":\"27,485.63\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"244.16\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"3591.60\",\"grabSaleGross\":\"1230.00\",\"onlineTips\":\"897.90\",\"cashRemittedShortage\":\"5,511.40\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-25 01:55:18', '2026-09-25 07:54:52'),
(41, 'SR-20260928044051-365', '2026-09-25', 'Lunch', 39, 1, 7, 20, 13855.94, 12704.83, 'draft', '{\"saleDate\":\"2026-09-25\",\"pax\":\"20\",\"tableNumber\":\"7\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"11354.83\",\"barSale\":\"1350.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"12704.83\",\"serviceCharge\":\"1151.11\",\"grandTotal\":\"13855.94\",\"cashRemitted\":\"4732\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"5558.73\",\"bancnet\":\"3564.68\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"13,855.41\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"13,855.41\",\"reconShortOver\":\"0.53\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"390.73\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"390.73\",\"grossSale\":\"14,246.14\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"390.73\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"4,732.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 02:40:51', '2026-09-28 05:35:45'),
(42, 'SR-20260928053003-435', '2026-09-25', 'Closing', 39, 1, 45, 129, 106970.13, 98986.11, 'draft', '{\"saleDate\":\"2026-09-25\",\"pax\":\"129\",\"tableNumber\":\"45\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"42266.11\",\"barSale\":\"56720.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"98986.11\",\"serviceCharge\":\"7984.02\",\"grandTotal\":\"106970.13\",\"cashRemitted\":\"11042.00\",\"gcashQrph\":\"9923.38\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"12155.89\",\"mastercard\":\"58140.62\",\"bancnet\":\"3496.61\",\"jcb\":\"1546.79\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"96,305.29\",\"owner_4\":\"\",\"owner_1\":\"8698.60\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"Micholo Ong\",\"unpaidAccountAmount\":\"1966.16\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"8,698.60\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"1579.00\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"1,579.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"1,966.16\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"105,003.89\",\"reconShortOver\":\"0.08\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"195.25\",\"specialCustomerDiscount\":\"862.94\",\"totalSalesDeduction\":\"1,058.19\",\"grossSale\":\"106,062.08\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"1,058.19\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"788.40\",\"grabSaleGross\":\"540.00\",\"onlineTips\":\"394.20\",\"cashRemittedShortage\":\"10,253.60\",\"ownerAccounts\":{\"1\":\"8698.60\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 03:30:03', '2026-10-06 04:32:04'),
(43, 'SR-20260928053258-686', '2026-09-26', 'Lunch', 39, 1, 3, 9, 7848.31, 7205.00, 'draft', '{\"saleDate\":\"2026-09-26\",\"pax\":\"9\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6310.00\",\"barSale\":\"895.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"7205.00\",\"serviceCharge\":\"643.31\",\"grandTotal\":\"7848.31\",\"cashRemitted\":\"7848\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"7,848.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"7,848.00\",\"reconShortOver\":\"0.31\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"7,848.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"335.80\",\"grabSaleGross\":\"460.00\",\"onlineTips\":\"335.80\",\"cashRemittedShortage\":\"7,512.20\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 03:32:58', '2026-09-28 05:36:38'),
(44, 'SR-20260928053727-250', '2026-09-26', 'Closing', 39, 1, 30, 112, 54852.96, 50428.13, 'draft', '{\"saleDate\":\"2026-09-26\",\"pax\":\"112\",\"tableNumber\":\"30\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"23043.13\",\"barSale\":\"27385\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"50428.13\",\"serviceCharge\":\"4424.83\",\"grandTotal\":\"54852.96\",\"cashRemitted\":\"14290\",\"gcashQrph\":\"3567.24\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"11873.21\",\"mastercard\":\"17964.30\",\"bancnet\":\"2156.79\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"5000\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"54,851.54\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"54,851.54\",\"reconShortOver\":\"1.42\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"341.06\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"341.06\",\"grossSale\":\"55,192.60\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"341.06\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"14,290.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 03:37:27', '2026-09-28 03:37:27'),
(45, 'SR-20260928054013-727', '2026-09-27', 'Lunch', 39, 1, 4, 11, 7542.52, 6918.71, 'draft', '{\"saleDate\":\"2026-09-27\",\"pax\":\"11\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6473.71\",\"barSale\":\"445\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"6918.71\",\"serviceCharge\":\"623.81\",\"grandTotal\":\"7542.52\",\"cashRemitted\":\"6382\",\"gcashQrph\":\"1160.09\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"7,542.09\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"7,542.09\",\"reconShortOver\":\"0.43\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"141.43\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"141.43\",\"grossSale\":\"7,683.52\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"141.43\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"6,382.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 03:40:13', '2026-09-28 03:40:13');
INSERT INTO `daily_reports` (`report_id`, `report_number`, `report_date`, `shift_name`, `week_number`, `cashier_id`, `total_tables`, `total_pax`, `telegram_declared_total`, `pos_sales_total`, `status`, `notes`, `submitted_at`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(46, 'SR-20260928055241-844', '2026-09-27', 'Closing', 39, 1, 10, 37, 27155.34, 24916.12, 'draft', '{\"saleDate\":\"2026-09-27\",\"pax\":\"37\",\"tableNumber\":\"10\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"13596.12\",\"barSale\":\"11320.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"24916.12\",\"serviceCharge\":\"2239.22\",\"grandTotal\":\"27155.34\",\"cashRemitted\":\"6165.00\",\"gcashQrph\":\"2331.08\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"2239.60\",\"mastercard\":\"14959.66\",\"bancnet\":\"1459.64\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"27,154.98\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"27,154.98\",\"reconShortOver\":\"0.36\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"339.93\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"339.93\",\"grossSale\":\"27,494.91\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"339.93\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"6,165.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 03:52:41', '2026-10-06 04:36:01'),
(50, 'SR-20260928092934-220', '2026-09-25', 'Non POS', 39, 1, 1, 0, 1000.00, 0.00, 'draft', '{\"saleDate\":\"2026-09-25\",\"pax\":\"0\",\"tableNumber\":\"1\",\"saleType\":\"\",\"saleChannel\":\"Non POS\",\"kitchenSale\":\"0.00\",\"barSale\":\"\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"1000.00\",\"totalSale\":\"1000.00\",\"serviceCharge\":\"\",\"grandTotal\":\"1000.00\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"0.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"0.00\",\"reconShortOver\":\"1,000.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"0.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 07:29:34', '2026-09-28 08:12:43'),
(51, 'SR-20260928101402-969', '2026-09-26', 'Non POS', 39, 1, 1, 0, 1000.00, 0.00, 'draft', '{\"saleDate\":\"2026-09-26\",\"pax\":\"\",\"tableNumber\":\"\",\"saleType\":\"\",\"saleChannel\":\"Non POS\",\"kitchenSale\":\"0.00\",\"barSale\":\"\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"1000\",\"totalSale\":\"1000.00\",\"serviceCharge\":\"\",\"grandTotal\":\"1000.00\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"0.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"0.00\",\"reconShortOver\":\"1,000.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"0.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 08:14:02', '2026-09-28 08:14:02'),
(52, 'SR-20260928101414-111', '2026-09-27', 'Non POS', 39, 1, 1, 0, 1000.00, 0.00, 'draft', '{\"saleDate\":\"2026-09-27\",\"pax\":\"0\",\"tableNumber\":\"1\",\"saleType\":\"\",\"saleChannel\":\"Non POS\",\"kitchenSale\":\"0.00\",\"barSale\":\"\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"1000.00\",\"totalSale\":\"1000.00\",\"serviceCharge\":\"\",\"grandTotal\":\"1000.00\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"0.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"0.00\",\"reconShortOver\":\"1,000.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"0.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 08:14:14', '2026-09-28 08:14:33'),
(54, 'SR-20260928151243-419', '2026-09-01', 'Lunch', 36, 1, 3, 8, 4384.38, 4025.00, 'draft', '{\"saleDate\":\"2026-09-01\",\"pax\":\"8\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"3835.00\",\"barSale\":\"190\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"4025.00\",\"serviceCharge\":\"359.38\",\"grandTotal\":\"4384.38\",\"cashRemitted\":\"\",\"gcashQrph\":\"833.30\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1987.95\",\"mastercard\":\"1563.13\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"4,384.38\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"4,384.38\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"4,384.38\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 13:12:43', '2026-09-28 13:12:43'),
(55, 'SR-20260928152606-986', '2026-09-02', 'Lunch', 36, 1, 1, 9, 2024.00, 2024.00, 'draft', '{\"saleDate\":\"2026-09-02\",\"pax\":\"9\",\"tableNumber\":\"1\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"1199.00\",\"barSale\":\"825\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"2024.00\",\"serviceCharge\":\"\",\"grandTotal\":\"2024\",\"cashRemitted\":\"1912\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"1,912.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"112.00\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"112.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"2,024.00\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"426.77\",\"specialCustomerDiscount\":\"25\",\"totalSalesDeduction\":\"451.77\",\"grossSale\":\"2,475.77\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"451.77\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"1,912.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"112.00\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 13:26:06', '2026-09-28 13:26:06'),
(56, 'SR-20260928153031-725', '2026-09-03', 'Lunch', 36, 1, 2, 5, 6172.00, 6172.00, 'draft', '{\"saleDate\":\"2026-09-03\",\"pax\":\"5\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"5792.00\",\"barSale\":\"380\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"6172.00\",\"serviceCharge\":\"\",\"grandTotal\":\"6172\",\"cashRemitted\":\"520\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"5652\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"6,172.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"6,172.00\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"1261.60\",\"totalSalesDeduction\":\"1,261.60\",\"grossSale\":\"7,433.60\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"1,261.60\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"520.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 13:30:31', '2026-09-28 13:30:31'),
(57, 'SR-20260928154719-226', '2026-09-04', 'Lunch', 36, 1, 2, 6, 6001.96, 5510.00, 'draft', '{\"saleDate\":\"2026-09-04\",\"pax\":\"6\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"5060.00\",\"barSale\":\"450\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"5510.00\",\"serviceCharge\":\"491.96\",\"grandTotal\":\"6001.96\",\"cashRemitted\":\"2908\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"3093.57\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"6,001.57\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"6,001.57\",\"reconShortOver\":\"0.39\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"357.14\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"357.14\",\"grossSale\":\"6,358.71\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"357.14\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"2,908.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 13:47:19', '2026-09-28 13:47:19'),
(58, 'SR-20260928155107-769', '2026-09-05', 'Lunch', 36, 1, 2, 15, 9350.86, 8580.35, 'draft', '{\"saleDate\":\"2026-09-05\",\"pax\":\"15\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"8360.35\",\"barSale\":\"220\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"8580.35\",\"serviceCharge\":\"770.51\",\"grandTotal\":\"9350.86\",\"cashRemitted\":\"2053\",\"gcashQrph\":\"2335.86\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"4961.70\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"9,350.56\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"9,350.56\",\"reconShortOver\":\"0.30\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"102.90\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"102.90\",\"grossSale\":\"9,453.46\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"102.90\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"1610\",\"onlineTips\":\"1,175.30\",\"cashRemittedShortage\":\"2,053.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-28 13:51:07', '2026-09-28 13:51:07'),
(59, 'SR-20260929040634-626', '2026-09-28', 'Lunch', 40, 1, 1, 7, 4947.54, 4542.00, 'draft', '{\"saleDate\":\"2026-09-28\",\"pax\":\"7\",\"tableNumber\":\"1\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"4542.00\",\"barSale\":\"0\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"4542.00\",\"serviceCharge\":\"405.54\",\"grandTotal\":\"4947.54\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"4947.54\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"4,947.54\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"SIR JOSHUA\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"paid\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherCashNote\":\"\",\"otherMayaTerminal\":\"\",\"otherMayaTerminalNote\":\"\",\"otherBpiNooma\":\"\",\"otherBpiNoomaNote\":\"\",\"otherEastwestNooma\":\"\",\"otherEastwestNoomaNote\":\"\",\"otherGiftCheck\":\"\",\"otherGiftCheckNote\":\"\",\"otherCheques\":\"\",\"otherChequesNote\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"4,947.54\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"400.00\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"400.00\",\"grossSale\":\"5,347.54\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"400.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"unpaidAccountNote_0\":\"paid\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:06:34', '2026-09-30 07:08:26'),
(60, 'SR-20260929041333-345', '2026-09-28', 'Closing', 40, 1, 8, 34, 31103.67, 28730.00, 'draft', '{\"saleDate\":\"2026-09-28\",\"pax\":\"34\",\"tableNumber\":\"8\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"16805.00\",\"barSale\":\"11925.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"28730.00\",\"serviceCharge\":\"2373.67\",\"grandTotal\":\"31103.67\",\"cashRemitted\":\"6159.00\",\"gcashQrph\":\"7423.49\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"18501.52\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"32,084.01\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"980.36\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"980.36\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"32,084.01\",\"reconShortOver\":\"-980.34\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"32,084.01\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"4073.40\",\"grabSaleGross\":\"930.00\",\"onlineTips\":\"678.90\",\"cashRemittedShortage\":\"2,085.60\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:13:33', '2026-10-06 02:03:02'),
(61, 'SR-20260929042553-790', '2026-09-06', 'Lunch', 36, 1, 4, 18, 11064.23, 10148.48, 'draft', '{\"saleDate\":\"2026-09-06\",\"pax\":\"18\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6228.48\",\"barSale\":\"3920\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"10148.48\",\"serviceCharge\":\"915.75\",\"grandTotal\":\"11064.23\",\"cashRemitted\":\"4992\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1524.98\",\"mastercard\":\"4547.05\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"11,064.03\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"11,064.03\",\"reconShortOver\":\"0.20\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"414\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"414.00\",\"grossSale\":\"11,478.03\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"414.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"4,992.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:25:53', '2026-09-29 02:25:53'),
(62, 'SR-20260929042759-859', '2026-09-07', 'Lunch', 37, 1, 3, 14, 3789.21, 3616.00, 'draft', '{\"saleDate\":\"2026-09-07\",\"pax\":\"14\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"2806.00\",\"barSale\":\"810\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"3616.00\",\"serviceCharge\":\"173.21\",\"grandTotal\":\"3789.21\",\"cashRemitted\":\"3789\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"3,789.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"3,789.00\",\"reconShortOver\":\"0.21\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"374.11\",\"totalSalesDeduction\":\"374.11\",\"grossSale\":\"4,163.11\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"374.11\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"880\",\"onlineTips\":\"642.40\",\"cashRemittedShortage\":\"3,789.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:27:59', '2026-09-29 02:27:59'),
(63, 'SR-20260929043403-381', '2026-09-08', 'Lunch', 37, 1, 2, 4, 1820.63, 1667.14, 'draft', '{\"saleDate\":\"2026-09-08\",\"pax\":\"4\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"717.14\",\"barSale\":\"950\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"1667.14\",\"serviceCharge\":\"153.49\",\"grandTotal\":\"1820.63\",\"cashRemitted\":\"686\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1134.38\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"1,820.38\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"1,820.38\",\"reconShortOver\":\"0.25\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"108.04\",\"specialCustomerDiscount\":\"374.11\",\"totalSalesDeduction\":\"482.15\",\"grossSale\":\"2,302.53\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"482.15\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"686.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:34:03', '2026-09-29 02:34:03'),
(64, 'SR-20260929043626-806', '2026-09-09', 'Lunch', 37, 1, 2, 10, 2340.00, 2340.00, 'draft', '{\"saleDate\":\"2026-09-09\",\"pax\":\"10\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"1830.00\",\"barSale\":\"510\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"2340.00\",\"serviceCharge\":\"\",\"grandTotal\":\"2340\",\"cashRemitted\":\"644\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1696\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"2,340.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"2,340.00\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"522.31\",\"totalSalesDeduction\":\"522.31\",\"grossSale\":\"2,862.31\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"522.31\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"644.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:36:26', '2026-09-29 02:36:26'),
(65, 'SR-20260929044041-950', '2026-09-10', 'Lunch', 37, 1, 4, 12, 8123.63, 7451.90, 'draft', '{\"saleDate\":\"2026-09-10\",\"pax\":\"12\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6521.90\",\"barSale\":\"930\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"7451.90\",\"serviceCharge\":\"671.73\",\"grandTotal\":\"8123.63\",\"cashRemitted\":\"2532\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"3359.04\",\"mastercard\":\"2232\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"8,123.04\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"8,123.04\",\"reconShortOver\":\"0.59\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"142.82\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"142.82\",\"grossSale\":\"8,265.86\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"142.82\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"2,532.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:40:41', '2026-09-29 02:40:41'),
(66, 'SR-20260929044457-445', '2026-09-11', 'Lunch', 37, 1, 5, 16, 8132.77, 7456.97, 'draft', '{\"saleDate\":\"2026-09-11\",\"pax\":\"16\",\"tableNumber\":\"5\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"5931.97\",\"barSale\":\"1525\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"7456.97\",\"serviceCharge\":\"675.80\",\"grandTotal\":\"8132.77\",\"cashRemitted\":\"2657\",\"gcashQrph\":\"479.29\",\"paymaya\":\"\",\"amex\":\"2396.43\",\"visa\":\"2599.56\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"8,132.28\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"8,132.28\",\"reconShortOver\":\"0.49\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"233.13\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"233.13\",\"grossSale\":\"8,365.41\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"233.13\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"2,657.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:44:57', '2026-09-29 02:44:57'),
(67, 'SR-20260929044834-470', '2026-09-12', 'Lunch', 37, 1, 2, 5, 4128.39, 3790.00, 'draft', '{\"saleDate\":\"2026-09-12\",\"pax\":\"5\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"3170.00\",\"barSale\":\"620\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"3790.00\",\"serviceCharge\":\"338.39\",\"grandTotal\":\"4128.39\",\"cashRemitted\":\"4128\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"4,128.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"4,128.00\",\"reconShortOver\":\"0.39\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"4,128.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"4,128.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:48:34', '2026-09-29 02:48:34'),
(68, 'SR-20260929045150-550', '2026-09-13', 'Lunch', 37, 1, 2, 4, 2227.11, 2121.44, 'draft', '{\"saleDate\":\"2026-09-13\",\"pax\":\"4\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"1931.44\",\"barSale\":\"190\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"2121.44\",\"serviceCharge\":\"105.67\",\"grandTotal\":\"2227.11\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1232.82\",\"mastercard\":\"994.28\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"2,227.10\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"2,227.10\",\"reconShortOver\":\"0.01\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"220.97\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"220.97\",\"grossSale\":\"2,448.07\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"220.97\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:51:50', '2026-09-29 02:51:50'),
(69, 'SR-20260929045800-810', '2026-09-01', 'Closing', 36, 1, 7, 26, 15525.02, 14253.22, 'draft', '{\"saleDate\":\"2026-09-01\",\"pax\":\"26\",\"tableNumber\":\"7\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"12113.22\",\"barSale\":\"2140\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"14253.22\",\"serviceCharge\":\"1271.80\",\"grandTotal\":\"15525.02\",\"cashRemitted\":\"9406\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"6118.81\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"15,524.81\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"15,524.81\",\"reconShortOver\":\"0.21\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"398\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"398.00\",\"grossSale\":\"15,922.81\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"398.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"9,406.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 02:58:00', '2026-09-29 02:58:00'),
(70, 'SR-20260929050116-853', '2026-09-02', 'Closing', 36, 1, 10, 34, 36854.90, 33834.00, 'draft', '{\"saleDate\":\"2026-09-02\",\"pax\":\"34\",\"tableNumber\":\"10\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"14389.00\",\"barSale\":\"19445\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"33834.00\",\"serviceCharge\":\"3020.90\",\"grandTotal\":\"36854.90\",\"cashRemitted\":\"9023\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1323.48\",\"mastercard\":\"23572.15\",\"bancnet\":\"2935.63\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"36,854.26\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"36,854.26\",\"reconShortOver\":\"0.64\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"14.28\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"14.28\",\"grossSale\":\"36,868.54\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"14.28\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"9,023.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:01:16', '2026-09-29 03:01:16'),
(71, 'SR-20260929050728-671', '2026-09-03', 'Closing', 36, 1, 10, 34, 25325.16, 23365.94, 'draft', '{\"saleDate\":\"2026-09-03\",\"pax\":\"34\",\"tableNumber\":\"10\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"16115.94\",\"barSale\":\"7250\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"23365.94\",\"serviceCharge\":\"1959.22\",\"grandTotal\":\"25325.16\",\"cashRemitted\":\"8368\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1557.68\",\"mastercard\":\"15399.23\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"25,324.91\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"25,324.91\",\"reconShortOver\":\"0.25\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"1108.79\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"1,108.79\",\"grossSale\":\"26,433.70\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"1,108.79\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"540\",\"onlineTips\":\"394.20\",\"cashRemittedShortage\":\"8,368.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:07:28', '2026-09-29 03:07:28'),
(72, 'SR-20260929051329-169', '2026-09-04', 'Closing', 36, 1, 14, 51, 53376.90, 48983.24, 'draft', '{\"saleDate\":\"2026-09-04\",\"pax\":\"51\",\"tableNumber\":\"14\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"24303.24\",\"barSale\":\"23680\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"1000\",\"totalSale\":\"48983.24\",\"serviceCharge\":\"4393.66\",\"grandTotal\":\"53376.90\",\"cashRemitted\":\"26310\",\"gcashQrph\":\"1383.39\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"23433.78\",\"mastercard\":\"\",\"bancnet\":\"2249.38\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"53,376.55\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"53,376.55\",\"reconShortOver\":\"0.35\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"469.86\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"469.86\",\"grossSale\":\"53,846.41\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"469.86\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"1220\",\"onlineTips\":\"890.60\",\"cashRemittedShortage\":\"26,310.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:13:29', '2026-09-29 03:13:29'),
(73, 'SR-20260929052204-418', '2026-09-05', 'Closing', 36, 1, 36, 133, 78842.19, 72376.45, 'draft', '{\"saleDate\":\"2026-09-05\",\"pax\":\"133\",\"tableNumber\":\"36\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"39526.45\",\"barSale\":\"29850\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"3000\",\"totalSale\":\"72376.45\",\"serviceCharge\":\"6465.74\",\"grandTotal\":\"78842.19\",\"cashRemitted\":\"15628\",\"gcashQrph\":\"6948.13\",\"paymaya\":\"\",\"amex\":\"4119.44\",\"visa\":\"14333.26\",\"mastercard\":\"37219.78\",\"bancnet\":\"566.43\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"78,815.04\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"78,815.04\",\"reconShortOver\":\"27.15\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"499.08\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"499.08\",\"grossSale\":\"79,314.12\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"499.08\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"15,628.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:22:04', '2026-09-29 03:22:04'),
(74, 'SR-20260929053141-461', '2026-09-06', 'Closing', 36, 1, 13, 51, 34361.67, 31535.46, 'draft', '{\"saleDate\":\"2026-09-06\",\"pax\":\"51\",\"tableNumber\":\"13\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"24935.46\",\"barSale\":\"6600\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"31535.46\",\"serviceCharge\":\"2826.21\",\"grandTotal\":\"34361.67\",\"cashRemitted\":\"18964\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1106.25\",\"mastercard\":\"10211.59\",\"bancnet\":\"4079.37\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"34,361.21\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"34,361.21\",\"reconShortOver\":\"0.46\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"246.60\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"246.60\",\"grossSale\":\"34,607.81\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"246.60\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"1010\",\"onlineTips\":\"737.30\",\"cashRemittedShortage\":\"18,964.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:31:41', '2026-09-29 03:31:41');
INSERT INTO `daily_reports` (`report_id`, `report_number`, `report_date`, `shift_name`, `week_number`, `cashier_id`, `total_tables`, `total_pax`, `telegram_declared_total`, `pos_sales_total`, `status`, `notes`, `submitted_at`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(75, 'SR-20260929053922-295', '2026-09-07', 'Closing', 37, 1, 11, 29, 21217.91, 19695.82, 'draft', '{\"saleDate\":\"2026-09-07\",\"pax\":\"29\",\"tableNumber\":\"11\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"10080.82\",\"barSale\":\"9615\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"19695.82\",\"serviceCharge\":\"1522.09\",\"grandTotal\":\"21217.91\",\"cashRemitted\":\"6633\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"1230.89\",\"visa\":\"7308.57\",\"mastercard\":\"3528.45\",\"bancnet\":\"2516.25\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"21,217.16\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"21,217.16\",\"reconShortOver\":\"0.75\",\"reconRefund\":\"460.20\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"11.25\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"11.25\",\"grossSale\":\"21,228.41\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"11.25\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"6,633.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:39:22', '2026-09-29 03:39:22'),
(76, 'SR-20260929054358-474', '2026-09-08', 'Closing', 37, 1, 17, 27, 19151.50, 17639.48, 'draft', '{\"saleDate\":\"2026-09-08\",\"pax\":\"27\",\"tableNumber\":\"17\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"10364.48\",\"barSale\":\"7275\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"17639.48\",\"serviceCharge\":\"1512.02\",\"grandTotal\":\"19151.50\",\"cashRemitted\":\"4456\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"8783.64\",\"mastercard\":\"5421.26\",\"bancnet\":\"495.63\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"19,156.53\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"19,156.53\",\"reconShortOver\":\"-5.03\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"281.57\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"281.57\",\"grossSale\":\"19,438.10\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"281.57\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"540\",\"onlineTips\":\"394.20\",\"cashRemittedShortage\":\"4,456.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:43:58', '2026-09-29 03:43:58'),
(77, 'SR-20260929054745-788', '2026-09-09', 'Closing', 37, 1, 4, 27, 18194.55, 16690.59, 'draft', '{\"saleDate\":\"2026-09-09\",\"pax\":\"27\",\"tableNumber\":\"4\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"9815.59\",\"barSale\":\"6875\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"16690.59\",\"serviceCharge\":\"1503.96\",\"grandTotal\":\"18194.55\",\"cashRemitted\":\"5802\",\"gcashQrph\":\"1599.07\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"4792.86\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"6000\",\"cheque\":\"\",\"totalPayment\":\"18,193.93\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"18,193.93\",\"reconShortOver\":\"0.62\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"366.69\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"366.69\",\"grossSale\":\"18,560.62\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"366.69\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"5,802.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:47:45', '2026-09-29 03:47:45'),
(78, 'SR-20260929055148-977', '2026-09-10', 'Closing', 37, 1, 5, 24, 23870.16, 21911.28, 'draft', '{\"saleDate\":\"2026-09-10\",\"pax\":\"24\",\"tableNumber\":\"5\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"11281.28\",\"barSale\":\"10630\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"21911.28\",\"serviceCharge\":\"1958.88\",\"grandTotal\":\"23870.16\",\"cashRemitted\":\"3197\",\"gcashQrph\":\"7374.47\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"11611.79\",\"bancnet\":\"\",\"jcb\":\"1686.86\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"23,870.12\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"23,870.12\",\"reconShortOver\":\"0.04\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"58.58\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"58.58\",\"grossSale\":\"23,928.70\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"58.58\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"3,197.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:51:48', '2026-09-29 03:51:48'),
(79, 'SR-20260929055524-299', '2026-09-11', 'Closing', 37, 1, 11, 33, 26256.28, 24097.05, 'draft', '{\"saleDate\":\"2026-09-11\",\"pax\":\"33\",\"tableNumber\":\"11\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"13382.05\",\"barSale\":\"10715\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"24097.05\",\"serviceCharge\":\"2159.23\",\"grandTotal\":\"26256.28\",\"cashRemitted\":\"3883\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"12630.26\",\"mastercard\":\"5548.96\",\"bancnet\":\"4193.75\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"26,255.97\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"26,255.97\",\"reconShortOver\":\"0.31\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"179.96\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"179.96\",\"grossSale\":\"26,435.93\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"179.96\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"3,883.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:55:24', '2026-09-29 03:55:24'),
(80, 'SR-20260929055926-423', '2026-09-12', 'Closing', 37, 1, 35, 106, 95850.19, 88405.16, 'draft', '{\"saleDate\":\"2026-09-12\",\"pax\":\"106\",\"tableNumber\":\"35\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"35893.16\",\"barSale\":\"52512\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"88405.16\",\"serviceCharge\":\"7445.03\",\"grandTotal\":\"95850.19\",\"cashRemitted\":\"19838\",\"gcashQrph\":\"10055.18\",\"paymaya\":\"\",\"amex\":\"3137.14\",\"visa\":\"20059.62\",\"mastercard\":\"33438.41\",\"bancnet\":\"9351.53\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"95,879.88\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"95,879.88\",\"reconShortOver\":\"-29.69\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"2032.76\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"2,032.76\",\"grossSale\":\"97,912.64\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"2,032.76\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"19,838.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 03:59:26', '2026-09-29 03:59:26'),
(81, 'SR-20260929060519-390', '2026-09-13', 'Closing', 37, 1, 11, 35, 30712.23, 28188.15, 'draft', '{\"saleDate\":\"2026-09-13\",\"pax\":\"35\",\"tableNumber\":\"11\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"22168.15\",\"barSale\":\"6020\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"28188.15\",\"serviceCharge\":\"2524.08\",\"grandTotal\":\"30712.23\",\"cashRemitted\":\"4482\",\"gcashQrph\":\"3436.70\",\"paymaya\":\"\",\"amex\":\"3039.11\",\"visa\":\"12597.59\",\"mastercard\":\"7156.42\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"30,711.82\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"27\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"27.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"30,711.82\",\"reconShortOver\":\"0.41\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"129.92\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"129.92\",\"grossSale\":\"30,841.74\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"129.92\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"4,482.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-29 04:05:19', '2026-09-29 04:05:19'),
(84, 'SR-20260930035905-516', '2026-09-29', 'Lunch', 40, 1, 1, 7, 4700.27, 4315.00, 'draft', '{\"saleDate\":\"2026-09-29\",\"pax\":\"7\",\"tableNumber\":\"1\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"4030.00\",\"barSale\":\"285.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"4315.00\",\"serviceCharge\":\"385.27\",\"grandTotal\":\"4700.27\",\"cashRemitted\":\"\",\"gcashQrph\":\"4700.27\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"4,700.27\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"4,700.27\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"4,700.27\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-30 01:59:05', '2026-10-01 09:21:28'),
(85, 'SR-20260930040137-436', '2026-09-29', 'Closing', 40, 1, 9, 49, 45997.29, 42169.21, 'draft', '{\"saleDate\":\"2026-09-29\",\"pax\":\"49\",\"tableNumber\":\"9\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"27369.21\",\"barSale\":\"14800.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"42169.21\",\"serviceCharge\":\"3828.08\",\"grandTotal\":\"45997.29\",\"cashRemitted\":\"20406.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"11757.48\",\"bancnet\":\"6229.93\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"38,393.41\",\"owner_4\":\"\",\"owner_1\":\"7603.21\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"7,603.21\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"45,996.62\",\"reconShortOver\":\"0.67\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"1469.25\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"1,469.25\",\"grossSale\":\"47,465.87\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"1,469.25\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"2861.60\",\"grabSaleGross\":\"1960.00\",\"onlineTips\":\"1,430.80\",\"cashRemittedShortage\":\"17,544.40\",\"ownerAccounts\":{\"1\":\"7603.21\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-09-30 02:01:37', '2026-10-06 00:58:49'),
(86, 'SR-20261001035317-933', '2026-09-30', 'Lunch', 40, 1, 2, 6, 3038.82, 2954.00, 'draft', '{\"saleDate\":\"2026-09-30\",\"pax\":\"6\",\"tableNumber\":\"2\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"2379.00\",\"barSale\":\"575\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"2954.00\",\"serviceCharge\":\"84.82\",\"grandTotal\":\"3038.82\",\"cashRemitted\":\"2004\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"1034.82\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"3,038.82\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherCashNote\":\"\",\"otherMayaTerminal\":\"\",\"otherMayaTerminalNote\":\"\",\"otherBpiNooma\":\"\",\"otherBpiNoomaNote\":\"\",\"otherEastwestNooma\":\"\",\"otherEastwestNoomaNote\":\"\",\"otherGiftCheck\":\"\",\"otherGiftCheckNote\":\"\",\"otherCheques\":\"\",\"otherChequesNote\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"3,038.82\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"447.32\",\"totalSalesDeduction\":\"447.32\",\"grossSale\":\"3,486.14\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"447.32\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"2,004.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-01 01:53:17', '2026-10-01 01:53:17'),
(87, 'SR-20261001040107-865', '2026-09-30', 'Closing', 40, 1, 32, 80, 55701.18, 51125.70, 'draft', '{\"saleDate\":\"2026-09-30\",\"pax\":\"80\",\"tableNumber\":\"32\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"31250.70\",\"barSale\":\"19875.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"51125.70\",\"serviceCharge\":\"4575.48\",\"grandTotal\":\"55701.18\",\"cashRemitted\":\"22910.00\",\"gcashQrph\":\"675.35\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"12428.75\",\"mastercard\":\"9370.65\",\"bancnet\":\"9335.18\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"54,719.93\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"-980.34\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"-980.34\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"54,719.93\",\"reconShortOver\":\"981.25\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"294.22\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"294.22\",\"grossSale\":\"55,014.15\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"294.22\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"496.40\",\"grabSaleGross\":\"340.00\",\"onlineTips\":\"248.20\",\"cashRemittedShortage\":\"22,413.60\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-01 02:01:07', '2026-10-06 01:52:33'),
(88, 'SR-20261002034854-930', '2026-10-01', 'Lunch', 40, 1, 1, 1, 280.00, 280.00, 'draft', '{\"saleDate\":\"2026-10-01\",\"pax\":\"1\",\"tableNumber\":\"1\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"280.00\",\"barSale\":\"0\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"280.00\",\"serviceCharge\":\"0\",\"grandTotal\":\"280.00\",\"cashRemitted\":\"280.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"280.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"280.00\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"280.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"280.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-02 01:48:54', '2026-10-02 05:37:51'),
(89, 'SR-20261002035144-190', '2026-10-01', 'Closing', 40, 1, 14, 44, 31081.01, 28634.05, 'draft', '{\"saleDate\":\"2026-10-01\",\"pax\":\"44\",\"tableNumber\":\"14\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"16759.05\",\"barSale\":\"11875.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"28634.05\",\"serviceCharge\":\"2446.96\",\"grandTotal\":\"31081.01\",\"cashRemitted\":\"10181.00\",\"gcashQrph\":\"544.64\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"14376.13\",\"mastercard\":\"1742.86\",\"bancnet\":\"4235.76\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"31,080.39\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"31,080.39\",\"reconShortOver\":\"0.62\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"566.24\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"566.24\",\"grossSale\":\"31,646.63\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"566.24\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"10,181.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-02 01:51:44', '2026-10-02 01:52:07'),
(92, 'SR-20261005034717-390', '2026-10-02', 'Lunch', 40, 1, 5, 16, 7519.61, 6917.29, 'draft', '{\"saleDate\":\"2026-10-02\",\"pax\":\"16\",\"tableNumber\":\"5\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6312.29\",\"barSale\":\"605\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"6917.29\",\"serviceCharge\":\"602.32\",\"grandTotal\":\"7519.61\",\"cashRemitted\":\"3026\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"2832.14\",\"mastercard\":\"1661.16\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"7,519.30\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"7,519.30\",\"reconShortOver\":\"0.31\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"275.89\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"275.89\",\"grossSale\":\"7,795.19\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"275.89\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"3,026.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 01:47:17', '2026-10-05 01:47:17'),
(93, 'SR-20261005034854-523', '2026-10-03', 'Lunch', 40, 1, 1, 1, 174.29, 160.00, 'draft', '{\"saleDate\":\"2026-10-03\",\"pax\":\"1\",\"tableNumber\":\"1\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"-0.00\",\"barSale\":\"160\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"160.00\",\"serviceCharge\":\"14.29\",\"grandTotal\":\"174.29\",\"cashRemitted\":\"174\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"174.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"174.00\",\"reconShortOver\":\"0.29\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"174.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"174.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 01:48:54', '2026-10-05 01:48:54'),
(94, 'SR-20261005035054-574', '2026-10-04', 'Lunch', 40, 1, 3, 10, 6852.36, 6277.60, 'draft', '{\"saleDate\":\"2026-10-04\",\"pax\":\"10\",\"tableNumber\":\"3\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6208.60\",\"barSale\":\"69.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"6277.60\",\"serviceCharge\":\"574.76\",\"grandTotal\":\"6852.36\",\"cashRemitted\":\"2559.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"4293.00\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"6,852.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"6,852.00\",\"reconShortOver\":\"0.36\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"332.73\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"332.73\",\"grossSale\":\"7,184.73\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"332.73\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"2,559.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 01:50:54', '2026-10-06 02:05:39'),
(95, 'SR-20261005035817-292', '2026-10-02', 'Closing', 40, 1, 23, 78, 50009.02, 45942.16, 'draft', '{\"saleDate\":\"2026-10-02\",\"pax\":\"78\",\"tableNumber\":\"23\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"25387.16\",\"barSale\":\"20555.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"45942.16\",\"serviceCharge\":\"4066.86\",\"grandTotal\":\"50009.02\",\"cashRemitted\":\"15185.00\",\"gcashQrph\":\"2140.44\",\"paymaya\":\"\",\"amex\":\"8970.27\",\"visa\":\"3398.57\",\"mastercard\":\"9050.92\",\"bancnet\":\"7813.76\",\"jcb\":\"2861\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"49,419.96\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"588.00\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"588.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"50,007.96\",\"reconShortOver\":\"1.06\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"641.17\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"641.17\",\"grossSale\":\"50,649.13\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"641.17\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"890.60\",\"grabSaleGross\":\"1220.00\",\"onlineTips\":\"890.60\",\"cashRemittedShortage\":\"14,294.40\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"588.00\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 01:58:17', '2026-10-05 03:09:14'),
(96, 'SR-20261005040215-331', '2026-10-03', 'Closing', 40, 1, 23, 96, 63927.85, 58671.16, 'draft', '{\"saleDate\":\"2026-10-03\",\"pax\":\"96\",\"tableNumber\":\"23\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"27396.16\",\"barSale\":\"31275.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"58671.16\",\"serviceCharge\":\"5256.69\",\"grandTotal\":\"63927.85\",\"cashRemitted\":\"35497.00\",\"gcashQrph\":\"1868.13\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"7701.26\",\"mastercard\":\"19514.55\",\"bancnet\":\"4651.25\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"69,232.19\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"MIGUEL RIVERO\",\"unpaidAccountAmount\":\"-5304.82\",\"unpaidAccountNote\":\"paid (TRANS VIA MASTERCARD)\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"-5,304.82\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"69,232.19\",\"reconShortOver\":\"0.48\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"424.30\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"424.30\",\"grossSale\":\"69,656.49\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"424.30\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"897.90\",\"grabSaleGross\":\"615.00\",\"onlineTips\":\"448.95\",\"cashRemittedShortage\":\"34,599.10\",\"unpaidAccountNote_0\":\"paid (TRANS VIA MASTERCARD)\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 02:02:15', '2026-10-05 03:13:28'),
(97, 'SR-20261005040813-842', '2026-10-04', 'Closing', 40, 1, 7, 18, 14865.36, 13644.28, 'draft', '{\"saleDate\":\"2026-10-04\",\"pax\":\"18\",\"tableNumber\":\"7\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"11624.28\",\"barSale\":\"2020.00\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"13644.28\",\"serviceCharge\":\"1221.08\",\"grandTotal\":\"14865.36\",\"cashRemitted\":\"11701.00\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"2619.74\",\"bancnet\":\"544.64\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"14,865.38\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"14,865.38\",\"reconShortOver\":\"-0.02\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"66.07\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"66.07\",\"grossSale\":\"14,931.45\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"66.07\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"1065.80\",\"grabSaleGross\":\"1460.00\",\"onlineTips\":\"1,065.80\",\"cashRemittedShortage\":\"10,635.20\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 02:08:13', '2026-10-06 02:04:50'),
(98, 'SR-20261005045432-689', '2026-10-03', 'Non POS', 40, 1, 1, 0, 1000.00, 0.00, 'draft', '{\"saleDate\":\"2026-10-03\",\"pax\":\"\",\"tableNumber\":\"\",\"saleType\":\"\",\"saleChannel\":\"Non POS\",\"kitchenSale\":\"0.00\",\"barSale\":\"\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"1000\",\"totalSale\":\"1000.00\",\"serviceCharge\":\"\",\"grandTotal\":\"1000.00\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"0.00\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"0.00\",\"reconShortOver\":\"1,000.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"0.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-05 02:54:32', '2026-10-05 02:54:32'),
(99, 'SR-20261006041757-386', '2026-10-05', 'Lunch', 41, 1, 4, 12, 8187.51, 7510.01, 'draft', '{\"saleDate\":\"2026-10-05\",\"pax\":\"12\",\"tableNumber\":\"4\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"6200.01\",\"barSale\":\"1310\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"7510.01\",\"serviceCharge\":\"677.50\",\"grandTotal\":\"8187.51\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"2075.09\",\"mastercard\":\"2697.51\",\"bancnet\":\"3414.91\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"8,187.51\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"8,187.51\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"162.49\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"162.49\",\"grossSale\":\"8,350.00\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"162.49\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"1890\",\"onlineTips\":\"1,379.70\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-06 02:17:57', '2026-10-06 02:17:57'),
(100, 'SR-20261006042046-600', '2026-10-05', 'Closing', 41, 1, 10, 31, 23551.22, 21728.71, 'draft', '{\"saleDate\":\"2026-10-05\",\"pax\":\"31\",\"tableNumber\":\"10\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"14493.71\",\"barSale\":\"7235\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"21728.71\",\"serviceCharge\":\"1822.51\",\"grandTotal\":\"23551.22\",\"cashRemitted\":\"6079\",\"gcashQrph\":\"2277.14\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"5898.49\",\"mastercard\":\"6431.61\",\"bancnet\":\"2864.82\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"23,551.06\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"23,551.06\",\"reconShortOver\":\"0.16\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"288.29\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"288.29\",\"grossSale\":\"23,839.35\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"288.29\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"6,079.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-06 02:20:46', '2026-10-06 02:20:46'),
(101, 'SR-20261007033238-214', '2026-10-06', 'Lunch', 41, 1, 1, 2, 1742.86, 1600.00, 'draft', '{\"saleDate\":\"2026-10-06\",\"pax\":\"2\",\"tableNumber\":\"1\",\"saleType\":\"Lunch\",\"saleChannel\":\"POS\",\"kitchenSale\":\"1600.00\",\"barSale\":\"0\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"1600.00\",\"serviceCharge\":\"142.86\",\"grandTotal\":\"1742.86\",\"cashRemitted\":\"\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"1742.86\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"1,742.86\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"1,742.86\",\"reconShortOver\":\"0.00\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"0.00\",\"grossSale\":\"1,742.86\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"0.00\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"0.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-07 01:32:38', '2026-10-07 01:32:38'),
(102, 'SR-20261007033437-958', '2026-10-06', 'Closing', 41, 1, 4, 20, 21031.10, 19299.86, 'draft', '{\"saleDate\":\"2026-10-06\",\"pax\":\"20\",\"tableNumber\":\"4\",\"saleType\":\"Closing\",\"saleChannel\":\"POS\",\"kitchenSale\":\"16474.86\",\"barSale\":\"2825\",\"giftCheckSale\":\"\",\"otherProducts\":\"\",\"corkage\":\"\",\"totalSale\":\"19299.86\",\"serviceCharge\":\"1731.24\",\"grandTotal\":\"21031.10\",\"cashRemitted\":\"9052\",\"gcashQrph\":\"\",\"paymaya\":\"\",\"amex\":\"\",\"visa\":\"11979.14\",\"mastercard\":\"\",\"bancnet\":\"\",\"jcb\":\"\",\"bpi\":\"\",\"easwest\":\"\",\"giftcheck\":\"\",\"cheque\":\"\",\"totalPayment\":\"21,031.14\",\"owner_4\":\"\",\"owner_1\":\"\",\"owner_3\":\"\",\"owner_2\":\"\",\"owner_5\":\"\",\"unpaidAccountName\":\"\",\"unpaidAccountAmount\":\"\",\"unpaidAccountNote\":\"\",\"totalOwnerAccounts\":\"0.00\",\"otherCash\":\"\",\"otherMayaTerminal\":\"\",\"otherBpiNooma\":\"\",\"otherEastwestNooma\":\"\",\"otherGiftCheck\":\"\",\"otherCheques\":\"\",\"totalOtherPayments\":\"0.00\",\"marketingExpensesF\":\"\",\"djRonald\":\"\",\"ejVelez\":\"\",\"guestDJ\":\"\",\"marketingOthers\":\"\",\"totalMarketingExpenses\":\"0.00\",\"unpaidAccounts\":\"0.00\",\"ownersDiscount\":\"\",\"paidAccounts\":\"\",\"totalSalesBasedOnPayment\":\"21,031.14\",\"reconShortOver\":\"-0.04\",\"reconRefund\":\"\",\"pwdDiscount\":\"\",\"seniorCitizenDiscount\":\"187.59\",\"specialCustomerDiscount\":\"\",\"totalSalesDeduction\":\"187.59\",\"grossSale\":\"21,218.73\",\"marketingOwnersDiscount\":\"0.00\",\"foodBeverageSummary\":\"0.00\",\"djsTalentFee\":\"\",\"bouncersFee\":\"\",\"others\":\"\",\"totalDeductionsAndExpenses\":\"187.59\",\"totalExpenses\":\"0.00\",\"totalNetSale\":\"0.00\",\"cashSales\":\"\",\"grabSaleGross\":\"\",\"onlineTips\":\"0.00\",\"cashRemittedShortage\":\"9,052.00\",\"ownerAccounts\":{\"1\":\"\",\"2\":\"\",\"3\":\"\",\"4\":\"\",\"5\":\"\"}}', NULL, NULL, NULL, '2026-10-07 01:34:37', '2026-10-07 01:34:37');

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
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL,
  `item_description` varchar(255) NOT NULL,
  `quantity` decimal(10,2) DEFAULT 0.00,
  `unit` varchar(50) NOT NULL,
  `beginning` decimal(10,2) DEFAULT 0.00,
  `purchases` decimal(10,2) DEFAULT 0.00,
  `sold_used` decimal(10,2) DEFAULT 0.00,
  `ending` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(12, 'Cheque Payment', 'cheque', 1, 12, '2026-09-21 13:01:11'),
(13, 'Other Cash', 'cash', 1, 13, '2026-09-28 06:35:09'),
(14, 'Card (Maya Terminal)', 'card', 1, 14, '2026-09-28 06:35:09'),
(15, 'Direct BT BPI Nooma (Other)', 'bank_transfer', 1, 15, '2026-09-28 06:35:09'),
(16, 'Direct BT EastWest Nooma (Other)', 'bank_transfer', 1, 16, '2026-09-28 06:35:09'),
(17, 'Gift Check (Other)', 'gift_check', 1, 17, '2026-09-28 06:35:09'),
(18, 'Cheques (Other)', 'cheque', 1, 18, '2026-09-28 06:35:09');

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

--
-- Dumping data for table `report_accounts`
--

INSERT INTO `report_accounts` (`report_account_id`, `report_id`, `account_holder_id`, `account_category`, `transaction_type`, `amount`, `payment_method_id`, `reference_number`, `remarks`, `created_at`) VALUES
(37, 7, 1, 'owners_account', 'charge', 1900.00, NULL, NULL, NULL, '2026-09-23 10:05:03'),
(79, 55, 2, 'owners_account', 'charge', 112.00, NULL, NULL, NULL, '2026-09-28 13:26:06'),
(102, 95, 2, 'owners_account', 'charge', 588.00, NULL, NULL, NULL, '2026-10-05 03:09:14'),
(103, 85, 1, 'owners_account', 'charge', 7603.21, NULL, NULL, NULL, '2026-10-06 00:58:49'),
(104, 42, 1, 'owners_account', 'charge', 8698.60, NULL, NULL, NULL, '2026-10-06 04:32:04');

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

--
-- Dumping data for table `report_deductions`
--

INSERT INTO `report_deductions` (`deduction_id`, `report_id`, `discount_type_id`, `deduction_type`, `amount`, `reference_number`, `remarks`, `created_at`) VALUES
(19, 5, 2, 'discount', 222.65, NULL, NULL, '2026-09-23 04:23:06'),
(20, 4, 2, 'discount', 228.58, NULL, NULL, '2026-09-23 04:24:58'),
(22, 8, 2, 'discount', 229.07, NULL, NULL, '2026-09-23 05:12:41'),
(23, 9, 2, 'discount', 294.66, NULL, NULL, '2026-09-23 05:16:53'),
(28, 15, 2, 'discount', 478.76, NULL, NULL, '2026-09-23 08:21:48'),
(29, 16, 2, 'discount', 760.31, NULL, NULL, '2026-09-23 08:28:59'),
(30, 17, 2, 'discount', 654.55, NULL, NULL, '2026-09-23 08:45:20'),
(34, 7, 3, 'discount', 424.11, NULL, NULL, '2026-09-23 10:05:03'),
(44, 18, 2, 'discount', 529.47, NULL, NULL, '2026-09-24 03:34:36'),
(45, 18, 3, 'discount', 413.38, NULL, NULL, '2026-09-24 03:34:36'),
(46, 19, 2, 'discount', 139.73, NULL, NULL, '2026-09-24 03:37:22'),
(52, 20, 2, 'discount', 700.61, NULL, NULL, '2026-09-24 08:30:32'),
(62, 30, 3, 'discount', 439.30, NULL, NULL, '2026-09-25 01:50:48'),
(66, 29, 2, 'discount', 113.54, NULL, NULL, '2026-09-25 02:21:25'),
(67, 28, 3, 'discount', 366.07, NULL, NULL, '2026-09-25 02:22:27'),
(76, 31, 2, 'discount', 244.16, NULL, NULL, '2026-09-25 07:54:52'),
(79, 44, 2, 'discount', 341.06, NULL, NULL, '2026-09-28 03:37:27'),
(80, 45, 2, 'discount', 141.43, NULL, NULL, '2026-09-28 03:40:13'),
(82, 41, 2, 'discount', 390.73, NULL, NULL, '2026-09-28 05:35:45'),
(89, 55, 2, 'discount', 426.77, NULL, NULL, '2026-09-28 13:26:06'),
(90, 55, 3, 'discount', 25.00, NULL, NULL, '2026-09-28 13:26:06'),
(91, 56, 3, 'discount', 1261.60, NULL, NULL, '2026-09-28 13:30:31'),
(92, 57, 2, 'discount', 357.14, NULL, NULL, '2026-09-28 13:47:19'),
(93, 58, 2, 'discount', 102.90, NULL, NULL, '2026-09-28 13:51:07'),
(95, 61, 2, 'discount', 414.00, NULL, NULL, '2026-09-29 02:25:53'),
(96, 62, 3, 'discount', 374.11, NULL, NULL, '2026-09-29 02:27:59'),
(97, 63, 2, 'discount', 108.04, NULL, NULL, '2026-09-29 02:34:03'),
(98, 63, 3, 'discount', 374.11, NULL, NULL, '2026-09-29 02:34:03'),
(99, 64, 3, 'discount', 522.31, NULL, NULL, '2026-09-29 02:36:26'),
(100, 65, 2, 'discount', 142.82, NULL, NULL, '2026-09-29 02:40:41'),
(101, 66, 2, 'discount', 233.13, NULL, NULL, '2026-09-29 02:44:57'),
(102, 68, 2, 'discount', 220.97, NULL, NULL, '2026-09-29 02:51:50'),
(103, 69, 2, 'discount', 398.00, NULL, NULL, '2026-09-29 02:58:00'),
(104, 70, 2, 'discount', 14.28, NULL, NULL, '2026-09-29 03:01:16'),
(105, 71, 2, 'discount', 1108.79, NULL, NULL, '2026-09-29 03:07:28'),
(106, 72, 2, 'discount', 469.86, NULL, NULL, '2026-09-29 03:13:29'),
(107, 73, 2, 'discount', 499.08, NULL, NULL, '2026-09-29 03:22:04'),
(108, 74, 2, 'discount', 246.60, NULL, NULL, '2026-09-29 03:31:41'),
(109, 75, 2, 'discount', 11.25, NULL, NULL, '2026-09-29 03:39:22'),
(110, 76, 2, 'discount', 281.57, NULL, NULL, '2026-09-29 03:43:58'),
(111, 77, 2, 'discount', 366.69, NULL, NULL, '2026-09-29 03:47:45'),
(112, 78, 2, 'discount', 58.58, NULL, NULL, '2026-09-29 03:51:48'),
(113, 79, 2, 'discount', 179.96, NULL, NULL, '2026-09-29 03:55:24'),
(114, 80, 2, 'discount', 2032.76, NULL, NULL, '2026-09-29 03:59:26'),
(115, 81, 2, 'discount', 129.92, NULL, NULL, '2026-09-29 04:05:19'),
(117, 11, 2, 'discount', 216.07, NULL, NULL, '2026-09-29 09:06:52'),
(119, 59, 2, 'discount', 400.00, NULL, NULL, '2026-09-30 07:08:26'),
(121, 86, 3, 'discount', 447.32, NULL, NULL, '2026-10-01 01:53:17'),
(129, 89, 2, 'discount', 566.24, NULL, NULL, '2026-10-02 05:37:37'),
(136, 92, 2, 'discount', 275.89, NULL, NULL, '2026-10-05 01:47:17'),
(140, 95, 2, 'discount', 641.17, NULL, NULL, '2026-10-05 03:09:14'),
(142, 96, 2, 'discount', 424.30, NULL, NULL, '2026-10-05 03:13:28'),
(145, 85, 2, 'discount', 1469.25, NULL, NULL, '2026-10-06 00:58:49'),
(146, 87, 2, 'discount', 294.22, NULL, NULL, '2026-10-06 01:52:33'),
(149, 97, 2, 'discount', 66.07, NULL, NULL, '2026-10-06 02:04:50'),
(150, 94, 2, 'discount', 332.73, NULL, NULL, '2026-10-06 02:05:39'),
(151, 99, 2, 'discount', 162.49, NULL, NULL, '2026-10-06 02:17:57'),
(152, 100, 2, 'discount', 288.29, NULL, NULL, '2026-10-06 02:20:46'),
(153, 10, 2, 'discount', 199.33, NULL, NULL, '2026-10-06 04:20:35'),
(156, 13, 2, 'discount', 624.93, NULL, NULL, '2026-10-06 04:26:38'),
(157, 12, 2, 'discount', 134.37, NULL, NULL, '2026-10-06 04:27:05'),
(158, 21, 2, 'discount', 120.01, NULL, NULL, '2026-10-06 04:28:38'),
(159, 42, 2, 'discount', 195.25, NULL, NULL, '2026-10-06 04:32:04'),
(160, 42, 3, 'discount', 862.94, NULL, NULL, '2026-10-06 04:32:04'),
(161, 46, 2, 'discount', 339.93, NULL, NULL, '2026-10-06 04:36:01'),
(162, 102, 2, 'discount', 187.59, NULL, NULL, '2026-10-07 01:34:37');

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

--
-- Dumping data for table `report_payments`
--

INSERT INTO `report_payments` (`payment_id`, `report_id`, `payment_method_id`, `payment_source`, `amount`, `reference_number`, `remarks`, `created_at`) VALUES
(75, 5, 1, 'sales', 3379.00, NULL, NULL, '2026-09-23 04:23:06'),
(76, 5, 2, 'sales', 4466.07, NULL, NULL, '2026-09-23 04:23:06'),
(77, 5, 5, 'sales', 27024.72, NULL, NULL, '2026-09-23 04:23:06'),
(78, 5, 6, 'sales', 446.61, NULL, NULL, '2026-09-23 04:23:06'),
(79, 5, 7, 'sales', 2587.05, NULL, NULL, '2026-09-23 04:23:06'),
(80, 5, 8, 'sales', 2854.29, NULL, NULL, '2026-09-23 04:23:06'),
(81, 4, 5, 'sales', 1775.54, NULL, NULL, '2026-09-23 04:24:58'),
(82, 4, 6, 'sales', 4165.43, NULL, NULL, '2026-09-23 04:24:58'),
(83, 6, 1, 'sales', 4139.00, NULL, NULL, '2026-09-23 04:45:35'),
(84, 6, 6, 'sales', 9150.00, NULL, NULL, '2026-09-23 04:45:35'),
(85, 6, 7, 'sales', 1906.25, NULL, NULL, '2026-09-23 04:45:35'),
(90, 8, 6, 'sales', 3831.56, NULL, NULL, '2026-09-23 05:12:41'),
(91, 9, 1, 'sales', 7059.00, NULL, NULL, '2026-09-23 05:16:53'),
(92, 9, 2, 'sales', 1755.18, NULL, NULL, '2026-09-23 05:16:53'),
(93, 9, 5, 'sales', 2467.24, NULL, NULL, '2026-09-23 05:16:53'),
(94, 9, 6, 'sales', 43242.65, NULL, NULL, '2026-09-23 05:16:53'),
(95, 9, 7, 'sales', 4890.90, NULL, NULL, '2026-09-23 05:16:53'),
(110, 14, 1, 'sales', 910.00, NULL, NULL, '2026-09-23 08:17:34'),
(111, 14, 7, 'sales', 1808.22, NULL, NULL, '2026-09-23 08:17:34'),
(112, 15, 1, 'sales', 27513.00, NULL, NULL, '2026-09-23 08:21:48'),
(113, 15, 2, 'sales', 7555.18, NULL, NULL, '2026-09-23 08:21:48'),
(114, 15, 5, 'sales', 10762.27, NULL, NULL, '2026-09-23 08:21:48'),
(115, 15, 6, 'sales', 3654.57, NULL, NULL, '2026-09-23 08:21:48'),
(116, 15, 7, 'sales', 12106.07, NULL, NULL, '2026-09-23 08:21:48'),
(117, 15, 8, 'sales', 1644.82, NULL, NULL, '2026-09-23 08:21:48'),
(118, 16, 1, 'sales', 2471.89, NULL, NULL, '2026-09-23 08:28:59'),
(119, 16, 5, 'sales', 5220.02, NULL, NULL, '2026-09-23 08:28:59'),
(120, 16, 6, 'sales', 3428.99, NULL, NULL, '2026-09-23 08:28:59'),
(121, 16, 7, 'sales', 1818.10, NULL, NULL, '2026-09-23 08:28:59'),
(122, 17, 1, 'sales', 5474.00, NULL, NULL, '2026-09-23 08:45:20'),
(123, 17, 2, 'sales', 2516.26, NULL, NULL, '2026-09-23 08:45:20'),
(124, 17, 5, 'sales', 11995.15, NULL, NULL, '2026-09-23 08:45:20'),
(125, 17, 6, 'sales', 4356.79, NULL, NULL, '2026-09-23 08:45:20'),
(126, 17, 7, 'sales', 3691.83, NULL, NULL, '2026-09-23 08:45:20'),
(127, 17, 8, 'sales', 3175.27, NULL, NULL, '2026-09-23 08:45:20'),
(140, 7, 1, 'sales', 1241.00, NULL, NULL, '2026-09-23 10:05:03'),
(141, 7, 2, 'sales', 3469.38, NULL, NULL, '2026-09-23 10:05:03'),
(142, 7, 5, 'sales', 7336.34, NULL, NULL, '2026-09-23 10:05:03'),
(143, 7, 6, 'sales', 2085.98, NULL, NULL, '2026-09-23 10:05:03'),
(186, 18, 1, 'sales', 5183.00, NULL, NULL, '2026-09-24 03:34:36'),
(187, 18, 2, 'sales', 5631.61, NULL, NULL, '2026-09-24 03:34:36'),
(188, 18, 6, 'sales', 7633.71, NULL, NULL, '2026-09-24 03:34:36'),
(189, 19, 1, 'sales', 545.00, NULL, NULL, '2026-09-24 03:37:22'),
(190, 19, 5, 'sales', 1467.19, NULL, NULL, '2026-09-24 03:37:22'),
(191, 19, 7, 'sales', 1448.75, NULL, NULL, '2026-09-24 03:37:22'),
(240, 20, 1, 'sales', 6084.00, NULL, NULL, '2026-09-24 08:30:32'),
(241, 20, 5, 'sales', 2352.86, NULL, NULL, '2026-09-24 08:30:32'),
(242, 20, 6, 'sales', 18911.26, NULL, NULL, '2026-09-24 08:30:32'),
(243, 20, 7, 'sales', 4063.04, NULL, NULL, '2026-09-24 08:30:32'),
(244, 20, 10, 'sales', 784.29, NULL, NULL, '2026-09-24 08:30:32'),
(298, 30, 1, 'sales', 3191.00, NULL, NULL, '2026-09-25 01:50:48'),
(299, 30, 5, 'sales', 1968.00, NULL, NULL, '2026-09-25 01:50:48'),
(315, 29, 1, 'sales', 2593.00, NULL, NULL, '2026-09-25 02:21:25'),
(316, 29, 2, 'sales', 370.36, NULL, NULL, '2026-09-25 02:21:25'),
(317, 29, 5, 'sales', 10021.43, NULL, NULL, '2026-09-25 02:21:25'),
(318, 29, 6, 'sales', 2264.34, NULL, NULL, '2026-09-25 02:21:25'),
(319, 29, 7, 'sales', 653.57, NULL, NULL, '2026-09-25 02:21:25'),
(320, 28, 1, 'sales', 1640.00, NULL, NULL, '2026-09-25 02:22:27'),
(321, 28, 5, 'sales', 441.16, NULL, NULL, '2026-09-25 02:22:27'),
(379, 31, 1, 'sales', 9103.00, NULL, NULL, '2026-09-25 07:54:52'),
(380, 31, 2, 'sales', 1525.00, NULL, NULL, '2026-09-25 07:54:52'),
(381, 31, 5, 'sales', 6205.33, NULL, NULL, '2026-09-25 07:54:52'),
(382, 31, 6, 'sales', 5010.72, NULL, NULL, '2026-09-25 07:54:52'),
(383, 31, 7, 'sales', 5397.42, NULL, NULL, '2026-09-25 07:54:52'),
(394, 44, 1, 'sales', 14290.00, NULL, NULL, '2026-09-28 03:37:27'),
(395, 44, 2, 'sales', 3567.24, NULL, NULL, '2026-09-28 03:37:27'),
(396, 44, 5, 'sales', 11873.21, NULL, NULL, '2026-09-28 03:37:27'),
(397, 44, 6, 'sales', 17964.30, NULL, NULL, '2026-09-28 03:37:27'),
(398, 44, 7, 'sales', 2156.79, NULL, NULL, '2026-09-28 03:37:27'),
(399, 44, 10, 'sales', 5000.00, NULL, NULL, '2026-09-28 03:37:27'),
(400, 45, 1, 'sales', 6382.00, NULL, NULL, '2026-09-28 03:40:13'),
(401, 45, 2, 'sales', 1160.09, NULL, NULL, '2026-09-28 03:40:13'),
(410, 41, 1, 'sales', 4732.00, NULL, NULL, '2026-09-28 05:35:45'),
(411, 41, 6, 'sales', 5558.73, NULL, NULL, '2026-09-28 05:35:45'),
(412, 41, 7, 'sales', 3564.68, NULL, NULL, '2026-09-28 05:35:45'),
(413, 43, 1, 'sales', 7848.00, NULL, NULL, '2026-09-28 05:36:38'),
(468, 54, 2, 'sales', 833.30, NULL, NULL, '2026-09-28 13:12:43'),
(469, 54, 5, 'sales', 1987.95, NULL, NULL, '2026-09-28 13:12:43'),
(470, 54, 6, 'sales', 1563.13, NULL, NULL, '2026-09-28 13:12:43'),
(471, 55, 1, 'sales', 1912.00, NULL, NULL, '2026-09-28 13:26:06'),
(472, 56, 1, 'sales', 520.00, NULL, NULL, '2026-09-28 13:30:31'),
(473, 56, 6, 'sales', 5652.00, NULL, NULL, '2026-09-28 13:30:31'),
(474, 57, 1, 'sales', 2908.00, NULL, NULL, '2026-09-28 13:47:19'),
(475, 57, 4, 'sales', 3093.57, NULL, NULL, '2026-09-28 13:47:19'),
(476, 58, 1, 'sales', 2053.00, NULL, NULL, '2026-09-28 13:51:07'),
(477, 58, 2, 'sales', 2335.86, NULL, NULL, '2026-09-28 13:51:07'),
(478, 58, 7, 'sales', 4961.70, NULL, NULL, '2026-09-28 13:51:07'),
(488, 61, 1, 'sales', 4992.00, NULL, NULL, '2026-09-29 02:25:53'),
(489, 61, 5, 'sales', 1524.98, NULL, NULL, '2026-09-29 02:25:53'),
(490, 61, 6, 'sales', 4547.05, NULL, NULL, '2026-09-29 02:25:53'),
(491, 62, 1, 'sales', 3789.00, NULL, NULL, '2026-09-29 02:27:59'),
(492, 63, 1, 'sales', 686.00, NULL, NULL, '2026-09-29 02:34:03'),
(493, 63, 5, 'sales', 1134.38, NULL, NULL, '2026-09-29 02:34:03'),
(494, 64, 1, 'sales', 644.00, NULL, NULL, '2026-09-29 02:36:26'),
(495, 64, 5, 'sales', 1696.00, NULL, NULL, '2026-09-29 02:36:26'),
(496, 65, 1, 'sales', 2532.00, NULL, NULL, '2026-09-29 02:40:41'),
(497, 65, 5, 'sales', 3359.04, NULL, NULL, '2026-09-29 02:40:41'),
(498, 65, 6, 'sales', 2232.00, NULL, NULL, '2026-09-29 02:40:41'),
(499, 66, 1, 'sales', 2657.00, NULL, NULL, '2026-09-29 02:44:57'),
(500, 66, 2, 'sales', 479.29, NULL, NULL, '2026-09-29 02:44:57'),
(501, 66, 4, 'sales', 2396.43, NULL, NULL, '2026-09-29 02:44:57'),
(502, 66, 5, 'sales', 2599.56, NULL, NULL, '2026-09-29 02:44:57'),
(503, 67, 1, 'sales', 4128.00, NULL, NULL, '2026-09-29 02:48:34'),
(504, 68, 5, 'sales', 1232.82, NULL, NULL, '2026-09-29 02:51:50'),
(505, 68, 6, 'sales', 994.28, NULL, NULL, '2026-09-29 02:51:50'),
(506, 69, 1, 'sales', 9406.00, NULL, NULL, '2026-09-29 02:58:00'),
(507, 69, 6, 'sales', 6118.81, NULL, NULL, '2026-09-29 02:58:00'),
(508, 70, 1, 'sales', 9023.00, NULL, NULL, '2026-09-29 03:01:16'),
(509, 70, 5, 'sales', 1323.48, NULL, NULL, '2026-09-29 03:01:16'),
(510, 70, 6, 'sales', 23572.15, NULL, NULL, '2026-09-29 03:01:16'),
(511, 70, 7, 'sales', 2935.63, NULL, NULL, '2026-09-29 03:01:16'),
(512, 71, 1, 'sales', 8368.00, NULL, NULL, '2026-09-29 03:07:28'),
(513, 71, 5, 'sales', 1557.68, NULL, NULL, '2026-09-29 03:07:28'),
(514, 71, 6, 'sales', 15399.23, NULL, NULL, '2026-09-29 03:07:28'),
(515, 72, 1, 'sales', 26310.00, NULL, NULL, '2026-09-29 03:13:29'),
(516, 72, 2, 'sales', 1383.39, NULL, NULL, '2026-09-29 03:13:29'),
(517, 72, 5, 'sales', 23433.78, NULL, NULL, '2026-09-29 03:13:29'),
(518, 72, 7, 'sales', 2249.38, NULL, NULL, '2026-09-29 03:13:29'),
(519, 73, 1, 'sales', 15628.00, NULL, NULL, '2026-09-29 03:22:04'),
(520, 73, 2, 'sales', 6948.13, NULL, NULL, '2026-09-29 03:22:04'),
(521, 73, 4, 'sales', 4119.44, NULL, NULL, '2026-09-29 03:22:04'),
(522, 73, 5, 'sales', 14333.26, NULL, NULL, '2026-09-29 03:22:04'),
(523, 73, 6, 'sales', 37219.78, NULL, NULL, '2026-09-29 03:22:04'),
(524, 73, 7, 'sales', 566.43, NULL, NULL, '2026-09-29 03:22:04'),
(525, 74, 1, 'sales', 18964.00, NULL, NULL, '2026-09-29 03:31:41'),
(526, 74, 5, 'sales', 1106.25, NULL, NULL, '2026-09-29 03:31:41'),
(527, 74, 6, 'sales', 10211.59, NULL, NULL, '2026-09-29 03:31:41'),
(528, 74, 7, 'sales', 4079.37, NULL, NULL, '2026-09-29 03:31:41'),
(529, 75, 1, 'sales', 6633.00, NULL, NULL, '2026-09-29 03:39:22'),
(530, 75, 4, 'sales', 1230.89, NULL, NULL, '2026-09-29 03:39:22'),
(531, 75, 5, 'sales', 7308.57, NULL, NULL, '2026-09-29 03:39:22'),
(532, 75, 6, 'sales', 3528.45, NULL, NULL, '2026-09-29 03:39:22'),
(533, 75, 7, 'sales', 2516.25, NULL, NULL, '2026-09-29 03:39:22'),
(534, 76, 1, 'sales', 4456.00, NULL, NULL, '2026-09-29 03:43:58'),
(535, 76, 5, 'sales', 8783.64, NULL, NULL, '2026-09-29 03:43:58'),
(536, 76, 6, 'sales', 5421.26, NULL, NULL, '2026-09-29 03:43:58'),
(537, 76, 7, 'sales', 495.63, NULL, NULL, '2026-09-29 03:43:58'),
(538, 77, 1, 'sales', 5802.00, NULL, NULL, '2026-09-29 03:47:45'),
(539, 77, 2, 'sales', 1599.07, NULL, NULL, '2026-09-29 03:47:45'),
(540, 77, 6, 'sales', 4792.86, NULL, NULL, '2026-09-29 03:47:45'),
(541, 77, 11, 'sales', 6000.00, NULL, NULL, '2026-09-29 03:47:45'),
(542, 78, 1, 'sales', 3197.00, NULL, NULL, '2026-09-29 03:51:48'),
(543, 78, 2, 'sales', 7374.47, NULL, NULL, '2026-09-29 03:51:48'),
(544, 78, 6, 'sales', 11611.79, NULL, NULL, '2026-09-29 03:51:48'),
(545, 78, 8, 'sales', 1686.86, NULL, NULL, '2026-09-29 03:51:48'),
(546, 79, 1, 'sales', 3883.00, NULL, NULL, '2026-09-29 03:55:24'),
(547, 79, 5, 'sales', 12630.26, NULL, NULL, '2026-09-29 03:55:24'),
(548, 79, 6, 'sales', 5548.96, NULL, NULL, '2026-09-29 03:55:24'),
(549, 79, 7, 'sales', 4193.75, NULL, NULL, '2026-09-29 03:55:24'),
(550, 80, 1, 'sales', 19838.00, NULL, NULL, '2026-09-29 03:59:26'),
(551, 80, 2, 'sales', 10055.18, NULL, NULL, '2026-09-29 03:59:26'),
(552, 80, 4, 'sales', 3137.14, NULL, NULL, '2026-09-29 03:59:26'),
(553, 80, 5, 'sales', 20059.62, NULL, NULL, '2026-09-29 03:59:26'),
(554, 80, 6, 'sales', 33438.41, NULL, NULL, '2026-09-29 03:59:26'),
(555, 80, 7, 'sales', 9351.53, NULL, NULL, '2026-09-29 03:59:26'),
(556, 81, 1, 'sales', 4482.00, NULL, NULL, '2026-09-29 04:05:19'),
(557, 81, 2, 'sales', 3436.70, NULL, NULL, '2026-09-29 04:05:19'),
(558, 81, 4, 'sales', 3039.11, NULL, NULL, '2026-09-29 04:05:19'),
(559, 81, 5, 'sales', 12597.59, NULL, NULL, '2026-09-29 04:05:19'),
(560, 81, 6, 'sales', 7156.42, NULL, NULL, '2026-09-29 04:05:19'),
(561, 81, 14, 'sales', 27.00, NULL, NULL, '2026-09-29 04:05:19'),
(568, 11, 1, 'sales', 817.00, NULL, NULL, '2026-09-29 09:06:52'),
(569, 11, 2, 'sales', 2450.89, NULL, NULL, '2026-09-29 09:06:52'),
(570, 11, 5, 'sales', 326.79, NULL, NULL, '2026-09-29 09:06:52'),
(571, 11, 6, 'sales', 18689.96, NULL, NULL, '2026-09-29 09:06:52'),
(576, 59, 6, 'sales', 4947.54, NULL, NULL, '2026-09-30 07:08:26'),
(584, 86, 1, 'sales', 2004.00, NULL, NULL, '2026-10-01 01:53:17'),
(585, 86, 7, 'sales', 1034.82, NULL, NULL, '2026-10-01 01:53:17'),
(603, 84, 2, 'sales', 4700.27, NULL, NULL, '2026-10-01 09:24:12'),
(651, 89, 1, 'sales', 10181.00, NULL, NULL, '2026-10-02 05:37:37'),
(652, 89, 2, 'sales', 544.64, NULL, NULL, '2026-10-02 05:37:37'),
(653, 89, 5, 'sales', 14376.13, NULL, NULL, '2026-10-02 05:37:37'),
(654, 89, 6, 'sales', 1742.86, NULL, NULL, '2026-10-02 05:37:37'),
(655, 89, 7, 'sales', 4235.76, NULL, NULL, '2026-10-02 05:37:37'),
(656, 88, 1, 'sales', 280.00, NULL, NULL, '2026-10-02 05:37:51'),
(693, 92, 1, 'sales', 3026.00, NULL, NULL, '2026-10-05 01:47:17'),
(694, 92, 5, 'sales', 2832.14, NULL, NULL, '2026-10-05 01:47:17'),
(695, 92, 6, 'sales', 1661.16, NULL, NULL, '2026-10-05 01:47:17'),
(696, 93, 1, 'sales', 174.00, NULL, NULL, '2026-10-05 01:48:54'),
(713, 95, 1, 'sales', 15185.00, NULL, NULL, '2026-10-05 03:09:14'),
(714, 95, 2, 'sales', 2140.44, NULL, NULL, '2026-10-05 03:09:14'),
(715, 95, 4, 'sales', 8970.27, NULL, NULL, '2026-10-05 03:09:14'),
(716, 95, 5, 'sales', 3398.57, NULL, NULL, '2026-10-05 03:09:14'),
(717, 95, 6, 'sales', 9050.92, NULL, NULL, '2026-10-05 03:09:14'),
(718, 95, 7, 'sales', 7813.76, NULL, NULL, '2026-10-05 03:09:14'),
(719, 95, 8, 'sales', 2861.00, NULL, NULL, '2026-10-05 03:09:14'),
(725, 96, 1, 'sales', 35497.00, NULL, NULL, '2026-10-05 03:13:28'),
(726, 96, 2, 'sales', 1868.13, NULL, NULL, '2026-10-05 03:13:28'),
(727, 96, 5, 'sales', 7701.26, NULL, NULL, '2026-10-05 03:13:28'),
(728, 96, 6, 'sales', 19514.55, NULL, NULL, '2026-10-05 03:13:28'),
(729, 96, 7, 'sales', 4651.25, NULL, NULL, '2026-10-05 03:13:28'),
(739, 85, 1, 'sales', 20406.00, NULL, NULL, '2026-10-06 00:58:49'),
(740, 85, 6, 'sales', 11757.48, NULL, NULL, '2026-10-06 00:58:49'),
(741, 85, 7, 'sales', 6229.93, NULL, NULL, '2026-10-06 00:58:49'),
(742, 87, 1, 'sales', 22910.00, NULL, NULL, '2026-10-06 01:52:33'),
(743, 87, 2, 'sales', 675.35, NULL, NULL, '2026-10-06 01:52:33'),
(744, 87, 5, 'sales', 12428.75, NULL, NULL, '2026-10-06 01:52:33'),
(745, 87, 6, 'sales', 9370.65, NULL, NULL, '2026-10-06 01:52:33'),
(746, 87, 7, 'sales', 9335.18, NULL, NULL, '2026-10-06 01:52:33'),
(751, 60, 1, 'sales', 6159.00, NULL, NULL, '2026-10-06 02:03:02'),
(752, 60, 2, 'sales', 7423.49, NULL, NULL, '2026-10-06 02:03:02'),
(753, 60, 6, 'sales', 18501.52, NULL, NULL, '2026-10-06 02:03:02'),
(754, 60, 13, 'sales', 980.36, NULL, NULL, '2026-10-06 02:03:02'),
(757, 97, 1, 'sales', 11701.00, NULL, NULL, '2026-10-06 02:04:50'),
(758, 97, 6, 'sales', 2619.74, NULL, NULL, '2026-10-06 02:04:50'),
(759, 97, 7, 'sales', 544.64, NULL, NULL, '2026-10-06 02:04:50'),
(760, 94, 1, 'sales', 2559.00, NULL, NULL, '2026-10-06 02:05:39'),
(761, 94, 6, 'sales', 4293.00, NULL, NULL, '2026-10-06 02:05:39'),
(762, 99, 5, 'sales', 2075.09, NULL, NULL, '2026-10-06 02:17:57'),
(763, 99, 6, 'sales', 2697.51, NULL, NULL, '2026-10-06 02:17:57'),
(764, 99, 7, 'sales', 3414.91, NULL, NULL, '2026-10-06 02:17:57'),
(765, 100, 1, 'sales', 6079.00, NULL, NULL, '2026-10-06 02:20:46'),
(766, 100, 2, 'sales', 2277.14, NULL, NULL, '2026-10-06 02:20:46'),
(767, 100, 5, 'sales', 5898.49, NULL, NULL, '2026-10-06 02:20:46'),
(768, 100, 6, 'sales', 6431.61, NULL, NULL, '2026-10-06 02:20:46'),
(769, 100, 7, 'sales', 2864.82, NULL, NULL, '2026-10-06 02:20:46'),
(770, 10, 1, 'sales', 8141.00, NULL, NULL, '2026-10-06 04:20:35'),
(780, 13, 1, 'sales', 13721.00, NULL, NULL, '2026-10-06 04:26:38'),
(781, 13, 2, 'sales', 21058.98, NULL, NULL, '2026-10-06 04:26:38'),
(782, 13, 5, 'sales', 4444.28, NULL, NULL, '2026-10-06 04:26:38'),
(783, 13, 6, 'sales', 13628.34, NULL, NULL, '2026-10-06 04:26:38'),
(784, 13, 7, 'sales', 7313.56, NULL, NULL, '2026-10-06 04:26:38'),
(785, 12, 1, 'sales', 3050.00, NULL, NULL, '2026-10-06 04:27:05'),
(786, 12, 2, 'sales', 2489.02, NULL, NULL, '2026-10-06 04:27:05'),
(787, 12, 6, 'sales', 2004.29, NULL, NULL, '2026-10-06 04:27:05'),
(788, 12, 7, 'sales', 2341.96, NULL, NULL, '2026-10-06 04:27:05'),
(789, 21, 1, 'sales', 3965.00, NULL, NULL, '2026-10-06 04:28:38'),
(790, 21, 5, 'sales', 4754.73, NULL, NULL, '2026-10-06 04:28:38'),
(791, 21, 6, 'sales', 14032.95, NULL, NULL, '2026-10-06 04:28:38'),
(792, 42, 1, 'sales', 11042.00, NULL, NULL, '2026-10-06 04:32:04'),
(793, 42, 2, 'sales', 9923.38, NULL, NULL, '2026-10-06 04:32:04'),
(794, 42, 5, 'sales', 12155.89, NULL, NULL, '2026-10-06 04:32:04'),
(795, 42, 6, 'sales', 58140.62, NULL, NULL, '2026-10-06 04:32:04'),
(796, 42, 7, 'sales', 3496.61, NULL, NULL, '2026-10-06 04:32:04'),
(797, 42, 8, 'sales', 1546.79, NULL, NULL, '2026-10-06 04:32:04'),
(798, 42, 16, 'sales', 1579.00, NULL, NULL, '2026-10-06 04:32:04'),
(799, 46, 1, 'sales', 6165.00, NULL, NULL, '2026-10-06 04:36:01'),
(800, 46, 2, 'sales', 2331.08, NULL, NULL, '2026-10-06 04:36:01'),
(801, 46, 5, 'sales', 2239.60, NULL, NULL, '2026-10-06 04:36:01'),
(802, 46, 6, 'sales', 14959.66, NULL, NULL, '2026-10-06 04:36:01'),
(803, 46, 7, 'sales', 1459.64, NULL, NULL, '2026-10-06 04:36:01'),
(804, 101, 5, 'sales', 1742.86, NULL, NULL, '2026-10-07 01:32:38'),
(805, 102, 1, 'sales', 9052.00, NULL, NULL, '2026-10-07 01:34:37'),
(806, 102, 5, 'sales', 11979.14, NULL, NULL, '2026-10-07 01:34:37');

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

--
-- Dumping data for table `report_sales`
--

INSERT INTO `report_sales` (`sale_id`, `report_id`, `sales_type`, `amount`, `remarks`, `created_at`) VALUES
(37, 5, 'kitchen', 18898.39, NULL, '2026-09-23 04:23:06'),
(38, 5, 'bar', 14220.00, NULL, '2026-09-23 04:23:06'),
(39, 5, 'corkage', 4300.00, NULL, '2026-09-23 04:23:06'),
(40, 5, 'service_charge', 3339.35, NULL, '2026-09-23 04:23:06'),
(41, 5, 'grab_gross', 3015.00, NULL, '2026-09-23 04:23:06'),
(42, 5, 'tip', 2200.95, NULL, '2026-09-23 04:23:06'),
(43, 4, 'kitchen', 4114.00, NULL, '2026-09-23 04:24:58'),
(44, 4, 'bar', 1340.00, NULL, '2026-09-23 04:24:58'),
(45, 4, 'service_charge', 486.97, NULL, '2026-09-23 04:24:58'),
(46, 6, 'kitchen', 11820.00, NULL, '2026-09-23 04:45:35'),
(47, 6, 'bar', 2130.00, NULL, '2026-09-23 04:45:35'),
(48, 6, 'service_charge', 1245.54, NULL, '2026-09-23 04:45:35'),
(52, 8, 'kitchen', 3318.48, NULL, '2026-09-23 05:12:41'),
(53, 8, 'bar', 190.00, NULL, '2026-09-23 05:12:41'),
(54, 8, 'service_charge', 323.08, NULL, '2026-09-23 05:12:41'),
(55, 9, 'kitchen', 37558.55, NULL, '2026-09-23 05:16:53'),
(56, 9, 'bar', 16975.00, NULL, '2026-09-23 05:16:53'),
(57, 9, 'service_charge', 4881.71, NULL, '2026-09-23 05:16:53'),
(72, 14, 'kitchen', 2345.00, NULL, '2026-09-23 08:17:34'),
(73, 14, 'bar', 225.00, NULL, '2026-09-23 08:17:34'),
(74, 14, 'service_charge', 148.22, NULL, '2026-09-23 08:17:34'),
(75, 15, 'kitchen', 29383.98, NULL, '2026-09-23 08:21:48'),
(76, 15, 'bar', 28650.00, NULL, '2026-09-23 08:21:48'),
(77, 15, 'service_charge', 5202.13, NULL, '2026-09-23 08:21:48'),
(78, 16, 'kitchen', 10638.51, NULL, '2026-09-23 08:28:59'),
(79, 16, 'bar', 1210.00, NULL, '2026-09-23 08:28:59'),
(80, 16, 'service_charge', 1090.49, NULL, '2026-09-23 08:28:59'),
(81, 16, 'grab_gross', 2550.00, NULL, '2026-09-23 08:28:59'),
(82, 16, 'tip', 1861.50, NULL, '2026-09-23 08:28:59'),
(83, 17, 'kitchen', 20887.71, NULL, '2026-09-23 08:45:20'),
(84, 17, 'bar', 7785.00, NULL, '2026-09-23 08:45:20'),
(85, 17, 'service_charge', 2537.12, NULL, '2026-09-23 08:45:20'),
(86, 17, 'grab_gross', 600.00, NULL, '2026-09-23 08:45:20'),
(87, 17, 'tip', 438.00, NULL, '2026-09-23 08:45:20'),
(101, 7, 'kitchen', 8555.00, NULL, '2026-09-23 10:05:03'),
(102, 7, 'bar', 6320.00, NULL, '2026-09-23 10:05:03'),
(103, 7, 'service_charge', 1158.49, NULL, '2026-09-23 10:05:03'),
(137, 18, 'kitchen', 14219.99, NULL, '2026-09-24 03:34:36'),
(138, 18, 'bar', 2865.00, NULL, '2026-09-24 03:34:36'),
(139, 18, 'service_charge', 1363.57, NULL, '2026-09-24 03:34:36'),
(140, 19, 'kitchen', 2791.21, NULL, '2026-09-24 03:37:22'),
(141, 19, 'bar', 385.00, NULL, '2026-09-24 03:37:22'),
(142, 19, 'service_charge', 284.73, NULL, '2026-09-24 03:37:22'),
(208, 20, 'kitchen', 22404.01, NULL, '2026-09-24 08:30:32'),
(209, 20, 'bar', 7125.00, NULL, '2026-09-24 08:30:32'),
(210, 20, 'service_charge', 2666.55, NULL, '2026-09-24 08:30:32'),
(211, 20, 'grab_gross', 860.00, NULL, '2026-09-24 08:30:32'),
(212, 20, 'tip', 1883.40, NULL, '2026-09-24 08:30:32'),
(260, 30, 'kitchen', 3983.00, NULL, '2026-09-25 01:50:48'),
(261, 30, 'bar', 915.00, NULL, '2026-09-25 01:50:48'),
(262, 30, 'service_charge', 261.61, NULL, '2026-09-25 01:50:48'),
(278, 29, 'kitchen', 4611.69, NULL, '2026-09-25 02:21:25'),
(279, 29, 'bar', 9985.00, NULL, '2026-09-25 02:21:25'),
(280, 29, 'service_charge', 1306.06, NULL, '2026-09-25 02:21:25'),
(281, 29, 'grab_gross', 1580.00, NULL, '2026-09-25 02:21:25'),
(282, 29, 'tip', 2306.80, NULL, '2026-09-25 02:21:25'),
(283, 28, 'kitchen', 1225.00, NULL, '2026-09-25 02:22:27'),
(284, 28, 'bar', 820.00, NULL, '2026-09-25 02:22:27'),
(285, 28, 'service_charge', 36.16, NULL, '2026-09-25 02:22:27'),
(286, 28, 'grab_gross', 1830.00, NULL, '2026-09-25 02:22:27'),
(287, 28, 'tip', 1335.90, NULL, '2026-09-25 02:22:27'),
(356, 31, 'kitchen', 11884.34, NULL, '2026-09-25 07:54:52'),
(357, 31, 'bar', 13115.00, NULL, '2026-09-25 07:54:52'),
(358, 31, 'service_charge', 2242.56, NULL, '2026-09-25 07:54:52'),
(359, 31, 'grab_gross', 1230.00, NULL, '2026-09-25 07:54:52'),
(360, 31, 'tip', 4489.50, NULL, '2026-09-25 07:54:52'),
(378, 44, 'kitchen', 23043.13, NULL, '2026-09-28 03:37:27'),
(379, 44, 'bar', 27385.00, NULL, '2026-09-28 03:37:27'),
(380, 44, 'service_charge', 4424.83, NULL, '2026-09-28 03:37:27'),
(381, 45, 'kitchen', 6473.71, NULL, '2026-09-28 03:40:13'),
(382, 45, 'bar', 445.00, NULL, '2026-09-28 03:40:13'),
(383, 45, 'service_charge', 623.81, NULL, '2026-09-28 03:40:13'),
(391, 41, 'kitchen', 11354.83, NULL, '2026-09-28 05:35:45'),
(392, 41, 'bar', 1350.00, NULL, '2026-09-28 05:35:45'),
(393, 41, 'service_charge', 1151.11, NULL, '2026-09-28 05:35:45'),
(394, 43, 'kitchen', 6310.00, NULL, '2026-09-28 05:36:38'),
(395, 43, 'bar', 895.00, NULL, '2026-09-28 05:36:38'),
(396, 43, 'service_charge', 643.31, NULL, '2026-09-28 05:36:38'),
(397, 43, 'grab_gross', 460.00, NULL, '2026-09-28 05:36:38'),
(398, 43, 'tip', 671.60, NULL, '2026-09-28 05:36:38'),
(422, 50, 'corkage', 1000.00, NULL, '2026-09-28 08:12:43'),
(423, 51, 'corkage', 1000.00, NULL, '2026-09-28 08:14:02'),
(425, 52, 'corkage', 1000.00, NULL, '2026-09-28 08:14:33'),
(449, 54, 'kitchen', 3835.00, NULL, '2026-09-28 13:12:43'),
(450, 54, 'bar', 190.00, NULL, '2026-09-28 13:12:43'),
(451, 54, 'service_charge', 359.38, NULL, '2026-09-28 13:12:43'),
(452, 55, 'kitchen', 1199.00, NULL, '2026-09-28 13:26:06'),
(453, 55, 'bar', 825.00, NULL, '2026-09-28 13:26:06'),
(454, 56, 'kitchen', 5792.00, NULL, '2026-09-28 13:30:31'),
(455, 56, 'bar', 380.00, NULL, '2026-09-28 13:30:31'),
(456, 57, 'kitchen', 5060.00, NULL, '2026-09-28 13:47:19'),
(457, 57, 'bar', 450.00, NULL, '2026-09-28 13:47:19'),
(458, 57, 'service_charge', 491.96, NULL, '2026-09-28 13:47:19'),
(459, 58, 'kitchen', 8360.35, NULL, '2026-09-28 13:51:07'),
(460, 58, 'bar', 220.00, NULL, '2026-09-28 13:51:07'),
(461, 58, 'service_charge', 770.51, NULL, '2026-09-28 13:51:07'),
(462, 58, 'grab_gross', 1610.00, NULL, '2026-09-28 13:51:07'),
(463, 58, 'tip', 1175.30, NULL, '2026-09-28 13:51:07'),
(474, 61, 'kitchen', 6228.48, NULL, '2026-09-29 02:25:53'),
(475, 61, 'bar', 3920.00, NULL, '2026-09-29 02:25:53'),
(476, 61, 'service_charge', 915.75, NULL, '2026-09-29 02:25:53'),
(477, 62, 'kitchen', 2806.00, NULL, '2026-09-29 02:27:59'),
(478, 62, 'bar', 810.00, NULL, '2026-09-29 02:27:59'),
(479, 62, 'service_charge', 173.21, NULL, '2026-09-29 02:27:59'),
(480, 62, 'grab_gross', 880.00, NULL, '2026-09-29 02:27:59'),
(481, 62, 'tip', 642.40, NULL, '2026-09-29 02:27:59'),
(482, 63, 'kitchen', 717.14, NULL, '2026-09-29 02:34:03'),
(483, 63, 'bar', 950.00, NULL, '2026-09-29 02:34:03'),
(484, 63, 'service_charge', 153.49, NULL, '2026-09-29 02:34:03'),
(485, 64, 'kitchen', 1830.00, NULL, '2026-09-29 02:36:26'),
(486, 64, 'bar', 510.00, NULL, '2026-09-29 02:36:26'),
(487, 65, 'kitchen', 6521.90, NULL, '2026-09-29 02:40:41'),
(488, 65, 'bar', 930.00, NULL, '2026-09-29 02:40:41'),
(489, 65, 'service_charge', 671.73, NULL, '2026-09-29 02:40:41'),
(490, 66, 'kitchen', 5931.97, NULL, '2026-09-29 02:44:57'),
(491, 66, 'bar', 1525.00, NULL, '2026-09-29 02:44:57'),
(492, 66, 'service_charge', 675.80, NULL, '2026-09-29 02:44:57'),
(493, 67, 'kitchen', 3170.00, NULL, '2026-09-29 02:48:34'),
(494, 67, 'bar', 620.00, NULL, '2026-09-29 02:48:34'),
(495, 67, 'service_charge', 338.39, NULL, '2026-09-29 02:48:34'),
(496, 68, 'kitchen', 1931.44, NULL, '2026-09-29 02:51:50'),
(497, 68, 'bar', 190.00, NULL, '2026-09-29 02:51:50'),
(498, 68, 'service_charge', 105.67, NULL, '2026-09-29 02:51:50'),
(499, 69, 'kitchen', 12113.22, NULL, '2026-09-29 02:58:00'),
(500, 69, 'bar', 2140.00, NULL, '2026-09-29 02:58:00'),
(501, 69, 'service_charge', 1271.80, NULL, '2026-09-29 02:58:00'),
(502, 70, 'kitchen', 14389.00, NULL, '2026-09-29 03:01:16'),
(503, 70, 'bar', 19445.00, NULL, '2026-09-29 03:01:16'),
(504, 70, 'service_charge', 3020.90, NULL, '2026-09-29 03:01:16'),
(505, 71, 'kitchen', 16115.94, NULL, '2026-09-29 03:07:28'),
(506, 71, 'bar', 7250.00, NULL, '2026-09-29 03:07:28'),
(507, 71, 'service_charge', 1959.22, NULL, '2026-09-29 03:07:28'),
(508, 71, 'grab_gross', 540.00, NULL, '2026-09-29 03:07:28'),
(509, 71, 'tip', 394.20, NULL, '2026-09-29 03:07:28'),
(510, 72, 'kitchen', 24303.24, NULL, '2026-09-29 03:13:29'),
(511, 72, 'bar', 23680.00, NULL, '2026-09-29 03:13:29'),
(512, 72, 'corkage', 1000.00, NULL, '2026-09-29 03:13:29'),
(513, 72, 'service_charge', 4393.66, NULL, '2026-09-29 03:13:29'),
(514, 72, 'grab_gross', 1220.00, NULL, '2026-09-29 03:13:29'),
(515, 72, 'tip', 890.60, NULL, '2026-09-29 03:13:29'),
(516, 73, 'kitchen', 39526.45, NULL, '2026-09-29 03:22:04'),
(517, 73, 'bar', 29850.00, NULL, '2026-09-29 03:22:04'),
(518, 73, 'corkage', 3000.00, NULL, '2026-09-29 03:22:04'),
(519, 73, 'service_charge', 6465.74, NULL, '2026-09-29 03:22:04'),
(520, 74, 'kitchen', 24935.46, NULL, '2026-09-29 03:31:41'),
(521, 74, 'bar', 6600.00, NULL, '2026-09-29 03:31:41'),
(522, 74, 'service_charge', 2826.21, NULL, '2026-09-29 03:31:41'),
(523, 74, 'grab_gross', 1010.00, NULL, '2026-09-29 03:31:41'),
(524, 74, 'tip', 737.30, NULL, '2026-09-29 03:31:41'),
(525, 75, 'kitchen', 10080.82, NULL, '2026-09-29 03:39:22'),
(526, 75, 'bar', 9615.00, NULL, '2026-09-29 03:39:22'),
(527, 75, 'service_charge', 1522.09, NULL, '2026-09-29 03:39:22'),
(528, 76, 'kitchen', 10364.48, NULL, '2026-09-29 03:43:58'),
(529, 76, 'bar', 7275.00, NULL, '2026-09-29 03:43:58'),
(530, 76, 'service_charge', 1512.02, NULL, '2026-09-29 03:43:58'),
(531, 76, 'grab_gross', 540.00, NULL, '2026-09-29 03:43:58'),
(532, 76, 'tip', 394.20, NULL, '2026-09-29 03:43:58'),
(533, 77, 'kitchen', 9815.59, NULL, '2026-09-29 03:47:45'),
(534, 77, 'bar', 6875.00, NULL, '2026-09-29 03:47:45'),
(535, 77, 'service_charge', 1503.96, NULL, '2026-09-29 03:47:45'),
(536, 78, 'kitchen', 11281.28, NULL, '2026-09-29 03:51:48'),
(537, 78, 'bar', 10630.00, NULL, '2026-09-29 03:51:48'),
(538, 78, 'service_charge', 1958.88, NULL, '2026-09-29 03:51:48'),
(539, 79, 'kitchen', 13382.05, NULL, '2026-09-29 03:55:24'),
(540, 79, 'bar', 10715.00, NULL, '2026-09-29 03:55:24'),
(541, 79, 'service_charge', 2159.23, NULL, '2026-09-29 03:55:24'),
(542, 80, 'kitchen', 35893.16, NULL, '2026-09-29 03:59:26'),
(543, 80, 'bar', 52512.00, NULL, '2026-09-29 03:59:26'),
(544, 80, 'service_charge', 7445.03, NULL, '2026-09-29 03:59:26'),
(545, 81, 'kitchen', 22168.15, NULL, '2026-09-29 04:05:19'),
(546, 81, 'bar', 6020.00, NULL, '2026-09-29 04:05:19'),
(547, 81, 'service_charge', 2524.08, NULL, '2026-09-29 04:05:19'),
(567, 11, 'kitchen', 7483.00, NULL, '2026-09-29 09:06:52'),
(568, 11, 'bar', 19955.00, NULL, '2026-09-29 09:06:52'),
(569, 11, 'service_charge', 2449.81, NULL, '2026-09-29 09:06:52'),
(570, 11, 'grab_gross', 1180.00, NULL, '2026-09-29 09:06:52'),
(571, 11, 'tip', 12059.60, NULL, '2026-09-29 09:06:52'),
(580, 59, 'kitchen', 4542.00, NULL, '2026-09-30 07:08:26'),
(581, 59, 'service_charge', 405.54, NULL, '2026-09-30 07:08:26'),
(592, 86, 'kitchen', 2379.00, NULL, '2026-10-01 01:53:17'),
(593, 86, 'bar', 575.00, NULL, '2026-10-01 01:53:17'),
(594, 86, 'service_charge', 84.82, NULL, '2026-10-01 01:53:17'),
(612, 84, 'kitchen', 4030.00, NULL, '2026-10-01 09:24:12'),
(613, 84, 'bar', 285.00, NULL, '2026-10-01 09:24:12'),
(614, 84, 'service_charge', 385.27, NULL, '2026-10-01 09:24:12'),
(641, 89, 'kitchen', 16759.05, NULL, '2026-10-02 05:37:37'),
(642, 89, 'bar', 11875.00, NULL, '2026-10-02 05:37:37'),
(643, 89, 'service_charge', 2446.96, NULL, '2026-10-02 05:37:37'),
(644, 88, 'kitchen', 280.00, NULL, '2026-10-02 05:37:51'),
(657, 92, 'kitchen', 6312.29, NULL, '2026-10-05 01:47:17'),
(658, 92, 'bar', 605.00, NULL, '2026-10-05 01:47:17'),
(659, 92, 'service_charge', 602.32, NULL, '2026-10-05 01:47:17'),
(660, 93, 'bar', 160.00, NULL, '2026-10-05 01:48:54'),
(661, 93, 'service_charge', 14.29, NULL, '2026-10-05 01:48:54'),
(680, 98, 'corkage', 1000.00, NULL, '2026-10-05 02:54:32'),
(681, 95, 'kitchen', 25387.16, NULL, '2026-10-05 03:09:14'),
(682, 95, 'bar', 20555.00, NULL, '2026-10-05 03:09:14'),
(683, 95, 'service_charge', 4066.86, NULL, '2026-10-05 03:09:14'),
(684, 95, 'grab_gross', 1220.00, NULL, '2026-10-05 03:09:14'),
(685, 95, 'tip', 1781.20, NULL, '2026-10-05 03:09:14'),
(691, 96, 'kitchen', 27396.16, NULL, '2026-10-05 03:13:28'),
(692, 96, 'bar', 31275.00, NULL, '2026-10-05 03:13:28'),
(693, 96, 'service_charge', 5256.69, NULL, '2026-10-05 03:13:28'),
(694, 96, 'grab_gross', 615.00, NULL, '2026-10-05 03:13:28'),
(695, 96, 'tip', 1346.85, NULL, '2026-10-05 03:13:28'),
(706, 85, 'kitchen', 27369.21, NULL, '2026-10-06 00:58:49'),
(707, 85, 'bar', 14800.00, NULL, '2026-10-06 00:58:49'),
(708, 85, 'service_charge', 3828.08, NULL, '2026-10-06 00:58:49'),
(709, 85, 'grab_gross', 1960.00, NULL, '2026-10-06 00:58:49'),
(710, 85, 'tip', 4292.40, NULL, '2026-10-06 00:58:49'),
(711, 87, 'kitchen', 31250.70, NULL, '2026-10-06 01:52:33'),
(712, 87, 'bar', 19875.00, NULL, '2026-10-06 01:52:33'),
(713, 87, 'service_charge', 4575.48, NULL, '2026-10-06 01:52:33'),
(714, 87, 'grab_gross', 340.00, NULL, '2026-10-06 01:52:33'),
(715, 87, 'tip', 744.60, NULL, '2026-10-06 01:52:33'),
(721, 60, 'kitchen', 16805.00, NULL, '2026-10-06 02:03:02'),
(722, 60, 'bar', 11925.00, NULL, '2026-10-06 02:03:02'),
(723, 60, 'service_charge', 2373.67, NULL, '2026-10-06 02:03:02'),
(724, 60, 'grab_gross', 930.00, NULL, '2026-10-06 02:03:02'),
(725, 60, 'tip', 4752.30, NULL, '2026-10-06 02:03:02'),
(729, 97, 'kitchen', 11624.28, NULL, '2026-10-06 02:04:50'),
(730, 97, 'bar', 2020.00, NULL, '2026-10-06 02:04:50'),
(731, 97, 'service_charge', 1221.08, NULL, '2026-10-06 02:04:50'),
(732, 97, 'grab_gross', 1460.00, NULL, '2026-10-06 02:04:50'),
(733, 97, 'tip', 2131.60, NULL, '2026-10-06 02:04:50'),
(734, 94, 'kitchen', 6208.60, NULL, '2026-10-06 02:05:39'),
(735, 94, 'bar', 69.00, NULL, '2026-10-06 02:05:39'),
(736, 94, 'service_charge', 574.76, NULL, '2026-10-06 02:05:39'),
(737, 99, 'kitchen', 6200.01, NULL, '2026-10-06 02:17:57'),
(738, 99, 'bar', 1310.00, NULL, '2026-10-06 02:17:57'),
(739, 99, 'service_charge', 677.50, NULL, '2026-10-06 02:17:57'),
(740, 99, 'grab_gross', 1890.00, NULL, '2026-10-06 02:17:57'),
(741, 99, 'tip', 1379.70, NULL, '2026-10-06 02:17:57'),
(742, 100, 'kitchen', 14493.71, NULL, '2026-10-06 02:20:46'),
(743, 100, 'bar', 7235.00, NULL, '2026-10-06 02:20:46'),
(744, 100, 'service_charge', 1822.51, NULL, '2026-10-06 02:20:46'),
(745, 10, 'kitchen', 6186.06, NULL, '2026-10-06 04:20:35'),
(746, 10, 'bar', 1280.00, NULL, '2026-10-06 04:20:35'),
(747, 10, 'service_charge', 675.15, NULL, '2026-10-06 04:20:35'),
(754, 13, 'kitchen', 27253.31, NULL, '2026-10-06 04:26:38'),
(755, 13, 'bar', 33960.00, NULL, '2026-10-06 04:26:38'),
(756, 13, 'service_charge', 5489.28, NULL, '2026-10-06 04:26:38'),
(757, 12, 'kitchen', 8560.29, NULL, '2026-10-06 04:27:05'),
(758, 12, 'bar', 510.00, NULL, '2026-10-06 04:27:05'),
(759, 12, 'service_charge', 815.58, NULL, '2026-10-06 04:27:05'),
(760, 21, 'kitchen', 19837.99, NULL, '2026-10-06 04:28:38'),
(761, 21, 'bar', 5915.00, NULL, '2026-10-06 04:28:38'),
(762, 21, 'service_charge', 2304.51, NULL, '2026-10-06 04:28:38'),
(763, 42, 'kitchen', 42266.11, NULL, '2026-10-06 04:32:04'),
(764, 42, 'bar', 56720.00, NULL, '2026-10-06 04:32:04'),
(765, 42, 'service_charge', 7984.02, NULL, '2026-10-06 04:32:04'),
(766, 42, 'grab_gross', 540.00, NULL, '2026-10-06 04:32:04'),
(767, 42, 'tip', 1182.60, NULL, '2026-10-06 04:32:04'),
(768, 46, 'kitchen', 13596.12, NULL, '2026-10-06 04:36:01'),
(769, 46, 'bar', 11320.00, NULL, '2026-10-06 04:36:01'),
(770, 46, 'service_charge', 2239.22, NULL, '2026-10-06 04:36:01'),
(771, 101, 'kitchen', 1600.00, NULL, '2026-10-07 01:32:38'),
(772, 101, 'service_charge', 142.86, NULL, '2026-10-07 01:32:38'),
(773, 102, 'kitchen', 16474.86, NULL, '2026-10-07 01:34:37'),
(774, 102, 'bar', 2825.00, NULL, '2026-10-07 01:34:37'),
(775, 102, 'service_charge', 1731.24, NULL, '2026-10-07 01:34:37');

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
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`);

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
  MODIFY `account_holder_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `report_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

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
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `remittance_types`
--
ALTER TABLE `remittance_types`
  MODIFY `remittance_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `report_accounts`
--
ALTER TABLE `report_accounts`
  MODIFY `report_account_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

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
  MODIFY `deduction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `report_expenses`
--
ALTER TABLE `report_expenses`
  MODIFY `expense_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `report_payments`
--
ALTER TABLE `report_payments`
  MODIFY `payment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=807;

--
-- AUTO_INCREMENT for table `report_remittances`
--
ALTER TABLE `report_remittances`
  MODIFY `remittance_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_sales`
--
ALTER TABLE `report_sales`
  MODIFY `sale_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=776;

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
