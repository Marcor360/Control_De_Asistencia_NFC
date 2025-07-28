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

-- Dump completed on 2025-07-28  0:29:14
