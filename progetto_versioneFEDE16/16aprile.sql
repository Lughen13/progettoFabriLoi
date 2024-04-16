-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 16, 2024 alle 22:48
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
-- Database: `progettobdd`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `blog`
--

CREATE TABLE `blog` (
  `id_blog` int(10) NOT NULL,
  `data_blog` datetime NOT NULL,
  `titolo_blog` varchar(50) NOT NULL,
  `descrizione` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `id_utente` int(10) NOT NULL,
  `id_coautore` int(10) NOT NULL,
  `id_genere` int(10) NOT NULL,
  `id_stile` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(20) NOT NULL,
  `descrizione` int(11) NOT NULL,
  `ig_blog` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `commento`
--

CREATE TABLE `commento` (
  `id_comm` int(10) NOT NULL,
  `data_comm` datetime NOT NULL,
  `contenuto` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_comm` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'default.png',
  `id_utente` int(10) NOT NULL,
  `id_post` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `like`
--

CREATE TABLE `like` (
  `id_like` int(10) NOT NULL,
  `data_like` datetime NOT NULL,
  `id_utente` int(10) NOT NULL,
  `id_post` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `id_sotgenere` int(10) NOT NULL,
  `id_stile` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL,
  `id_comm` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `scrittore`
--

CREATE TABLE `scrittore` (
  `id_utente` int(10) NOT NULL,
  `coautore` tinyint(1) NOT NULL,
  `proprietario` tinyint(1) NOT NULL,
  `id_blog` int(10) NOT NULL,
  `id_scrittore` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `stile`
--

CREATE TABLE `stile` (
  `id_stile` int(10) NOT NULL,
  `nome` varchar(20) NOT NULL,
  `carattere` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id_utente` int(10) NOT NULL,
  `user` varchar(20) NOT NULL,
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
  `cvc` varchar(3) NOT NULL,
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
  ADD KEY `id_stile` (`id_stile`),
  ADD KEY `id_genere` (`id_genere`),
  ADD KEY `FK_blog_utente` (`id_utente`);

--
-- Indici per le tabelle `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `ig_blog` (`ig_blog`);

--
-- Indici per le tabelle `commento`
--
ALTER TABLE `commento`
  ADD PRIMARY KEY (`id_comm`),
  ADD KEY `id_utente` (`id_utente`,`id_post`),
  ADD KEY `fk_commento_post` (`id_post`);

--
-- Indici per le tabelle `like`
--
ALTER TABLE `like`
  ADD PRIMARY KEY (`id_like`),
  ADD KEY `id_utente` (`id_utente`,`id_post`),
  ADD KEY `fk_like_post` (`id_post`);

--
-- Indici per le tabelle `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id_post`),
  ADD KEY `id_autore` (`id_autore`,`id_sotgenere`,`id_stile`,`id_blog`),
  ADD KEY `fk_post_blog` (`id_blog`),
  ADD KEY `fk_post_stile` (`id_stile`),
  ADD KEY `id_comm` (`id_comm`);

--
-- Indici per le tabelle `scrittore`
--
ALTER TABLE `scrittore`
  ADD PRIMARY KEY (`id_scrittore`),
  ADD KEY `id_utente` (`id_utente`,`id_blog`),
  ADD KEY `fk_scrittore_blog` (`id_blog`);

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
  ADD UNIQUE KEY `user` (`user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `blog`
--
ALTER TABLE `blog`
  MODIFY `id_blog` int(10) NOT NULL AUTO_INCREMENT;

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
-- Limiti per la tabella `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `fk_categoria_blog` FOREIGN KEY (`ig_blog`) REFERENCES `blog` (`id_blog`);

--
-- Limiti per la tabella `commento`
--
ALTER TABLE `commento`
  ADD CONSTRAINT `commento_ibfk_1` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`),
  ADD CONSTRAINT `fk_commento_post` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`);

--
-- Limiti per la tabella `like`
--
ALTER TABLE `like`
  ADD CONSTRAINT `fk_like_blog` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`),
  ADD CONSTRAINT `fk_like_post` FOREIGN KEY (`id_post`) REFERENCES `post` (`id_post`);

--
-- Limiti per la tabella `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `fk_post_blog` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`),
  ADD CONSTRAINT `fk_post_scrittore` FOREIGN KEY (`id_autore`) REFERENCES `scrittore` (`id_utente`),
  ADD CONSTRAINT `fk_post_stile` FOREIGN KEY (`id_stile`) REFERENCES `stile` (`id_stile`),
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`id_comm`) REFERENCES `commento` (`id_comm`);

--
-- Limiti per la tabella `scrittore`
--
ALTER TABLE `scrittore`
  ADD CONSTRAINT `fk_scrittore_blog` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`),
  ADD CONSTRAINT `fk_scrittore_utente` FOREIGN KEY (`id_utente`) REFERENCES `utente` (`id_utente`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
