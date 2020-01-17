-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           10.3.15-MariaDB - mariadb.org binary distribution
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Copiando estrutura do banco de dados para mosyle_challenge
CREATE DATABASE IF NOT EXISTS `mosyle_challenge` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `mosyle_challenge`;

-- Copiando estrutura para tabela mosyle_challenge.users
CREATE TABLE IF NOT EXISTS `users` (
	`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(600) NOT NULL,
	`email` varchar(255) NOT NULL,
	`password` varchar(255) NOT NULL,
	`token` varchar(13) DEFAULT NULL,
	`created_at` timestamp NULL DEFAULT current_timestamp(),
	`updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	PRIMARY KEY (`id`),
	FULLTEXT KEY `name_email` (`name`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

-- Exportação de dados foi desmarcado.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
