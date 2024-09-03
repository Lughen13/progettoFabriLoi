-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Set 03, 2024 alle 04:55
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
  `id_proprietario` int(10) NOT NULL,
  `followers_count` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `blog`
--

INSERT INTO `blog` (`id_blog`, `data_blog`, `titolo_blog`, `descrizione`, `img_logo`, `id_categoria`, `id_proprietario`, `followers_count`) VALUES
(1, '2024-09-02 08:24:30', 'L\'armadio di Federica  ', 'Questo è un blog in cui vi parlerò dei capi che vorrei acquistare ', 'blog_logo_1.jpg', 2, 1, 1),
(2, '2024-09-02 08:51:26', 'Recensioni su posti in cui sono stata', 'Ciao a tutti, questo è il mio blog di recensioni. Viaggio molto e mi piace poter condividere le mie esperienze sui posti che visito. ', 'blog_logo_2.jpg', 7, 3, 1),
(3, '2024-09-02 09:20:53', 'Passatempi che amo ', 'Questo sarà il mio blog dei passatempi. Se avete qualcosa da consigliarmi commentatee!! <3', 'Passatempi che amo .png', 3, 4, 1),
(4, '2024-09-02 09:58:48', 'In cucina con Ale', 'Sono una chef a domicilio, vi parlerò delle ricette che propongo ai miei clienti. ', 'blog_logo_4.jpg', 5, 5, 0),
(5, '2024-09-02 11:20:49', 'Apple.', 'Tutto ciò che riguarda il mondo della mela.', 'Apple..jpg', 1, 6, 1),
(6, '2024-09-02 11:44:56', 'In viaggio con WeRoad', 'Ti raccontiamo le nostre esperienze', 'In viaggio con WeRoad.png', 4, 7, 1),
(7, '2024-09-02 12:36:06', 'La Biologia in modo semplice!', 'Vi racconto il mio mondo scrivendo sulla scienza!', 'La Biologia in modo semplice!.png', 6, 8, 0),
(8, '2024-09-02 17:18:32', 'Parliamo di motori!', 'Formula 1 e non solo', 'Parliamo di motori!.png', 3, 9, 1),
(9, '2024-09-02 18:32:05', 'Calcio, calcio e ancora calcio', 'Parliamo dello sport più seguito al mondo', 'Calcio, calcio e ancora calcio.jpg', 9, 2, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(20) NOT NULL,
  `nome_categoria` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nome_categoria`) VALUES
(1, 'Tecnologia'),
(2, 'Moda'),
(3, 'Tempo libero'),
(4, 'Viaggi'),
(5, 'Cucina'),
(6, 'Scienze'),
(7, 'Recensioni'),
(8, 'Arredamento'),
(9, 'Altro');

-- --------------------------------------------------------

--
-- Struttura della tabella `commento`
--

CREATE TABLE `commento` (
  `id_comm` int(10) NOT NULL,
  `data_comm` datetime NOT NULL,
  `contenuto` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `id_utente` int(10) NOT NULL,
  `id_post` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `commento`
--

INSERT INTO `commento` (`id_comm`, `data_comm`, `contenuto`, `id_utente`, `id_post`) VALUES
(1, '2024-09-02 09:13:45', 'L\'abito rosso è davvero fantastico, ne ho indossato uno simile ad un matrimonio qualche anno fa', 3, 1),
(2, '2024-09-02 09:16:39', 'ADORO LE BORSE DI TELA!!!!', 4, 2),
(3, '2024-09-02 09:37:45', 'Interessante questo albergo. Com\'è la spiaggia?', 4, 3),
(4, '2024-09-02 09:48:23', 'Lo leggerò sicuramenteeee', 1, 6),
(5, '2024-09-02 09:53:33', 'siiii, andiamo a Spazio 900', 3, 7),
(6, '2024-09-02 12:38:13', 'Wow, ci voglio andare !', 8, 9),
(7, '2024-09-02 18:30:52', 'Che scuderia! ❤️', 2, 11),
(8, '2024-09-02 18:36:36', 'Mah!', 2, 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `co_autore`
--

CREATE TABLE `co_autore` (
  `id_utente` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `co_autore`
--

INSERT INTO `co_autore` (`id_utente`, `id_blog`) VALUES
(1, 3),
(1, 9),
(2, 2);

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
-- Dump dei dati per la tabella `follow`
--

INSERT INTO `follow` (`id_follow`, `id_utente`, `id_blog`, `data_follow`) VALUES
(1, 2, 1, '2024-09-02 06:37:29'),
(2, 3, 1, '2024-09-02 07:13:48'),
(3, 4, 1, '2024-09-02 07:37:14'),
(4, 2, 2, '2024-09-02 07:54:15'),
(5, 6, 1, '2024-09-02 09:14:42'),
(6, 7, 5, '2024-09-02 09:52:20'),
(7, 8, 3, '2024-09-02 10:37:52'),
(8, 8, 6, '2024-09-02 10:38:03'),
(9, 8, 5, '2024-09-02 15:07:11'),
(10, 9, 5, '2024-09-02 15:10:58'),
(11, 2, 8, '2024-09-02 16:30:33'),
(12, 1, 9, '2024-09-02 16:37:47');

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
-- Dump dei dati per la tabella `likes`
--

INSERT INTO `likes` (`id_like`, `id_utente`, `id_post`) VALUES
(2, 2, 2),
(3, 3, 1),
(4, 4, 2),
(5, 4, 3),
(6, 1, 6),
(7, 1, 4),
(8, 1, 5),
(9, 1, 7),
(10, 3, 4),
(11, 3, 6),
(12, 6, 2),
(13, 6, 1),
(14, 7, 8),
(15, 8, 9),
(16, 8, 8),
(17, 2, 11),
(18, 2, 1);

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
  `data` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `notifiche`
--

INSERT INTO `notifiche` (`id`, `user_id`, `sender_id`, `tipo`, `contenuto_id`, `data`) VALUES
(1, 1, 2, 'follow', 1, '2024-09-02 06:37:29'),
(3, 1, 2, 'like', 2, '2024-09-02 06:37:34'),
(4, 1, 3, 'like', 1, '2024-09-02 07:13:20'),
(5, 1, 3, 'comment', 1, '2024-09-02 07:13:45'),
(6, 1, 3, 'follow', 1, '2024-09-02 07:13:48'),
(7, 1, 4, 'comment', 2, '2024-09-02 07:16:39'),
(8, 1, 4, 'like', 2, '2024-09-02 07:16:41'),
(9, 1, 4, 'follow', 1, '2024-09-02 07:37:14'),
(10, 3, 4, 'comment', 3, '2024-09-02 07:37:45'),
(11, 3, 4, 'like', 3, '2024-09-02 07:37:47'),
(12, 4, 1, 'like', 6, '2024-09-02 07:48:16'),
(13, 4, 1, 'comment', 6, '2024-09-02 07:48:23'),
(14, 4, 1, 'like', 4, '2024-09-02 07:48:26'),
(15, 4, 1, 'like', 5, '2024-09-02 07:48:28'),
(16, 1, 1, 'like', 7, '2024-09-02 07:49:02'),
(17, 4, 3, 'like', 4, '2024-09-02 07:53:13'),
(18, 4, 3, 'like', 6, '2024-09-02 07:53:17'),
(19, 4, 3, 'comment', 7, '2024-09-02 07:53:33'),
(20, 3, 2, 'follow', 2, '2024-09-02 07:54:15'),
(21, 1, 6, 'follow', 1, '2024-09-02 09:14:42'),
(22, 1, 6, 'like', 2, '2024-09-02 09:14:56'),
(23, 1, 6, 'like', 1, '2024-09-02 09:14:58'),
(24, 6, 7, 'follow', 5, '2024-09-02 09:52:20'),
(25, 6, 7, 'like', 8, '2024-09-02 09:52:22'),
(26, 4, 8, 'follow', 3, '2024-09-02 10:37:52'),
(27, 7, 8, 'follow', 6, '2024-09-02 10:38:03'),
(28, 7, 8, 'like', 9, '2024-09-02 10:38:05'),
(29, 7, 8, 'comment', 9, '2024-09-02 10:38:13'),
(30, 6, 8, 'follow', 5, '2024-09-02 15:07:11'),
(31, 6, 8, 'like', 8, '2024-09-02 15:07:12'),
(32, 6, 9, 'follow', 5, '2024-09-02 15:10:58'),
(33, 9, 2, 'follow', 8, '2024-09-02 16:30:33'),
(36, 1, 2, 'like', 1, '2024-09-02 16:36:18'),
(37, 1, 2, 'comment', 2, '2024-09-02 16:36:36'),
(38, 2, 1, 'follow', 9, '2024-09-02 16:37:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `post`
--

CREATE TABLE `post` (
  `id_post` int(10) NOT NULL,
  `data_post` datetime NOT NULL,
  `titolo_post` varchar(50) NOT NULL,
  `descrizione_post` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_post` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '''default.png''',
  `id_autore` int(10) NOT NULL,
  `id_sottocat` int(10) NOT NULL,
  `id_blog` int(10) NOT NULL,
  `likes_count` int(10) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `post`
--

INSERT INTO `post` (`id_post`, `data_post`, `titolo_post`, `descrizione_post`, `img_post`, `id_autore`, `id_sottocat`, `id_blog`, `likes_count`) VALUES
(1, '2024-09-02 08:29:54', 'Abiti da cerimonia ', 'Gli abiti da cerimonia sono la quintessenza dello stile, offrono senza dubbio l\'opportunità di distinguerti e lasciare un\'impressione indelebile in ogni momento da celebrare con indiscutibile raffinatezza.\r\n\r\nOgni evento speciale richiede un tocco di classe ed eleganza, e i vestiti da cerimonia sono la scelta ideale per catturare tale atmosfera. Niente di più azzeccato per esprimere classe e stile, trasformando ogni occasione in un\'opportunità per brillare con un allure personale di eleganza e glamour.\r\n\r\nDai matrimoni, momenti di gioia e festa dove la presentazione impeccabile è d\'obbligo, ai party formali, serate di gala e premiazioni, gli abiti da cerimonia sono l\'emblema della moda per eventi memorabili.', '[\"post_66d55bb25264d2.41297660.jpg\",\"post_66d55bb2529292.18018392.jpg\"]', 1, 5, 1, 3),
(2, '2024-09-02 08:35:12', 'Borse di tela ', 'Le prime borse di tela “culturali” furono quelle della National Public Radio (NPR), che negli anni Settanta cominciò a regalarle agli ascoltatori che facevano delle donazioni come forma di ringraziamento.Le borse di tela “culturali” di maggior successo oggi negli Stati Uniti sono però quelle del New Yorker. Da poco più di un anno si possono ottenere (“gratis”) abbonandosi alla rivista, e solo in questo modo: in questo modo la borsa distingue ancora di più chi la porta dalle altre persone, perché solo un vero lettore del New Yorker (o comunque qualcuno che ha speso come minimo i 12 dollari dell’abbonamento digitale per 12 settimane) può sfoggiarla. ', '[\"66d55ca05fe09_Borse di tela _1.jpg\"]', 1, 6, 1, 3),
(3, '2024-09-02 09:13:06', 'Albergo Promenade (Giulianova) ', 'Sono stata una settimana in questo albergo, con la formula tutto incluso (colazione, pranzo e cena). Ci siamo trovati benissimo, il cibo era eccellente e i camerieri super disponibili e sempre sorridenti. La nostra bambina non si è mai annoiata con l\'animazione che ha svolto tante attività e cene a tema. Torneremo sicuramente ', '[\"66d5658221c46_Albergo Promenade (Giulianova) _4.jpg\"]', 3, 31, 2, 1),
(4, '2024-09-02 09:22:06', 'La corsa ', 'La corsa è un\'attività fisica da sempre praticata dall\'uomo ed è l\'attività sulla quale si basa la maggioranza delle attività sportive.\r\n\r\nSi definisce come \"corsa\" l\'andatura umana o animale composta da una prosecuzione di balzi, in cui, in una prima fase, un piede rimane a contatto con il terreno; nella fase successiva il piede si stacca da terra insieme al resto del corpo (per questo si chiama fase di volo), fino a quando atterra l\'altro piede. Si contraddistingue dalla camminata, mentre la marcia (in cui il corpo mantiene sempre il contatto con il terreno) e ogni altra versione agonistica della corsa rientrano nel podismo.', '[\"66d5679e066b7_La corsa _1.jpg\"]', 4, 10, 3, 2),
(5, '2024-09-02 09:27:20', 'Cosa suonano le mie cuffiette ', 'Adoro ascoltare musica, lo faccio per la maggior parte del mio tempo. Prevalgo il cantautorato italiano e la musica classica, mi rilassano e mi rendono molto produttiv* e creativ*. \r\nI miei cantautori preferiti sono: \r\nLigabue, \r\nDomenico Modugno, \r\nVasco Rossi, \r\nFabrizio De Andrè, \r\nMia Martini, \r\nFranco Battiato, \r\nLucio Battisti. \r\nInvece i compositori di musica classica che adoro sono: 1. Ludwig Van Beethoven, Wolfgang Amadeus Mozart, Johann Sebastian Bach, Richard Wagner, Franz Schubert.', '[\"post_66d569c017d333.79545986.jpg\",\"post_66d569c0180692.58116337.jpg\"]', 4, 12, 3, 1),
(6, '2024-09-02 09:36:45', 'La mia copertina preferita di agosto ', 'Nel mese di agosto ho letto tanti libri, ma quello che mi ha rubato il cuore è Assassinio sull\'Orient Express di Agatha Christie. Questo è il secondo libro che leggo dell\'autrice, avevo letto e amato \"dieci piccoli indiani\" ma questo devo dire che mi è piaciuto moooooooltooooo di più. La maestria dell\'autrice nel creare trame e intrecci è innegabile e anche questo libro è scorrevole da leggere. Anche se non mi ha preso al 100% è un libro che incuriosisce e arrivi tranquillamente alla fine per scoprire chi è l\'assassino. È un libro lineare, non ha picchi di azione se non alla fine. I personaggi sono sviluppati molto bene e tutti i pezzi del puzzle si incastrano perfettamente e ti portano ad un risvolto inaspettato. Un classico del genere che sicuramente vale la pena di leggere.', '[\"66d56b0d630de_La mia copertina preferita di agosto _1.jpg\"]', 4, 14, 3, 2),
(7, '2024-09-02 09:47:49', 'LA TECHNOOO', 'Ciao a tutti. ne approfitto per condividere con voi la mia passione per la musica techno. La adoro e quando vado a ballarla mi sento me stessa e libera di esprimermi. Mi piacerebbe organizzare qualche serata con gente nuova, magari per andare a Roma. \r\nPS: la berlinese è diventata patrimonio unesco', '[\"66d56da5b9297_LA TECHNOOO_1.jpeg\",\"66d56da5b9654_LA TECHNOOO_2.jpeg\",\"66d56da5b9a48_LA TECHNOOO_3.jpeg\"]', 1, 12, 3, 1),
(8, '2024-09-02 11:25:02', 'Apple Silicon M4 ufficiale.', 'Le prestazioni ad alta efficienza energetica del chip, M4 unite al nuovo motore del display, rendono possibili il design ultrasottile e il rivoluzionario display di iPad Pro, mentre i miglioramenti sostanziali alla CPU, alla GPU, al Neural Engine e al sistema di memoria, rendono il chip M4 perfetto per le più recenti applicazioni basate sull’AI - ha spiegato Johny Srouji, Senior Vice President of Hardware Technologies di Apple. Complessivamente, questo nuovo chip rende iPad Pro il dispositivo più potente della sua categoria.', '[\"66d5846e97dc1_Apple Silicon M4 ufficiale._4.jpg\"]', 6, 2, 5, 2),
(9, '2024-09-02 11:47:45', 'Tromsø Express: a caccia dell\'aurora boreale', 'Questo è un viaggio WeRoadX. Cosa vuol dire? Si tratta di un viaggio progettato e realizzato interamente da Coordinatori WeRoad esperti. Il Coordinatore si occupa di tutto il viaggio: dalla definizione dell\'itinerario alla selezione delle accommodation e delle esperienze in loco. Tramite WeRoad potrai prenotare il viaggio e gestirlo nella tua area personale, come qualsiasi altro WeRoad.\r\n\r\nChi non vorrebbe ammirare l’aurora boreale senza spendere troppo? Ed allora eccoci qui, la nostra avventura si svolgerà a Tromsø, la città più grande della Norvegia del Nord, definita “la porta dell’Artico”. Una città magica, incastonata nei fiordi e incorniciata da cime innevate. La sua posizione estrema, a soli 400 km dal Circolo Polare Artico, permette di assistere al meraviglioso spettacolo di forme e colori dell’aurora boreale, che potremmo ammirare da una slitta trainate da renne.', '[\"66d589c1dedaf_Troms\\u00f8 Express: a caccia dell\'aurora boreale_4.jpg\"]', 7, 16, 6, 1),
(10, '2024-09-02 12:37:39', 'Come treni sui binari: così le cellule navigano!', 'Un gruppo di fisici ha ricostruito, attraverso esperimenti in vitro e simulazioni, come si comportano e interagisono le cellule in movimento.', '[\"post_66d668873143e0.27578422.jpg\"]', 8, 25, 7, 0),
(11, '2024-09-02 17:20:53', 'Ferrari', 'La Scuderia Ferrari è la squadra corse fondata da Enzo Ferrari nel 1929: è l’unico team ad aver disputato tutti i campionati di Formula 1 dal 1950 a oggi ed è anche la squadra più vittoriosa nella storia della categoria regina del motorsport. La Ferrari vanta 16 titoli mondiali Costruttori e 15 Mondiali Piloti.', '[\"66d5d7d5d0fc9_Ferrari_4.jpg\"]', 9, 10, 8, 1),
(12, '2024-09-02 18:34:41', 'Juventus, Tiago Djalò al Porto: è fatta.', 'MAI PROTAGONISTA - Per Tiago Djalò, soffiato all’Inter nella finestra invernale del mercato dell’ultima stagione per circa 5 milioni di euro, si presenta così un interessante via d’uscita che gli permetterà di ritrovare protagonismo in campo. Con la maglia della Vecchia Signora non è mai stato preso in considerazione dall’ex allenatore Massimiliano Allegri, per poi essere utilizzato da Paolo Montero, dopo l\'esonero di Allegri, con appena 16 minuti nell’ultima partita dello scorso campionato contro il Monza.', '[\"post_66d66985b50989.14695325.jpeg\"]', 2, 38, 9, 0);

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

--
-- Dump dei dati per la tabella `premium`
--

INSERT INTO `premium` (`id_utente`, `intestatario`, `numero_carta`, `data_scadenza`) VALUES
(1, 'Federica Fabri', '1234567899876543', '2028-01-21'),
(2, 'Luca Loi', '7894561237894564', '2030-02-10'),
(4, 'nomecognome', '7897894561234561', '2031-05-04'),
(5, 'alessia fabri', '4561237531234569', '2033-08-07');

-- --------------------------------------------------------

--
-- Struttura della tabella `sottocat`
--

CREATE TABLE `sottocat` (
  `id_sottocat` int(10) NOT NULL,
  `id_categoria` int(10) NOT NULL,
  `nome_sottocat` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `sottocat`
--

INSERT INTO `sottocat` (`id_sottocat`, `id_categoria`, `nome_sottocat`) VALUES
(1, 1, 'Internet'),
(2, 1, 'Hardware'),
(3, 1, 'Software'),
(4, 1, 'Altro'),
(5, 2, 'Abbigliamento'),
(6, 2, 'Accessori'),
(7, 2, 'Altro'),
(8, 3, 'Benessere'),
(9, 3, 'Vacanze'),
(10, 3, 'Sport'),
(11, 3, 'Cinema'),
(12, 3, 'Musica'),
(13, 3, 'Televisione'),
(14, 3, 'Libri'),
(15, 3, 'Altro'),
(16, 4, 'Destinazioni'),
(17, 4, 'Trasporti'),
(18, 4, 'Cultura'),
(19, 4, 'Alloggi'),
(20, 4, 'Altro'),
(21, 5, 'Ricette'),
(22, 5, 'Tecniche di cucina'),
(23, 5, 'Attrezzi da cucina'),
(24, 5, 'Altro'),
(25, 6, 'Biologia'),
(26, 6, 'Medicina'),
(27, 6, 'Fisica'),
(28, 6, 'Chimica'),
(29, 6, 'Altro'),
(30, 7, 'Auto'),
(31, 7, 'Alberghi'),
(32, 7, 'Ristoranti'),
(33, 7, 'Veicoli'),
(34, 7, 'Altro'),
(35, 8, 'Interni'),
(36, 8, 'Giardino'),
(37, 8, 'Altro'),
(38, 9, 'Altro');

-- --------------------------------------------------------

--
-- Struttura della tabella `utente`
--

CREATE TABLE `utente` (
  `id_utente` int(10) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `pw` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `img_profilo` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'default.png',
  `nome` varchar(20) NOT NULL,
  `cognome` varchar(20) NOT NULL,
  `genere` varchar(10) NOT NULL,
  `data_nascita` date NOT NULL,
  `bio` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `numero_telefono` varchar(10) DEFAULT NULL,
  `premium` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utente`
--

INSERT INTO `utente` (`id_utente`, `username`, `email`, `pw`, `img_profilo`, `nome`, `cognome`, `genere`, `data_nascita`, `bio`, `numero_telefono`, `premium`) VALUES
(1, 'federicafabri', 'federicafabri11@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', 'federicafabri_profilo.jpg', 'Federica', 'Fabri', 'Femmina', '2002-10-02', 'Ciao a tutti, sono Federica ,una studentessa universitaria. Mi piacerebbe condividere con voi le cose che mi piacciono, lo farò qui. baci <3<3', '3407348310', 1),
(2, 'lucaloi', 'lucaloi@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', 'lucaloi_profilo.jpg', 'Luca', 'Loi', 'Maschio', '1999-02-08', 'Marinaio e studente universitario.', '1234567891', 1),
(3, 'nomecognome', 'nomecognome@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', '../uploads/predefinita.jpeg', 'nome', 'cognome', 'Femmina', '1980-05-04', NULL, '4561237890', 0),
(4, 'username', 'username@alice.it', '598d6b1a1d6e27fcd7f1960013b881d8', 'username_profilo.jpg', 'nomedue', 'cognomedue', 'Altro', '2001-09-28', 'Ciao, adoro i gatti e la musica', '4564564560', 1),
(5, 'alessia123', 'alessiafabri@email.it', '598d6b1a1d6e27fcd7f1960013b881d8', '../uploads/predefinita.jpeg', 'Alessia', 'Fabri', 'Femmina', '2007-09-20', NULL, '7897897897', 1),
(6, 'TimCook', 'apple@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', 'TimCook_profilo.jpg', 'Tim', 'Cook', 'Maschio', '1960-11-01', 'I\'m the fu****g Ceo.', '3339293943', 0),
(7, 'AndreWeRoad', 'andre@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', 'AndreWeRoad_profilo.jpg', 'Andrea', 'Mariolo', 'Maschio', '1990-09-01', NULL, '2319828349', 0),
(8, 'Elisabetta', 'elisabetta@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', 'Elisabetta_profilo.jpg', 'Elisabetta', 'Intini', 'Femmina', '1994-03-10', 'Scrivo su Focus!', '4829494239', 0),
(9, 'Leclerc16', 'leclerc@gmail.com', '598d6b1a1d6e27fcd7f1960013b881d8', 'Leclerc16_profilo.jpg', 'Charles', 'Leclerc', 'Maschio', '1997-10-16', 'Pilota con il numero 16', '4839498348', 0);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id_blog`),
  ADD UNIQUE KEY `titolo_blog` (`titolo_blog`,`id_proprietario`),
  ADD KEY `id_categoria` (`id_categoria`,`id_proprietario`),
  ADD KEY `id_proprietario` (`id_proprietario`);

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
  MODIFY `id_blog` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT per la tabella `commento`
--
ALTER TABLE `commento`
  MODIFY `id_comm` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `follow`
--
ALTER TABLE `follow`
  MODIFY `id_follow` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `likes`
--
ALTER TABLE `likes`
  MODIFY `id_like` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT per la tabella `notifiche`
--
ALTER TABLE `notifiche`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT per la tabella `post`
--
ALTER TABLE `post`
  MODIFY `id_post` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `sottocat`
--
ALTER TABLE `sottocat`
  MODIFY `id_sottocat` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT per la tabella `utente`
--
ALTER TABLE `utente`
  MODIFY `id_utente` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE,
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
