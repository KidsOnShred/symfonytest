/*
SQLyog Community v12.01 (64 bit)
MySQL - 5.5.37-cll : Database - angeltes_main
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`angeltes_main` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `angeltes_main`;

/*Table structure for table `testusers` */

DROP TABLE IF EXISTS `testusers`;

CREATE TABLE `testusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Data for the table `testusers` */

insert  into `testusers`(`id`,`email`,`password`) values (1,'illingworth_tom@angelbc.com','test'),(2,'illingworth_tom@hotmail.com','f4d2d7ed329e82d0b11a95c6b672973b'),(3,'new@email.com','f4d2d7ed329e82d0b11a95c6b672973b'),(4,'test@test','f4d2d7ed329e82d0b11a95c6b672973b');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
