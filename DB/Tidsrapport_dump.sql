/*
SQLyog Community
MySQL - 5.7.36 : Database - tidsrapport
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `kategorier` */

CREATE TABLE `kategorier` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Kategori` varchar(30) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UIX_kategori` (`Kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `kategorier` */

insert  into `kategorier`(`ID`,`Kategori`) values 
(2,'Html'),
(1,'Java');

/*Table structure for table `uppgifter` */

CREATE TABLE `uppgifter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Tid` time NOT NULL COMMENT 'Min 00:05 Max 8:00',
  `Datum` date NOT NULL,
  `KategoriId` int(11) NOT NULL,
  `Beskrivning` varchar(255) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `uppgifter_ibfk_1` (`KategoriId`),
  CONSTRAINT `uppgifter_ibfk_1` FOREIGN KEY (`KategoriId`) REFERENCES `kategorier` (`ID`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `uppgifter` */

insert  into `uppgifter`(`ID`,`Tid`,`Datum`,`KategoriId`,`Beskrivning`) values 
(1,'12:03:00','2023-01-19',1,'Bra'),
(2,'11:05:00','2022-12-27',2,'Va'),
(3,'23:00:00','2022-12-28',1,'Bra'),
(4,'12:00:00','2022-12-29',2,'K'),
(5,'11:05:00','2022-12-30',1,'ok'),
(6,'13:46:00','2022-12-31',2,'Helt ok');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
