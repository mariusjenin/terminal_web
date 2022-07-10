-- phpMyAdmin SQL Dump
-- version 4.6.6deb5ubuntu0.5
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Ven 16 Juillet 2021 à 13:18
-- Version du serveur :  5.7.34-0ubuntu0.18.04.1
-- Version de PHP :  7.2.24-0ubuntu0.18.04.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `terminal_bdd`
--

-- --------------------------------------------------------

--
-- Structure de la table `arret`
--

CREATE TABLE `arret` (
  `id_arret` int(11) NOT NULL,
  `id_itineraire` int(11) NOT NULL,
  `nom_arret` varchar(200) NOT NULL,
  `num_arret` int(11) NOT NULL COMMENT 'numéro de l''arrêt dans l''itinéraire',
  `latitude` float(8,6) NOT NULL,
  `longitude` float(8,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `horaire_arret`
--

CREATE TABLE `horaire_arret` (
  `id_horaire_arret` int(11) NOT NULL,
  `id_trajet` int(11) NOT NULL,
  `id_arret` int(11) NOT NULL,
  `heure_passage` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `itineraire`
--

CREATE TABLE `itineraire` (
  `id_itineraire` int(11) NOT NULL,
  `nom_itineraire` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `participe_a_trajet`
--

CREATE TABLE `participe_a_trajet` (
  `id_reservation` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `id_trajet` int(11) NOT NULL,
  `date_participation` date NOT NULL,
  `id_arret_depart` int(11) NOT NULL,
  `id_arret_fin` int(11) NOT NULL,
  `nb_places` int(11) NOT NULL,
  `latitude_utilisateur` float DEFAULT NULL,
  `longitude_utilisateur` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `trajet`
--

CREATE TABLE `trajet` (
  `id_trajet` int(11) NOT NULL,
  `id_itineraire` int(11) NOT NULL,
  `date_depart` date DEFAULT NULL,
  `repetition` enum('UNIQUE','HEBDOMADAIRE','MENSUEL','ANNUEL','OUVRES','WEEKEND') NOT NULL,
  `place_max` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `latitude_conducteur` float DEFAULT NULL,
  `longitude_conducteur` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `email` varchar(200) NOT NULL,
  `hash_pwd` varchar(200) NOT NULL,
  `type` enum('PASSAGER','CONDUCTEUR','ADMINISTRATEUR') NOT NULL,
  `token` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `arret`
--
ALTER TABLE `arret`
  ADD PRIMARY KEY (`id_arret`),
  ADD KEY `id_itineraire` (`id_itineraire`);

--
-- Index pour la table `horaire_arret`
--
ALTER TABLE `horaire_arret`
  ADD PRIMARY KEY (`id_horaire_arret`),
  ADD KEY `id_trajet` (`id_trajet`),
  ADD KEY `id_arret` (`id_arret`);

--
-- Index pour la table `itineraire`
--
ALTER TABLE `itineraire`
  ADD PRIMARY KEY (`id_itineraire`);

--
-- Index pour la table `participe_a_trajet`
--
ALTER TABLE `participe_a_trajet`
  ADD PRIMARY KEY (`id_reservation`),
  ADD KEY `id_trajet` (`id_trajet`),
  ADD KEY `id_arret_depart` (`id_arret_depart`),
  ADD KEY `id_arret_fin` (`id_arret_fin`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `trajet`
--
ALTER TABLE `trajet`
  ADD PRIMARY KEY (`id_trajet`),
  ADD KEY `id_itineraire` (`id_itineraire`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `token` (`token`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `arret`
--
ALTER TABLE `arret`
  MODIFY `id_arret` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `horaire_arret`
--
ALTER TABLE `horaire_arret`
  MODIFY `id_horaire_arret` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `itineraire`
--
ALTER TABLE `itineraire`
  MODIFY `id_itineraire` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `participe_a_trajet`
--
ALTER TABLE `participe_a_trajet`
  MODIFY `id_reservation` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `trajet`
--
ALTER TABLE `trajet`
  MODIFY `id_trajet` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `arret`
--
ALTER TABLE `arret`
  ADD CONSTRAINT `arret_ibfk_1` FOREIGN KEY (`id_itineraire`) REFERENCES `itineraire` (`id_itineraire`);

--
-- Contraintes pour la table `horaire_arret`
--
ALTER TABLE `horaire_arret`
  ADD CONSTRAINT `horaire_arret_ibfk_1` FOREIGN KEY (`id_trajet`) REFERENCES `trajet` (`id_trajet`),
  ADD CONSTRAINT `horaire_arret_ibfk_2` FOREIGN KEY (`id_arret`) REFERENCES `arret` (`id_arret`);

--
-- Contraintes pour la table `participe_a_trajet`
--
ALTER TABLE `participe_a_trajet`
  ADD CONSTRAINT `participe_a_trajet_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `participe_a_trajet_ibfk_2` FOREIGN KEY (`id_trajet`) REFERENCES `trajet` (`id_trajet`),
  ADD CONSTRAINT `participe_a_trajet_ibfk_3` FOREIGN KEY (`id_arret_depart`) REFERENCES `arret` (`id_arret`),
  ADD CONSTRAINT `participe_a_trajet_ibfk_4` FOREIGN KEY (`id_arret_fin`) REFERENCES `arret` (`id_arret`);

--
-- Contraintes pour la table `trajet`
--
ALTER TABLE `trajet`
  ADD CONSTRAINT `trajet_ibfk_1` FOREIGN KEY (`id_itineraire`) REFERENCES `itineraire` (`id_itineraire`),
  ADD CONSTRAINT `trajet_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
