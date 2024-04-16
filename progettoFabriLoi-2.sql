-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Apr 16, 2024 alle 15:29
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
-- Struttura della tabella `Blog`
--

CREATE TABLE `Blog` (
  `ID` int(11) NOT NULL,
  `Titolo` varchar(255) NOT NULL,
  `ID_Proprietario` int(11) DEFAULT NULL,
  `ID_Coautore` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Commenti`
--

CREATE TABLE `Commenti` (
  `ID` int(11) NOT NULL,
  `Contenuto` text NOT NULL,
  `DataPubblicazione` datetime DEFAULT current_timestamp(),
  `ID_Utente` int(11) DEFAULT NULL,
  `ID_Post` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `DesignBlog`
--

CREATE TABLE `DesignBlog` (
  `ID` int(11) NOT NULL,
  `Tema` varchar(50) NOT NULL,
  `Colore` varchar(7) DEFAULT '#FFFFFF',
  `ID_Utente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Genere`
--

CREATE TABLE `Genere` (
  `ID` int(11) NOT NULL,
  `Nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Mi_piace`
--

CREATE TABLE `Mi_piace` (
  `ID` int(11) NOT NULL,
  `ID_Utente` int(11) DEFAULT NULL,
  `ID_Post` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Post`
--

CREATE TABLE `Post` (
  `ID` int(11) NOT NULL,
  `Titolo` varchar(255) NOT NULL,
  `Contenuto` text NOT NULL,
  `DataPubblicazione` datetime DEFAULT current_timestamp(),
  `ID_Utente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Sottogenere`
--

CREATE TABLE `Sottogenere` (
  `ID` int(11) NOT NULL,
  `Nome` varchar(50) NOT NULL,
  `ID_Genere` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `Utenti`
--

CREATE TABLE `Utenti` (
  `ID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `DataDiNascita` date DEFAULT NULL,
  `Tipo` enum('Standard','Premium') NOT NULL DEFAULT 'Standard',
  `NumeroDiTelefono` varchar(10) NOT NULL,
  `CartaDiCredito` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `DataDiScadenza` date NOT NULL,
  `Cvv` varchar(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `Blog`
--
ALTER TABLE `Blog`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Proprietario` (`ID_Proprietario`),
  ADD KEY `ID_Coautore` (`ID_Coautore`);

--
-- Indici per le tabelle `Commenti`
--
ALTER TABLE `Commenti`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Utente` (`ID_Utente`),
  ADD KEY `ID_Post` (`ID_Post`);

--
-- Indici per le tabelle `DesignBlog`
--
ALTER TABLE `DesignBlog`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Utente` (`ID_Utente`);

--
-- Indici per le tabelle `Genere`
--
ALTER TABLE `Genere`
  ADD PRIMARY KEY (`ID`);

--
-- Indici per le tabelle `Mi_piace`
--
ALTER TABLE `Mi_piace`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Utente` (`ID_Utente`),
  ADD KEY `ID_Post` (`ID_Post`);

--
-- Indici per le tabelle `Post`
--
ALTER TABLE `Post`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Utente` (`ID_Utente`);

--
-- Indici per le tabelle `Sottogenere`
--
ALTER TABLE `Sottogenere`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ID_Genere` (`ID_Genere`);

--
-- Indici per le tabelle `Utenti`
--
ALTER TABLE `Utenti`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `Blog`
--
ALTER TABLE `Blog`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Commenti`
--
ALTER TABLE `Commenti`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `DesignBlog`
--
ALTER TABLE `DesignBlog`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Genere`
--
ALTER TABLE `Genere`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Mi_piace`
--
ALTER TABLE `Mi_piace`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Post`
--
ALTER TABLE `Post`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Sottogenere`
--
ALTER TABLE `Sottogenere`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `Utenti`
--
ALTER TABLE `Utenti`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `Blog`
--
ALTER TABLE `Blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`ID_Proprietario`) REFERENCES `Utenti` (`ID`),
  ADD CONSTRAINT `blog_ibfk_2` FOREIGN KEY (`ID_Coautore`) REFERENCES `Utenti` (`ID`);

--
-- Limiti per la tabella `Commenti`
--
ALTER TABLE `Commenti`
  ADD CONSTRAINT `commenti_ibfk_1` FOREIGN KEY (`ID_Utente`) REFERENCES `Utenti` (`ID`),
  ADD CONSTRAINT `commenti_ibfk_2` FOREIGN KEY (`ID_Post`) REFERENCES `Post` (`ID`);

--
-- Limiti per la tabella `DesignBlog`
--
ALTER TABLE `DesignBlog`
  ADD CONSTRAINT `designblog_ibfk_1` FOREIGN KEY (`ID_Utente`) REFERENCES `Utenti` (`ID`);

--
-- Limiti per la tabella `Genere`
--
ALTER TABLE `Genere`
  ADD CONSTRAINT `genere_ibfk_1` FOREIGN KEY (`ID`) REFERENCES `Post` (`ID`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `Mi_piace`
--
ALTER TABLE `Mi_piace`
  ADD CONSTRAINT `mi_piace_ibfk_1` FOREIGN KEY (`ID_Utente`) REFERENCES `Utenti` (`ID`),
  ADD CONSTRAINT `mi_piace_ibfk_2` FOREIGN KEY (`ID_Post`) REFERENCES `Post` (`ID`);

--
-- Limiti per la tabella `Post`
--
ALTER TABLE `Post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`ID_Utente`) REFERENCES `Utenti` (`ID`),
  ADD CONSTRAINT `post_ibfk_2` FOREIGN KEY (`ID`) REFERENCES `Blog` (`ID`) ON UPDATE CASCADE;

--
-- Limiti per la tabella `Sottogenere`
--
ALTER TABLE `Sottogenere`
  ADD CONSTRAINT `sottogenere_ibfk_1` FOREIGN KEY (`ID_Genere`) REFERENCES `Genere` (`ID`),
  ADD CONSTRAINT `sottogenere_ibfk_2` FOREIGN KEY (`ID`) REFERENCES `Post` (`ID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
