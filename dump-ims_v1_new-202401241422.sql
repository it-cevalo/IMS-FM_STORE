-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: ims_v1_new
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `do_his`
--

DROP TABLE IF EXISTS `do_his`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_his` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tgl_do` datetime NOT NULL,
  `id_do` varchar(11)  NOT NULL,
  `id_po` varchar(11)  NOT NULL,
  `no_do` varchar(30)  NOT NULL,
  `code_cust` varchar(60)  NOT NULL,
  `nama_cust` varchar(60)  NOT NULL,
  `reason_do` varchar(100)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_supplier` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `do_his`
--

LOCK TABLES `do_his` WRITE;
/*!40000 ALTER TABLE `do_his` DISABLE KEYS */;
/*!40000 ALTER TABLE `do_his` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `do_transfer_d`
--

DROP TABLE IF EXISTS `do_transfer_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_transfer_d` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_product_trf_h` int DEFAULT NULL,
  `id_product` int DEFAULT NULL,
  `qty_prd` int DEFAULT NULL,
  `desc_prd` varchar(255)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `do_transfer_d`
--

LOCK TABLES `do_transfer_d` WRITE;
/*!40000 ALTER TABLE `do_transfer_d` DISABLE KEYS */;
/*!40000 ALTER TABLE `do_transfer_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `do_transfer_h`
--

DROP TABLE IF EXISTS `do_transfer_h`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `do_transfer_h` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_trf` varchar(30) DEFAULT NULL,
  `tgl_trf` date DEFAULT NULL,
  `id_warehouse_from` int DEFAULT NULL,
  `id_warehouse_to` int DEFAULT NULL,
  `desc_trf` varchar(255) DEFAULT NULL,
  `total_qty_trf` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `do_transfer_h`
--

LOCK TABLES `do_transfer_h` WRITE;
/*!40000 ALTER TABLE `do_transfer_h` DISABLE KEYS */;
/*!40000 ALTER TABLE `do_transfer_h` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255)  NOT NULL,
  `connection` text  NOT NULL,
  `queue` text  NOT NULL,
  `payload` longtext  NOT NULL,
  `exception` longtext  NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inv_his`
--

DROP TABLE IF EXISTS `inv_his`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inv_his` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_inv` int DEFAULT NULL,
  `tgl_inv` datetime DEFAULT NULL,
  `no_inv` varchar(30)  DEFAULT NULL,
  `no_seri_pajak` varchar(30)  DEFAULT NULL,
  `shipping_via` varchar(100)  DEFAULT NULL,
  `status_faktur_pajak` varchar(10)  DEFAULT NULL,
  `reason_inv` varchar(100)  DEFAULT NULL,
  `reason_faktur_pajak` varchar(100)  DEFAULT NULL,
  `grand_total` varchar(100)  DEFAULT NULL,
  `term` varchar(100)  DEFAULT NULL,
  `code_cust` varchar(60)  DEFAULT NULL,
  `status_inv` varchar(10)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inv_his`
--

LOCK TABLES `inv_his` WRITE;
/*!40000 ALTER TABLE `inv_his` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_his` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inv_rcp_his`
--

DROP TABLE IF EXISTS `inv_rcp_his`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inv_rcp_his` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_inv_rcp` varchar(11)  NOT NULL,
  `id_inv` varchar(11)  NOT NULL,
  `tgl_inv` datetime DEFAULT NULL,
  `no_inv` varchar(30)  DEFAULT NULL,
  `no_tti` varchar(30)  DEFAULT NULL,
  `no_seri_pajak` varchar(30)  DEFAULT NULL,
  `shipping_via` varchar(100)  DEFAULT NULL,
  `grand_total` varchar(100)  DEFAULT NULL,
  `code_courier` varchar(30)  DEFAULT NULL,
  `term` varchar(100)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inv_rcp_his`
--

LOCK TABLES `inv_rcp_his` WRITE;
/*!40000 ALTER TABLE `inv_rcp_his` DISABLE KEYS */;
/*!40000 ALTER TABLE `inv_rcp_his` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m_couriers`
--

DROP TABLE IF EXISTS `m_couriers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_couriers` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `code_courier` varchar(60)  NOT NULL,
  `nama_courier` varchar(60)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`code_courier`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m_couriers`
--

LOCK TABLES `m_couriers` WRITE;
/*!40000 ALTER TABLE `m_couriers` DISABLE KEYS */;
/*!40000 ALTER TABLE `m_couriers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m_customers`
--

DROP TABLE IF EXISTS `m_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_customers` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `code_cust` varchar(60)  NOT NULL,
  `nama_cust` varchar(60)  NOT NULL,
  `tgl_cust` date NOT NULL,
  `phone` varchar(13)  DEFAULT NULL,
  `email` varchar(100)  DEFAULT NULL,
  `npwp_cust` varchar(25)  DEFAULT NULL,
  `address_cust` varchar(255)  DEFAULT NULL,
  `address_npwp` varchar(255)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`code_cust`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m_customers`
--

LOCK TABLES `m_customers` WRITE;
/*!40000 ALTER TABLE `m_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `m_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m_suppliers`
--

DROP TABLE IF EXISTS `m_suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_suppliers` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `code_spl` varchar(60)  NOT NULL,
  `nama_spl` varchar(60)  NOT NULL,
  `tgl_spl` date NOT NULL,
  `phone` varchar(13)  DEFAULT NULL,
  `email` varchar(100)  DEFAULT NULL,
  `npwp_spl` varchar(25)  DEFAULT NULL,
  `address_spl` varchar(255)  DEFAULT NULL,
  `address_npwp` varchar(255)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`code_spl`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m_suppliers`
--

LOCK TABLES `m_suppliers` WRITE;
/*!40000 ALTER TABLE `m_suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `m_suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `m_warehouses`
--

DROP TABLE IF EXISTS `m_warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_warehouses` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `code_wh` varchar(60)  NOT NULL,
  `nama_wh` varchar(60)  NOT NULL,
  `phone` varchar(13)  DEFAULT NULL,
  `email` varchar(100)  DEFAULT NULL,
  `address` varchar(255)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`code_wh`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `m_warehouses`
--

LOCK TABLES `m_warehouses` WRITE;
/*!40000 ALTER TABLE `m_warehouses` DISABLE KEYS */;
/*!40000 ALTER TABLE `m_warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255)  NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (2,'2023_11_24_194943_create_mproduct_type_table',1),(3,'2023_11_26_223644_create_mproduct_unit_table',2),(4,'2024_01_20_105946_create_stock_mutation_table',3),(5,'2024_01_20_122735_alter_do_transfer_h_table',4),(6,'2024_01_20_122742_alter_do_transfer_d_table',5),(7,'2024_01_20_132221_alter_stock_mutation_table',6),(8,'2024_01_20_171943_create_t_invoice_h_table',7),(9,'2024_01_20_171802_create_t_invoice_d_table',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255)  NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`) USING BTREE,
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`) USING BTREE,
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255)  NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`) USING BTREE,
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`) USING BTREE,
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mproduct`
--

DROP TABLE IF EXISTS `mproduct`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mproduct` (
  `id` int NOT NULL AUTO_INCREMENT,
  `SKU` varchar(25)  NOT NULL,
  `nama_barang` varchar(50)  NOT NULL,
  `id_unit` int unsigned DEFAULT NULL,
  `id_type` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `harga_beli` int DEFAULT NULL,
  `harga_jual` int DEFAULT NULL,
  `harga_rata_rata` int DEFAULT NULL,
  `flag_active` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mproduct`
--

LOCK TABLES `mproduct` WRITE;
/*!40000 ALTER TABLE `mproduct` DISABLE KEYS */;
/*!40000 ALTER TABLE `mproduct` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mproduct_stock`
--

DROP TABLE IF EXISTS `mproduct_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mproduct_stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_warehouse` int DEFAULT NULL,
  `id_product` int DEFAULT NULL,
  `qty_last` int DEFAULT NULL,
  `tgl_opname` date DEFAULT NULL,
  `tgl_mutasi` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mproduct_stock`
--

LOCK TABLES `mproduct_stock` WRITE;
/*!40000 ALTER TABLE `mproduct_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `mproduct_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mproduct_type`
--

DROP TABLE IF EXISTS `mproduct_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mproduct_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_tipe` varchar(25)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mproduct_type`
--

LOCK TABLES `mproduct_type` WRITE;
/*!40000 ALTER TABLE `mproduct_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `mproduct_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mproduct_unit`
--

DROP TABLE IF EXISTS `mproduct_unit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mproduct_unit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_unit` varchar(25)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mproduct_unit`
--

LOCK TABLES `mproduct_unit` WRITE;
/*!40000 ALTER TABLE `mproduct_unit` DISABLE KEYS */;
/*!40000 ALTER TABLE `mproduct_unit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255)  NOT NULL,
  `token` varchar(255)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NOT NULL,
  `guard_name` varchar(255)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'role-list','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(2,'role-create','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(3,'role-edit','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(4,'role-delete','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(5,'customer-list','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(6,'customer-create','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(7,'customer-edit','web','2022-07-07 04:27:59','2022-07-07 04:27:59'),(8,'customer-delete','web','2022-07-07 04:27:59','2022-07-07 04:27:59');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `po_his`
--

DROP TABLE IF EXISTS `po_his`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `po_his` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_po` int NOT NULL,
  `id_po_dtl` int DEFAULT NULL,
  `tgl_po` datetime NOT NULL,
  `id_cust` varchar(11)  DEFAULT NULL,
  `nama_cust` varchar(60)  DEFAULT NULL,
  `code_cust` varchar(60)  DEFAULT NULL,
  `no_po` varchar(30)  NOT NULL,
  `no_so` varchar(30)  NOT NULL,
  `reason_po` varchar(100)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `code_spl` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_spl` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `po_his`
--

LOCK TABLES `po_his` WRITE;
/*!40000 ALTER TABLE `po_his` DISABLE KEYS */;
/*!40000 ALTER TABLE `po_his` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`) USING BTREE,
  KEY `role_has_permissions_role_id_foreign` (`role_id`) USING BTREE,
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NOT NULL,
  `guard_name` varchar(255)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','web','2022-07-07 04:26:57','2022-07-07 04:26:57'),(2,'User','web','2022-10-04 12:34:10','2022-10-04 12:34:12');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_mutation`
--

DROP TABLE IF EXISTS `stock_mutation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_mutation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_product` int NOT NULL,
  `id_warehouse` int NOT NULL,
  `qty_start` int NOT NULL,
  `qty_in` int NOT NULL,
  `qty_out` int NOT NULL,
  `tgl_mutasi` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `qty_last` int NOT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_mutation`
--

LOCK TABLES `stock_mutation` WRITE;
/*!40000 ALTER TABLE `stock_mutation` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_mutation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_opname_his`
--

DROP TABLE IF EXISTS `stock_opname_his`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_opname_his` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_warehouse` int DEFAULT NULL,
  `id_product` int DEFAULT NULL,
  `qty_in` int DEFAULT NULL,
  `qty_last` int DEFAULT NULL,
  `qty_out` int DEFAULT NULL,
  `tgl_opname` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `id_stock_opname` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_opname_his`
--

LOCK TABLES `stock_opname_his` WRITE;
/*!40000 ALTER TABLE `stock_opname_his` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_opname_his` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_invoice_d`
--

DROP TABLE IF EXISTS `t_invoice_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_invoice_d` (
  `id` int NOT NULL AUTO_INCREMENT,
  `hid` int NOT NULL,
  `id_product` int NOT NULL,
  `SKU` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_inv` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_inv` date NOT NULL,
  `qty` int NOT NULL,
  `price` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_invoice_d`
--

LOCK TABLES `t_invoice_d` WRITE;
/*!40000 ALTER TABLE `t_invoice_d` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_invoice_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_invoice_h`
--

DROP TABLE IF EXISTS `t_invoice_h`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_invoice_h` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_inv` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_cust` int NOT NULL,
  `tgl_inv` date NOT NULL,
  `grand_total` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `id_cust` int NOT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_invoice_h`
--

LOCK TABLES `t_invoice_h` WRITE;
/*!40000 ALTER TABLE `t_invoice_h` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_invoice_h` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t_stock_opname`
--

DROP TABLE IF EXISTS `t_stock_opname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_stock_opname` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_warehouse` int DEFAULT NULL,
  `id_product` int DEFAULT NULL,
  `qty_in` int DEFAULT NULL,
  `qty_last` int DEFAULT NULL,
  `qty_out` int DEFAULT NULL,
  `tgl_opname` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t_stock_opname`
--

LOCK TABLES `t_stock_opname` WRITE;
/*!40000 ALTER TABLE `t_stock_opname` DISABLE KEYS */;
/*!40000 ALTER TABLE `t_stock_opname` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tdos`
--

DROP TABLE IF EXISTS `tdos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tdos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tgl_do` datetime NOT NULL,
  `tgl_po` datetime NOT NULL,
  `id_po` int DEFAULT NULL,
  `code_cust` varchar(60)  NOT NULL,
  `nama_cust` varchar(60)  NOT NULL,
  `no_po` varchar(30)  NOT NULL,
  `no_so` varchar(30)  NOT NULL,
  `no_do` varchar(30)  DEFAULT NULL,
  `shipping_via` varchar(100)  NOT NULL,
  `status_lmpr_do` varchar(10)  NOT NULL,
  `reason_do` varchar(100)  NOT NULL,
  `file` varchar(50)  DEFAULT NULL,
  `upload_date_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `id_supplier` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tdos`
--

LOCK TABLES `tdos` WRITE;
/*!40000 ALTER TABLE `tdos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tdos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tinv_rcp`
--

DROP TABLE IF EXISTS `tinv_rcp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tinv_rcp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tgl_inv` datetime DEFAULT NULL,
  `tgl_inv_rcp` datetime DEFAULT NULL,
  `id_inv` int unsigned DEFAULT NULL,
  `code_cust` varchar(60)  DEFAULT NULL,
  `no_inv` varchar(30)  DEFAULT NULL,
  `no_tti` varchar(30)  DEFAULT NULL,
  `no_seri_pajak` varchar(30)  DEFAULT NULL,
  `grand_total` varchar(100)  DEFAULT NULL,
  `term` varchar(100)  DEFAULT NULL,
  `shipping_via` varchar(100)  DEFAULT NULL,
  `code_courier` varchar(60)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tinv_rcp`
--

LOCK TABLES `tinv_rcp` WRITE;
/*!40000 ALTER TABLE `tinv_rcp` DISABLE KEYS */;
/*!40000 ALTER TABLE `tinv_rcp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tinv_send`
--

DROP TABLE IF EXISTS `tinv_send`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tinv_send` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_inv_rcp` varchar(11)  NOT NULL,
  `tgl_inv_rcp` datetime DEFAULT NULL,
  `no_inv` varchar(30)  NOT NULL,
  `no_tti` varchar(30)  NOT NULL,
  `bukti_tanda_terima` varchar(255)  NOT NULL,
  `no_resi` varchar(30)  NOT NULL,
  `code_courier` varchar(60)  NOT NULL,
  `code_cust` varchar(60)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tinv_send`
--

LOCK TABLES `tinv_send` WRITE;
/*!40000 ALTER TABLE `tinv_send` DISABLE KEYS */;
/*!40000 ALTER TABLE `tinv_send` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tinvs`
--

DROP TABLE IF EXISTS `tinvs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tinvs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_po` int DEFAULT NULL,
  `id_do` int NOT NULL,
  `tgl_inv` datetime NOT NULL,
  `code_cust` varchar(60)  NOT NULL,
  `no_inv` varchar(30)  NOT NULL,
  `no_seri_pajak` varchar(30)  NOT NULL,
  `status_faktur_pajak` varchar(10)  NOT NULL,
  `reason_inv` varchar(100)  NOT NULL,
  `reason_faktur_pajak` varchar(100)  NOT NULL,
  `grand_total` varchar(100)  NOT NULL,
  `term` varchar(100)  NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `shipping_via` varchar(100)  DEFAULT NULL,
  `status_inv` varchar(10)  DEFAULT NULL,
  `signed` longtext ,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tinvs`
--

LOCK TABLES `tinvs` WRITE;
/*!40000 ALTER TABLE `tinvs` DISABLE KEYS */;
/*!40000 ALTER TABLE `tinvs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpayment`
--

DROP TABLE IF EXISTS `tpayment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpayment` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_tax_inv` int DEFAULT NULL,
  `code_cust` varchar(60)  DEFAULT NULL,
  `no_tax_inv` varchar(60)  DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `bank_account` varchar(20)  DEFAULT NULL,
  `payment_via` varchar(20)  DEFAULT NULL,
  `invoice_paid` varchar(100)  DEFAULT NULL,
  `amount_paid` varchar(100)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `id_cust` int NOT NULL,
  `no_po` varchar(30)  NOT NULL,
  `id_po` int NOT NULL,
  `id_do` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpayment`
--

LOCK TABLES `tpayment` WRITE;
/*!40000 ALTER TABLE `tpayment` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpayment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpayment_his`
--

DROP TABLE IF EXISTS `tpayment_his`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpayment_his` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_payment` varchar(11)  DEFAULT NULL,
  `invoice_paid` varchar(100)  DEFAULT NULL,
  `amount_paid` varchar(100)  DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpayment_his`
--

LOCK TABLES `tpayment_his` WRITE;
/*!40000 ALTER TABLE `tpayment_his` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpayment_his` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpo_detail`
--

DROP TABLE IF EXISTS `tpo_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpo_detail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_po` int unsigned DEFAULT NULL,
  `part_number` varchar(25)  DEFAULT NULL,
  `product_name` varchar(30)  DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `price` int unsigned DEFAULT NULL,
  `total_price` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpo_detail`
--

LOCK TABLES `tpo_detail` WRITE;
/*!40000 ALTER TABLE `tpo_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpo_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tpos`
--

DROP TABLE IF EXISTS `tpos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tpos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_cust` int DEFAULT NULL,
  `tgl_po` datetime DEFAULT NULL,
  `code_cust` varchar(60)  DEFAULT NULL,
  `nama_cust` varchar(60)  DEFAULT NULL,
  `no_po` varchar(30)  DEFAULT NULL,
  `no_so` varchar(30)  DEFAULT NULL,
  `status_po` varchar(10)  DEFAULT NULL,
  `reason_po` varchar(100)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `grand_total` varchar(100)  DEFAULT NULL,
  `id_supplier` int DEFAULT NULL,
  `code_spl` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_spl` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tpos`
--

LOCK TABLES `tpos` WRITE;
/*!40000 ALTER TABLE `tpos` DISABLE KEYS */;
/*!40000 ALTER TABLE `tpos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255)  NOT NULL,
  `username` varchar(100)  DEFAULT NULL,
  `email` varchar(255)  NOT NULL,
  `position` varchar(100)  DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255)  NOT NULL,
  `remember_token` varchar(100)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `users_email_unique` (`email`) USING BTREE
) ;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (6,'superadmin','sa','sa@gmail.com','SUPERADMIN',NULL,'$2y$10$LWrB0byRHxCqymhAk81WxexV7Yk.64KjVSajDdGBcPl3G01j5Z5fq',NULL,'2023-02-15 15:44:26','2023-02-15 15:44:29');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ims_v1_new'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-01-24 14:22:03
