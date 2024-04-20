-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Apr 20, 2024 alle 18:44
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `progettoFabriLoi`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `blog`
--

CREATE TABLE `blog` (
  `id_blog` int(10) NOT NULL,
  `data_blog` datetime NOT NULL,
  `titolo_blog` varchar(50) NOT NULL,
  `descrizione` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_logo` varchar(50) NOT NULL DEFAULT 'default.png',
  `id_categoria` int(10) NOT NULL,
  `id_stile` int(10) NOT NULL,
  `id_proprietario` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(20) NOT NULL,
  `descrizione` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `commento`
--

CREATE TABLE `commento` (
  `id_comm` int(10) NOT NULL,
  `data_comm` datetime NOT NULL,
  `contenuto` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `id_utente` int(10) NOT NULL,
  `id_post` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `commento`
--
DELIMITER $$
CREATE TRIGGER `update_comment_count` AFTER INSERT ON `commento` FOR EACH ROW UPDATE post
SET comments = comments + 1
WHERE post.id = post_id
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `co_autore`
--

CREATE TABLE `co_autore` (
  `id_utente` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `follow`
--

CREATE TABLE `follow` (
  `id_follow` int(10) NOT NULL,
  `id_utente` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL,
  `data_follow` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `follow`
--
DELIMITER $$
CREATE TRIGGER `after_follow_insert` AFTER INSERT ON `follow` FOR EACH ROW BEGIN
   UPDATE `blog`
   SET `followers_count` = `followers_count` + 1
   WHERE `id_blog` = NEW.`id_blog`;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `like`
--

CREATE TABLE `like` (
  `id_like` int(10) NOT NULL,
  `id_utente` int(10) NOT NULL,
  `id_post` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `like`
--
DELIMITER $$
CREATE TRIGGER `after_like_insert` AFTER INSERT ON `like` FOR EACH ROW BEGIN
   UPDATE `post`
   SET `likes_count` = `likes_count` + 1
   WHERE `id_post` = NEW.`id_post`;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `post`
--

CREATE TABLE `post` (
  `id_post` int(10) NOT NULL,
  `data_post` datetime NOT NULL,
  `titolo_post` varchar(50) NOT NULL,
  `descrizione_post` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_post` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'default.png',
  `id_autore` int(10) NOT NULL,
  `id_sotcat` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL,
  `likes_count` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sottocat`
--

CREATE TABLE `sottocat` (
  `id_sottocat` int(10) NOT NULL,
  `titolo` varchar(20) NOT NULL,
  `id_categoria` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `stile`
--

CREATE TABLE `stile` (
  `id_stile` int(10) NOT NULL,
  `nome` varchar(20) NOT NULL,
  `font` varchar(20) NOT NULL,
  `colore_testo` varchar(10) NOT NULL,
  `background` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id_utente` int(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pw` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `img_profilo` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'default.png',
  `nome` varchar(20) NOT NULL,
  `cognome` varchar(20) NOT NULL,
  `genere` varchar(10) NOT NULL,
  `data_nascita` date NOT NULL,
  `bio` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `numero_telefono` varchar(10) NOT NULL,
  `premium` tinyint(1) NOT NULL DEFAULT 0,
  `intestatario` varchar(40) NOT NULL,
  `carta` varchar(16) NOT NULL,
  `data_scadenza` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id_blog`),
  ADD UNIQUE KEY `id_blog` (`id_blog`,`titolo_blog`),
  ADD UNIQUE KEY `titolo_blog` (`titolo_blog`,`id_proprietario`),
  ADD KEY `id_categoria` (`id_categoria`,`id_stile`,`id_proprietario`),
  ADD KEY `id_proprietario` (`id_proprietario`),
  ADD KEY `blog_ibfk_2` (`id_stile`);

--
-- Indici per le tabelle `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indici per le tabelle `commento`
--
ALTER TABLE `commento`
  ADD PRIMARY KEY (`id_comm`),
  ADD KEY `id_utente` (`id_utente`,`id_post`),
  ADD KEY `id_post` (`id_post`);

--
-- Indici per le tabelle `co_autore`
--
ALTER TABLE `co_autore`
  ADD PRIMARY KEY (`id_utente`,`id_blog`),
  ADD KEY `id_utente` (`id_utente`,`id_blog`),
  ADD KEY `id_blog` (`id_blog`);

--
-- Indici per le tabelle `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`id_follow`),
  ADD UNIQUE KEY `id_utente` (`id_utente`,`id_blog`),
  ADD KEY `id_follow` (`id_follow`,`id_utente`,`id_blog`);

--
-- Indici per le tabelle `like`
--
ALTER TABLE `like`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `id_utente_2` (`id_utente`,`id_post`),
  ADD KEY `id_utente` (`id_utente`,`id_post`),
  ADD KEY `id_post` (`id_post`);

--
-- Indici per le tabelle `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `id_autore` (`id_autore`,`id_sotcat`,`id_blog`),
  ADD KEY `id_blog` (`id_blog`),
  ADD KEY `id_sotcat` (`id_sotcat`);

--
-- Indici per le tabelle `sottocat`
--
ALTER TABLE `sottocat`
  ADD PRIMARY KEY (`id_sottocat`),
  ADD KEY `id_post` (`id_categoria`) USING BTREE,
  ADD KEY `titolo` (`titolo`);

--
-- Indici per le tabelle `stile`
--
ALTER TABLE `stile`
  ADD PRIMARY KEY (`id_stile`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`id_utente`),
  ADD UNIQUE KEY `username` (`username`,`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `blog`
--
ALTER TABLE `blog`
  MODIFY `id_blog` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `commento`
--
ALTER TABLE `commento`
  MODIFY `id_comm` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `like`
--
ALTER TABLE `like`
  MODIFY `id_like` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `post`
--
ALTER TABLE `post`
  MODIFY `id_post` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `sottocat`
--
ALTER TABLE `sottocat`
  MODIFY `id_sottocat` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `stile`
--
ALTER TABLE `stile`
  MODIFY `id_stile` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id_utente` int(10) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  ADD CONSTRAINT `blog_ibfk_2` FOREIGN KEY (`id_stile`) REFERENCES `stile` (`id_stile`),
  ADD CONSTRAINT `blog_ibfk_3` FOREIGN KEY (`id_proprietario`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_ibfk_4` FOREIGN KEY (`id_blog`) REFERENCES `co_autore` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `commento`
--
ALTER TABLE `commento`
  ADD CONSTRAINT `commento_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `commento_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `commento_ibfk_3` FOREIGN KEY (`id_comm`) REFERENCES `notifica` (`id_notifica`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_ibfk_1` FOREIGN KEY (`id_follow`) REFERENCES `blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `follow_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `like`
--
ALTER TABLE `like`
  ADD CONSTRAINT `like_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `like_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `like_ibfk_3` FOREIGN KEY (`id_like`) REFERENCES `notifica` (`id_notifica`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `post_ibfk_2` FOREIGN KEY (`id_autore`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `post_ibfk_3` FOREIGN KEY (`id_sotcat`) REFERENCES `sottocat` (`id_sottocat`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Limiti per la tabella `sottocat`
--
ALTER TABLE `sottocat`
  ADD CONSTRAINT `sottocat_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `utente`
--
ALTER TABLE `utente`
  ADD CONSTRAINT `utente_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `co_autore` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
