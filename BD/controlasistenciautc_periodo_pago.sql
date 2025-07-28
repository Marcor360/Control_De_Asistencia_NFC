-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
--
-- Host: localhost    Database: controlasistenciautc
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `periodo_pago`
--

DROP TABLE IF EXISTS `periodo_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodo_pago` (
  `id_periodo` int NOT NULL AUTO_INCREMENT,
  `mes_año` varchar(10) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estatus` enum('Activo','Cerrado') DEFAULT 'Activo',
  PRIMARY KEY (`id_periodo`),
  UNIQUE KEY `mes_año` (`mes_año`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodo_pago`
--

LOCK TABLES `periodo_pago` WRITE;
/*!40000 ALTER TABLE `periodo_pago` DISABLE KEYS */;
INSERT INTO `periodo_pago` VALUES (1,'ENE-2025','2025-01-01','2025-01-31','Cerrado'),(2,'FEB-2025','2025-02-01','2025-02-28','Cerrado'),(3,'MAR-2025','2025-03-01','2025-03-31','Cerrado'),(4,'ABR-2025','2025-04-01','2025-04-30','Cerrado'),(5,'MAY-2025','2025-05-01','2025-05-31','Cerrado'),(6,'JUN-2025','2025-06-01','2025-06-30','Cerrado'),(7,'JUL-2025','2025-07-01','2025-07-31','Cerrado'),(8,'AGO-2025','2025-08-01','2025-08-31','Cerrado'),(9,'SEP-2025','2025-09-01','2025-09-30','Cerrado'),(10,'OCT-2025','2025-10-01','2025-10-31','Cerrado'),(11,'NOV-2025','2025-11-01','2025-11-30','Activo'),(12,'DIC-2025','2025-12-01','2025-12-31','Activo');
/*!40000 ALTER TABLE `periodo_pago` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-28  0:29:13
