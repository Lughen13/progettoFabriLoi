-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Ago 07, 2024 alle 15:11
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `progettofabriloi`
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
  `id_proprietario` int(10) NOT NULL,
  `followers_count` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(20) NOT NULL,
  `nome_categoria` varchar(20) NOT NULL
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
  `data_follow` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
-- Struttura della tabella `likes`
--

CREATE TABLE `likes` (
  `id_like` int(10) NOT NULL,
  `id_utente` int(10) NOT NULL,
  `id_post` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `likes`
--
DELIMITER $$
CREATE TRIGGER `after_like_delete` AFTER DELETE ON `likes` FOR EACH ROW BEGIN
    UPDATE `post`
    SET `likes_count` = `likes_count` - 1
    WHERE `id_post` = OLD.`id_post`;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_like_insert` AFTER INSERT ON `likes` FOR EACH ROW BEGIN
   UPDATE `post`
   SET `likes_count` = `likes_count` + 1
   WHERE `id_post` = NEW.`id_post`;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struttura della tabella `notifiche`
--

CREATE TABLE `notifiche` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `sender_id` int(10) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `contenuto_id` int(10) NOT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Struttura della tabella `post`
--

CREATE TABLE `post` (
  `id_post` int(10) NOT NULL,
  `data_post` datetime NOT NULL,
  `titolo_post` varchar(100) NOT NULL,
  `descrizione_post` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_post` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'default.png',
  `id_autore` int(10) NOT NULL,
  `id_sottocat` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL,
  `likes_count` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `premium`
--

CREATE TABLE `premium` (
  `id_utente` int(10) NOT NULL,
  `intestatario` varchar(40) NOT NULL,
  `numero_carta` varchar(16) NOT NULL,
  `data_scadenza` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `sottocat`
--

CREATE TABLE `sottocat` (
  `id_sottocat` int(10) NOT NULL,
  `id_categoria` int(10) NOT NULL,
  `nome_sottocat` varchar(50) NOT NULL
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
  `numero_telefono` varchar(10) DEFAULT NULL,
  `premium` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id_blog`),
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
  ADD KEY `id_post` (`id_post`),
  ADD KEY `commento_ibfk_2` (`id_utente`);

--
-- Indici per le tabelle `co_autore`
--
ALTER TABLE `co_autore`
  ADD PRIMARY KEY (`id_utente`,`id_blog`),
  ADD KEY `id_blog` (`id_blog`);

--
-- Indici per le tabelle `follow`
--
ALTER TABLE `follow`
  ADD PRIMARY KEY (`id_follow`),
  ADD UNIQUE KEY `id_utente` (`id_utente`,`id_blog`),
  ADD KEY `id_follow` (`id_follow`,`id_utente`,`id_blog`),
  ADD KEY `id_blog` (`id_blog`);

--
-- Indici per le tabelle `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id_like`),
  ADD KEY `like_ibfk_1` (`id_post`),
  ADD KEY `like_ibfk_2` (`id_utente`);

--
-- Indici per le tabelle `notifiche`
--
ALTER TABLE `notifiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indici per le tabelle `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `id_autore` (`id_autore`,`id_sottocat`,`id_blog`),
  ADD KEY `id_blog` (`id_blog`),
  ADD KEY `id_sottocat` (`id_sottocat`);

--
-- Indici per le tabelle `premium`
--
ALTER TABLE `premium`
  ADD PRIMARY KEY (`id_utente`),
  ADD UNIQUE KEY `numero_carta` (`numero_carta`);

--
-- Indici per le tabelle `sottocat`
--
ALTER TABLE `sottocat`
  ADD PRIMARY KEY (`id_sottocat`),
  ADD KEY `id_post` (`id_categoria`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indici per le tabelle `stile`
--
ALTER TABLE `stile`
  ADD PRIMARY KEY (`id_stile`);

--
-- Indici per le tabelle `utente`
--
ALTER TABLE `utente`
  ADD PRIMARY KEY (`id_utente`);

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
-- AUTO_INCREMENT per la tabella `follow`
--
ALTER TABLE `follow`
  MODIFY `id_follow` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_ibfk_2` FOREIGN KEY (`id_stile`) REFERENCES `stile` (`id_stile`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_ibfk_3` FOREIGN KEY (`id_proprietario`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `commento`
--
ALTER TABLE `commento`
  ADD CONSTRAINT `commento_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `commento_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `co_autore`
--
ALTER TABLE `co_autore`
  ADD CONSTRAINT `co_autore_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`),
  ADD CONSTRAINT `co_autore_ibfk_2` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `follow`
--
ALTER TABLE `follow`
  ADD CONSTRAINT `follow_ibfk_1` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `follow_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `like_ibfk_1` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `like_ibfk_2` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  ADD CONSTRAINT `notifiche_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifiche_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `post_ibfk_2` FOREIGN KEY (`id_autore`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `post_ibfk_3` FOREIGN KEY (`id_sottocat`) REFERENCES `sottocat` (`id_sottocat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `premium`
--
ALTER TABLE `premium`
  ADD CONSTRAINT `premium_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `sottocat`
--
ALTER TABLE `sottocat`
  ADD CONSTRAINT `sottocat_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
