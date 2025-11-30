-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2024 at 07:07 AM
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
-- Database: `taf`
--

-- --------------------------------------------------------

--
-- Table structure for table `do_transfer_h`
--

CREATE TABLE `do_transfer_h` (
  `id_do_transfer` int(11) NOT NULL,
  `code_trf` varchar(30) NOT NULL,
  `tgl_trf` int(11) NOT NULL,
  `id_do` int(11) NOT NULL,
  `id_wrh` int(11) NOT NULL,
  `desc_trf` varchar(255) NOT NULL,
  `total_qty_trf` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mproduct`
--

CREATE TABLE `mproduct` (
  `id_product` int(11) NOT NULL,
  `kode_product` varchar(25) NOT NULL,
  `nama_product` varchar(50) NOT NULL,
  `id_unit` int(11) NOT NULL,
  `id_type` int(11) NOT NULL,
  `harga_beli` int(11) NOT NULL,
  `harga_jual` int(11) NOT NULL,
  `harga_rata_rata` int(11) NOT NULL,
  `flag_archive` varchar(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mproduct_type`
--

CREATE TABLE `mproduct_type` (
  `id_type` int(11) NOT NULL,
  `nama_type` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mproduct_unit`
--

CREATE TABLE `mproduct_unit` (
  `id_unit` int(11) NOT NULL,
  `nama_unit` varchar(25) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_customers`
--

CREATE TABLE `m_customers` (
  `id_cust` int(11) NOT NULL,
  `code_cust` varchar(60) NOT NULL,
  `nama_cust` varchar(60) NOT NULL,
  `tgl_cust` date NOT NULL,
  `phone` varchar(13) NOT NULL,
  `email` varchar(100) NOT NULL,
  `npwp_cust` varchar(25) NOT NULL,
  `address_cust` varchar(255) NOT NULL,
  `address_npwp` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_suppliers`
--

CREATE TABLE `m_suppliers` (
  `id_supplier` int(11) NOT NULL,
  `code_spl` varchar(60) NOT NULL,
  `nama_spl` varchar(60) NOT NULL,
  `tgl_spl` date NOT NULL,
  `phone` varchar(13) NOT NULL,
  `email` varchar(100) NOT NULL,
  `npwp_spl` varchar(25) NOT NULL,
  `address_spl` varchar(255) NOT NULL,
  `address_npwp` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_werehouses`
--

CREATE TABLE `m_werehouses` (
  `id_wrh` int(11) NOT NULL,
  `code_wh` varchar(60) NOT NULL,
  `nama_wh` varchar(60) NOT NULL,
  `phone` text NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po_his`
--

CREATE TABLE `po_his` (
  `id_po_his` int(11) NOT NULL,
  `id_po` int(11) NOT NULL,
  `id_po_dtl` int(11) NOT NULL,
  `tgl_po` datetime NOT NULL,
  `id_cust` int(11) NOT NULL,
  `nama_cust` varchar(60) NOT NULL,
  `code_cust` varchar(60) NOT NULL,
  `no_po` varchar(30) NOT NULL,
  `no_so` varchar(30) NOT NULL,
  `reason_po` varchar(100) NOT NULL,
  `code_spl` varchar(60) NOT NULL,
  `nama_spl` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request`
--

CREATE TABLE `purchase_request` (
  `id_pur_req` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_wrh` int(11) NOT NULL,
  `id_user_req` int(11) NOT NULL,
  `id_user_aprove` int(11) NOT NULL,
  `request_qty` int(11) NOT NULL,
  `request_date` datetime NOT NULL,
  `approved_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_detail`
--

CREATE TABLE `purchase_request_detail` (
  `id_purchase_req_dtl` int(11) NOT NULL,
  `id_purchase` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `code_purchase` varchar(30) NOT NULL,
  `kode_product` varchar(25) NOT NULL,
  `total_product` int(11) NOT NULL,
  `desc_product` int(11) NOT NULL,
  `product_qty` int(11) NOT NULL,
  `request_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_mutation`
--

CREATE TABLE `stock_mutation` (
  `id_stock_mut` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_wrh` int(11) NOT NULL,
  `qty_start` int(11) NOT NULL,
  `qty_in` int(11) NOT NULL,
  `qty_out` int(11) NOT NULL,
  `qty_last` int(11) NOT NULL,
  `tgl_mutasi` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_opname_his`
--

CREATE TABLE `stock_opname_his` (
  `id_stck_ophis` int(11) NOT NULL,
  `id_wrh` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_stck_op` int(11) NOT NULL,
  `qty_in` int(11) NOT NULL,
  `qty_out` int(11) NOT NULL,
  `qty_last` int(11) NOT NULL,
  `tgl_opname` date NOT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_at` datetime NOT NULL,
  `updated_by` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tdos`
--

CREATE TABLE `tdos` (
  `id_do` int(11) NOT NULL,
  `tgl_do` date NOT NULL,
  `tgl_po` date NOT NULL,
  `id_po` int(11) NOT NULL,
  `code_cust` varchar(60) NOT NULL,
  `nama_cust` varchar(60) NOT NULL,
  `no_po` varchar(30) NOT NULL,
  `no_so` varchar(30) NOT NULL,
  `no_do` varchar(30) NOT NULL,
  `shipping_via` varchar(10) NOT NULL,
  `status_lmpr_do` varchar(10) NOT NULL,
  `reason_do` varchar(10) NOT NULL,
  `file` varchar(50) NOT NULL,
  `upload_date_at` datetime NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tpos`
--

CREATE TABLE `tpos` (
  `id_po` int(11) NOT NULL,
  `id_cust` int(11) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `tgl_po` datetime NOT NULL,
  `code_cust` varchar(60) NOT NULL,
  `nama_cust` varchar(60) NOT NULL,
  `no_po` varchar(30) NOT NULL,
  `no_so` varchar(30) NOT NULL,
  `status_po` varchar(10) NOT NULL,
  `reason_po` varchar(100) NOT NULL,
  `grand_total` int(11) NOT NULL,
  `code_spl` varchar(60) NOT NULL,
  `nama_spl` varchar(60) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tpo_detail`
--

CREATE TABLE `tpo_detail` (
  `id_po_dtl` int(11) NOT NULL,
  `id_po` int(11) NOT NULL,
  `part_number` varchar(25) NOT NULL,
  `nama_product` varchar(30) NOT NULL,
  `qty` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `total_price` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_invoice_d`
--

CREATE TABLE `t_invoice_d` (
  `id_invoice_d` int(11) NOT NULL,
  `id_inv_h` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `kode_product` varchar(25) NOT NULL,
  `no_inv` varchar(30) NOT NULL,
  `tgl_inv` date NOT NULL,
  `qty` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_invoice_h`
--

CREATE TABLE `t_invoice_h` (
  `id_inv_h` int(11) NOT NULL,
  `id_cust` int(11) NOT NULL,
  `no_env` varchar(30) NOT NULL,
  `code_cust` varchar(30) NOT NULL,
  `tgl_env` date NOT NULL,
  `grand_total` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_stck_op`
--

CREATE TABLE `t_stck_op` (
  `id_stck_op` int(11) NOT NULL,
  `id_wrh` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `qty_in` int(11) NOT NULL,
  `qty_out` int(11) NOT NULL,
  `qty_last` int(11) NOT NULL,
  `tgl_opname` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `email_verified_at` datetime NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `do_transfer_h`
--
ALTER TABLE `do_transfer_h`
  ADD PRIMARY KEY (`id_do_transfer`),
  ADD KEY `id_do` (`id_do`),
  ADD KEY `id_wrh` (`id_wrh`);

--
-- Indexes for table `mproduct`
--
ALTER TABLE `mproduct`
  ADD PRIMARY KEY (`id_product`),
  ADD KEY `id_unit` (`id_unit`),
  ADD KEY `id_type` (`id_type`);

--
-- Indexes for table `mproduct_type`
--
ALTER TABLE `mproduct_type`
  ADD PRIMARY KEY (`id_type`);

--
-- Indexes for table `mproduct_unit`
--
ALTER TABLE `mproduct_unit`
  ADD PRIMARY KEY (`id_unit`);

--
-- Indexes for table `m_customers`
--
ALTER TABLE `m_customers`
  ADD PRIMARY KEY (`id_cust`);

--
-- Indexes for table `m_suppliers`
--
ALTER TABLE `m_suppliers`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `m_werehouses`
--
ALTER TABLE `m_werehouses`
  ADD PRIMARY KEY (`id_wrh`);

--
-- Indexes for table `po_his`
--
ALTER TABLE `po_his`
  ADD PRIMARY KEY (`id_po_his`),
  ADD KEY `id_po` (`id_po`),
  ADD KEY `id_po_dtl` (`id_po_dtl`),
  ADD KEY `id_cust` (`id_cust`);

--
-- Indexes for table `purchase_request`
--
ALTER TABLE `purchase_request`
  ADD PRIMARY KEY (`id_pur_req`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_wrh` (`id_wrh`),
  ADD KEY `id_user_req` (`id_user_req`),
  ADD KEY `id_user_aprove` (`id_user_aprove`);

--
-- Indexes for table `purchase_request_detail`
--
ALTER TABLE `purchase_request_detail`
  ADD PRIMARY KEY (`id_purchase_req_dtl`),
  ADD KEY `id_purchase` (`id_purchase`),
  ADD KEY `id_product` (`id_product`);

--
-- Indexes for table `stock_mutation`
--
ALTER TABLE `stock_mutation`
  ADD PRIMARY KEY (`id_stock_mut`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_wrh` (`id_wrh`);

--
-- Indexes for table `stock_opname_his`
--
ALTER TABLE `stock_opname_his`
  ADD PRIMARY KEY (`id_stck_ophis`),
  ADD KEY `id_wrh` (`id_wrh`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_stck_op` (`id_stck_op`);

--
-- Indexes for table `tdos`
--
ALTER TABLE `tdos`
  ADD PRIMARY KEY (`id_do`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `id_po` (`id_po`);

--
-- Indexes for table `tpos`
--
ALTER TABLE `tpos`
  ADD PRIMARY KEY (`id_po`),
  ADD KEY `id_cust` (`id_cust`),
  ADD KEY `id_supplier` (`id_supplier`);

--
-- Indexes for table `tpo_detail`
--
ALTER TABLE `tpo_detail`
  ADD PRIMARY KEY (`id_po_dtl`),
  ADD KEY `id_po` (`id_po`);

--
-- Indexes for table `t_invoice_d`
--
ALTER TABLE `t_invoice_d`
  ADD PRIMARY KEY (`id_invoice_d`),
  ADD KEY `id_hid` (`id_inv_h`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_inv_h` (`id_inv_h`);

--
-- Indexes for table `t_invoice_h`
--
ALTER TABLE `t_invoice_h`
  ADD PRIMARY KEY (`id_inv_h`),
  ADD KEY `id_cust` (`id_cust`);

--
-- Indexes for table `t_stck_op`
--
ALTER TABLE `t_stck_op`
  ADD PRIMARY KEY (`id_stck_op`),
  ADD KEY `id_wrh` (`id_wrh`),
  ADD KEY `id_product` (`id_product`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `do_transfer_h`
--
ALTER TABLE `do_transfer_h`
  MODIFY `id_do_transfer` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mproduct`
--
ALTER TABLE `mproduct`
  MODIFY `id_product` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mproduct_type`
--
ALTER TABLE `mproduct_type`
  MODIFY `id_type` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mproduct_unit`
--
ALTER TABLE `mproduct_unit`
  MODIFY `id_unit` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_customers`
--
ALTER TABLE `m_customers`
  MODIFY `id_cust` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_suppliers`
--
ALTER TABLE `m_suppliers`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_werehouses`
--
ALTER TABLE `m_werehouses`
  MODIFY `id_wrh` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po_his`
--
ALTER TABLE `po_his`
  MODIFY `id_po_his` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_request`
--
ALTER TABLE `purchase_request`
  MODIFY `id_pur_req` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_request_detail`
--
ALTER TABLE `purchase_request_detail`
  MODIFY `id_purchase_req_dtl` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_mutation`
--
ALTER TABLE `stock_mutation`
  MODIFY `id_stock_mut` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_opname_his`
--
ALTER TABLE `stock_opname_his`
  MODIFY `id_stck_ophis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tdos`
--
ALTER TABLE `tdos`
  MODIFY `id_do` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tpos`
--
ALTER TABLE `tpos`
  MODIFY `id_po` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tpo_detail`
--
ALTER TABLE `tpo_detail`
  MODIFY `id_po_dtl` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_invoice_d`
--
ALTER TABLE `t_invoice_d`
  MODIFY `id_invoice_d` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_invoice_h`
--
ALTER TABLE `t_invoice_h`
  MODIFY `id_inv_h` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_stck_op`
--
ALTER TABLE `t_stck_op`
  MODIFY `id_stck_op` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `do_transfer_h`
--
ALTER TABLE `do_transfer_h`
  ADD CONSTRAINT `do_transfer_h_ibfk_1` FOREIGN KEY (`id_do`) REFERENCES `tdos` (`id_do`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `do_transfer_h_ibfk_2` FOREIGN KEY (`id_wrh`) REFERENCES `m_werehouses` (`id_wrh`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mproduct`
--
ALTER TABLE `mproduct`
  ADD CONSTRAINT `mproduct_ibfk_1` FOREIGN KEY (`id_type`) REFERENCES `mproduct_type` (`id_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mproduct_ibfk_2` FOREIGN KEY (`id_unit`) REFERENCES `mproduct_unit` (`id_unit`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `po_his`
--
ALTER TABLE `po_his`
  ADD CONSTRAINT `po_his_ibfk_2` FOREIGN KEY (`id_po_dtl`) REFERENCES `tpo_detail` (`id_po_dtl`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_request`
--
ALTER TABLE `purchase_request`
  ADD CONSTRAINT `purchase_request_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mproduct` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_request_ibfk_2` FOREIGN KEY (`id_wrh`) REFERENCES `m_werehouses` (`id_wrh`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_request_ibfk_3` FOREIGN KEY (`id_user_req`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `purchase_request_detail`
--
ALTER TABLE `purchase_request_detail`
  ADD CONSTRAINT `purchase_request_detail_ibfk_2` FOREIGN KEY (`id_purchase`) REFERENCES `purchase_request` (`id_pur_req`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_mutation`
--
ALTER TABLE `stock_mutation`
  ADD CONSTRAINT `stock_mutation_ibfk_1` FOREIGN KEY (`id_wrh`) REFERENCES `m_werehouses` (`id_wrh`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_mutation_ibfk_2` FOREIGN KEY (`id_product`) REFERENCES `mproduct` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `stock_opname_his`
--
ALTER TABLE `stock_opname_his`
  ADD CONSTRAINT `stock_opname_his_ibfk_3` FOREIGN KEY (`id_stck_op`) REFERENCES `t_stck_op` (`id_stck_op`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tdos`
--
ALTER TABLE `tdos`
  ADD CONSTRAINT `tdos_ibfk_1` FOREIGN KEY (`id_po`) REFERENCES `tpos` (`id_po`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tdos_ibfk_2` FOREIGN KEY (`id_supplier`) REFERENCES `m_suppliers` (`id_supplier`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tpos`
--
ALTER TABLE `tpos`
  ADD CONSTRAINT `tpos_ibfk_2` FOREIGN KEY (`id_supplier`) REFERENCES `m_suppliers` (`id_supplier`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tpo_detail`
--
ALTER TABLE `tpo_detail`
  ADD CONSTRAINT `tpo_detail_ibfk_1` FOREIGN KEY (`id_po`) REFERENCES `tpos` (`id_po`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t_invoice_d`
--
ALTER TABLE `t_invoice_d`
  ADD CONSTRAINT `t_invoice_d_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `mproduct` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t_invoice_d_ibfk_2` FOREIGN KEY (`id_inv_h`) REFERENCES `t_invoice_h` (`id_inv_h`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t_invoice_h`
--
ALTER TABLE `t_invoice_h`
  ADD CONSTRAINT `t_invoice_h_ibfk_1` FOREIGN KEY (`id_cust`) REFERENCES `m_customers` (`id_cust`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t_stck_op`
--
ALTER TABLE `t_stck_op`
  ADD CONSTRAINT `t_stck_op_ibfk_1` FOREIGN KEY (`id_wrh`) REFERENCES `m_werehouses` (`id_wrh`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t_stck_op_ibfk_2` FOREIGN KEY (`id_product`) REFERENCES `mproduct` (`id_product`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
