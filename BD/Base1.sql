CREATE DATABASE  IF NOT EXISTS `controlasistenciautc` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `controlasistenciautc`;
-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: controlasistenciautc
-- ------------------------------------------------------
-- Server version	8.0.40

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
-- Table structure for table `alumno`
--

DROP TABLE IF EXISTS `alumno`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alumno` (
  `id_alumno` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `carrera` varchar(100) NOT NULL,
  `estatus_pago` enum('Al corriente','Pendiente','Bloqueado') DEFAULT 'Al corriente',
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  PRIMARY KEY (`id_alumno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumno`
--

LOCK TABLES `alumno` WRITE;
/*!40000 ALTER TABLE `alumno` DISABLE KEYS */;
/*!40000 ALTER TABLE `alumno` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistencia` (
  `id_asistencia` int NOT NULL AUTO_INCREMENT,
  `id_tarjeta` int NOT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `tipo_asistencia` enum('Presente','Retardo','Ausente') DEFAULT 'Ausente',
  `acceso_permitido` tinyint(1) DEFAULT '1',
  `observaciones` text,
  PRIMARY KEY (`id_asistencia`),
  KEY `idx_asistencia_tarjeta` (`id_tarjeta`),
  KEY `idx_asistencia_fecha` (`fecha`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjeta_nfc` (`id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asistencia`
--

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;
/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `configuracion` (
  `id_config` int NOT NULL AUTO_INCREMENT,
  `parametro` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL,
  `descripcion` text,
  `fecha_modificacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_usuario_modifica` int DEFAULT NULL,
  PRIMARY KEY (`id_config`),
  UNIQUE KEY `parametro` (`parametro`),
  KEY `id_usuario_modifica` (`id_usuario_modifica`),
  CONSTRAINT `configuracion_ibfk_1` FOREIGN KEY (`id_usuario_modifica`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `configuracion`
--

LOCK TABLES `configuracion` WRITE;
/*!40000 ALTER TABLE `configuracion` DISABLE KEYS */;
INSERT INTO `configuracion` VALUES (1,'dias_gracia_pago','5','Días de gracia después del vencimiento antes de bloquear acceso','2025-03-27 21:17:17',NULL),(2,'registro_entrada_libre','true','Permite registro de entrada sin restricción de horario','2025-03-27 21:17:17',NULL),(3,'registro_salida_libre','true','Permite registro de salida sin restricción de horario','2025-03-27 21:17:17',NULL),(4,'monitoreo_asistencia','true','Activa el monitoreo de asistencia sin bloquear acceso','2025-03-27 21:17:17',NULL),(5,'bloqueo_automatico','true','Activar/desactivar bloqueo automático por falta de pago','2025-03-27 21:17:17',NULL);
/*!40000 ALTER TABLE `configuracion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `control_acceso`
--

DROP TABLE IF EXISTS `control_acceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `control_acceso` (
  `id_acceso` int NOT NULL AUTO_INCREMENT,
  `id_tarjeta` int NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `tipo_acceso` enum('Entrada','Salida') NOT NULL,
  `permitido` tinyint(1) NOT NULL DEFAULT '1',
  `motivo_rechazo` varchar(100) DEFAULT NULL,
  `dispositivo` varchar(50) DEFAULT NULL,
  `ubicacion` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_acceso`),
  KEY `idx_control_tarjeta` (`id_tarjeta`),
  KEY `idx_control_fecha` (`fecha_hora`),
  CONSTRAINT `control_acceso_ibfk_1` FOREIGN KEY (`id_tarjeta`) REFERENCES `tarjeta_nfc` (`id_tarjeta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `control_acceso`
--

LOCK TABLES `control_acceso` WRITE;
/*!40000 ALTER TABLE `control_acceso` DISABLE KEYS */;
/*!40000 ALTER TABLE `control_acceso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_reporte`
--

DROP TABLE IF EXISTS `detalle_reporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_reporte` (
  `id_detalle` int NOT NULL AUTO_INCREMENT,
  `id_reporte` int NOT NULL,
  `tipo_referencia` enum('Asistencia','Acceso','Pago') NOT NULL,
  `id_referencia` int NOT NULL,
  PRIMARY KEY (`id_detalle`),
  KEY `id_reporte` (`id_reporte`),
  CONSTRAINT `detalle_reporte_ibfk_1` FOREIGN KEY (`id_reporte`) REFERENCES `reporte` (`id_reporte`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_reporte`
--

LOCK TABLES `detalle_reporte` WRITE;
/*!40000 ALTER TABLE `detalle_reporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_reporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id_pago` int NOT NULL AUTO_INCREMENT,
  `id_alumno` int NOT NULL,
  `id_periodo` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `concepto` varchar(100) NOT NULL,
  `fecha_pago` datetime NOT NULL,
  `metodo_pago` enum('Efectivo','Tarjeta','Transferencia') NOT NULL,
  `comprobante` varchar(100) DEFAULT NULL,
  `estado_pago` enum('Pagado','Pendiente','Vencido') DEFAULT 'Pendiente',
  PRIMARY KEY (`id_pago`),
  KEY `idx_pagos_alumno` (`id_alumno`),
  KEY `idx_pagos_periodo` (`id_periodo`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `pagos_ibfk_2` FOREIGN KEY (`id_periodo`) REFERENCES `periodo_pago` (`id_periodo`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Table structure for table `reporte`
--

DROP TABLE IF EXISTS `reporte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reporte` (
  `id_reporte` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `fecha_generacion` datetime NOT NULL,
  `tipo_reporte` enum('Asistencia','Pagos','Accesos','General') NOT NULL,
  `periodo_inicio` date DEFAULT NULL,
  `periodo_fin` date DEFAULT NULL,
  `parametros` json DEFAULT NULL,
  `archivo_generado` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_reporte`),
  KEY `idx_reporte_usuario` (`id_usuario`),
  KEY `idx_reporte_tipo` (`tipo_reporte`),
  CONSTRAINT `reporte_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reporte`
--

LOCK TABLES `reporte` WRITE;
/*!40000 ALTER TABLE `reporte` DISABLE KEYS */;
/*!40000 ALTER TABLE `reporte` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tarjeta_nfc`
--

DROP TABLE IF EXISTS `tarjeta_nfc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tarjeta_nfc` (
  `id_tarjeta` int NOT NULL AUTO_INCREMENT,
  `codigo_nfc` varchar(50) NOT NULL,
  `estado` enum('Activa','Inactiva','Bloqueada','Perdida') DEFAULT 'Activa',
  `fecha_emision` date NOT NULL,
  `id_alumno` int DEFAULT NULL,
  PRIMARY KEY (`id_tarjeta`),
  UNIQUE KEY `codigo_nfc` (`codigo_nfc`),
  KEY `idx_tarjeta_alumno` (`id_alumno`),
  CONSTRAINT `tarjeta_nfc_ibfk_1` FOREIGN KEY (`id_alumno`) REFERENCES `alumno` (`id_alumno`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tarjeta_nfc`
--

LOCK TABLES `tarjeta_nfc` WRITE;
/*!40000 ALTER TABLE `tarjeta_nfc` DISABLE KEYS */;
/*!40000 ALTER TABLE `tarjeta_nfc` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(80) NOT NULL,
  `email` varchar(100) NOT NULL,
  `tipo_rol` enum('Administrador','Profesor','Coordinador') NOT NULL,
  `nombre_usuario` varchar(30) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `ultimo_acceso` datetime DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `nombre_usuario` (`nombre_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Admin','Sistema','admin@utc.edu.mx','Administrador','admin','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9',NULL);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `vista_accesos`
--

DROP TABLE IF EXISTS `vista_accesos`;
/*!50001 DROP VIEW IF EXISTS `vista_accesos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vista_accesos` AS SELECT 
 1 AS `id_acceso`,
 1 AS `fecha_hora`,
 1 AS `tipo_acceso`,
 1 AS `permitido`,
 1 AS `motivo_rechazo`,
 1 AS `codigo_nfc`,
 1 AS `nombre_completo`,
 1 AS `id_alumno`,
 1 AS `carrera`,
 1 AS `estatus_pago`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vista_asistencia`
--

DROP TABLE IF EXISTS `vista_asistencia`;
/*!50001 DROP VIEW IF EXISTS `vista_asistencia`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vista_asistencia` AS SELECT 
 1 AS `id_asistencia`,
 1 AS `codigo_nfc`,
 1 AS `id_alumno`,
 1 AS `nombre_completo`,
 1 AS `carrera`,
 1 AS `fecha`,
 1 AS `hora_entrada`,
 1 AS `hora_salida`,
 1 AS `tipo_asistencia`,
 1 AS `acceso_permitido`,
 1 AS `estatus_pago`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `vista_pagos`
--

DROP TABLE IF EXISTS `vista_pagos`;
/*!50001 DROP VIEW IF EXISTS `vista_pagos`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `vista_pagos` AS SELECT 
 1 AS `id_pago`,
 1 AS `nombre_completo`,
 1 AS `id_alumno`,
 1 AS `periodo`,
 1 AS `monto`,
 1 AS `concepto`,
 1 AS `fecha_pago`,
 1 AS `metodo_pago`,
 1 AS `estado_pago`,
 1 AS `estatus_general`*/;
SET character_set_client = @saved_cs_client;

--
-- Final view structure for view `vista_accesos`
--

/*!50001 DROP VIEW IF EXISTS `vista_accesos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_accesos` AS select `ca`.`id_acceso` AS `id_acceso`,`ca`.`fecha_hora` AS `fecha_hora`,`ca`.`tipo_acceso` AS `tipo_acceso`,`ca`.`permitido` AS `permitido`,`ca`.`motivo_rechazo` AS `motivo_rechazo`,`t`.`codigo_nfc` AS `codigo_nfc`,concat(`a`.`nombre`,' ',`a`.`apellidos`) AS `nombre_completo`,`a`.`id_alumno` AS `id_alumno`,`a`.`carrera` AS `carrera`,`a`.`estatus_pago` AS `estatus_pago` from ((`control_acceso` `ca` join `tarjeta_nfc` `t` on((`ca`.`id_tarjeta` = `t`.`id_tarjeta`))) join `alumno` `a` on((`t`.`id_alumno` = `a`.`id_alumno`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vista_asistencia`
--

/*!50001 DROP VIEW IF EXISTS `vista_asistencia`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_asistencia` AS select `a`.`id_asistencia` AS `id_asistencia`,`t`.`codigo_nfc` AS `codigo_nfc`,`al`.`id_alumno` AS `id_alumno`,concat(`al`.`nombre`,' ',`al`.`apellidos`) AS `nombre_completo`,`al`.`carrera` AS `carrera`,`a`.`fecha` AS `fecha`,`a`.`hora_entrada` AS `hora_entrada`,`a`.`hora_salida` AS `hora_salida`,`a`.`tipo_asistencia` AS `tipo_asistencia`,`a`.`acceso_permitido` AS `acceso_permitido`,`al`.`estatus_pago` AS `estatus_pago` from ((`asistencia` `a` join `tarjeta_nfc` `t` on((`a`.`id_tarjeta` = `t`.`id_tarjeta`))) join `alumno` `al` on((`t`.`id_alumno` = `al`.`id_alumno`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `vista_pagos`
--

/*!50001 DROP VIEW IF EXISTS `vista_pagos`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `vista_pagos` AS select `p`.`id_pago` AS `id_pago`,concat(`a`.`nombre`,' ',`a`.`apellidos`) AS `nombre_completo`,`a`.`id_alumno` AS `id_alumno`,`pp`.`mes_año` AS `periodo`,`p`.`monto` AS `monto`,`p`.`concepto` AS `concepto`,`p`.`fecha_pago` AS `fecha_pago`,`p`.`metodo_pago` AS `metodo_pago`,`p`.`estado_pago` AS `estado_pago`,`a`.`estatus_pago` AS `estatus_general` from ((`pagos` `p` join `alumno` `a` on((`p`.`id_alumno` = `a`.`id_alumno`))) join `periodo_pago` `pp` on((`p`.`id_periodo` = `pp`.`id_periodo`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-14 15:35:43
