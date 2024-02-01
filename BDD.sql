DROP DATABASE eval;
-- creation d'une base de données
CREATE DATABASE IF NOT EXISTS eval;

-- Création d'un utilisateur ayant tous les droits pour cette base de données
CREATE USER IF NOT EXISTS 'eval'@'%' IDENTIFIED BY 'eval';
GRANT ALL PRIVILEGES ON eval.* TO 'eval'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;

-- utilisation d'une base de données
USE eval;

-- Créaton d'une table
CREATE TABLE IF NOT EXISTS classe
(
    idclasse INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(25)
);


INSERT INTO classe (nom) VALUES ('PROFS'), ('SNIR2'), ('SNIRA2') , ('CIELIR1'), ('CIELIRA1');


CREATE TABLE IF NOT EXISTS groupe
(
    idgroupe INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(25),
    idclasse INT,
    FOREIGN KEY (idclasse) REFERENCES classe(idclasse)
);

INSERT INTO groupe (nom, idclasse) 
VALUES 
   ('TS2SNIR_Grp1', (SELECT idclasse FROM classe WHERE nom='SNIR2')),
   ('TS2SNIR_Grp2', (SELECT idclasse FROM classe WHERE nom='SNIR2')),
   ('CIELIR1_Grp1', (SELECT idclasse FROM classe WHERE nom='CIELIR1')),
   ('CIELIR1_Grp2', (SELECT idclasse FROM classe WHERE nom='CIELIR1')),
   ('PROFS', (SELECT idclasse FROM classe WHERE nom='PROFS'))   
;

CREATE TABLE IF NOT EXISTS utilisateur
(
    idutilisateur INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    email VARCHAR(255),
    identifiant VARCHAR(30) NOT NULL,
    motdepasse CHAR(128) NOT NULL,
    idgroupe INT,
    FOREIGN KEY (idgroupe) REFERENCES groupe(idgroupe)
);

INSERT INTO utilisateur (nom, prenom, email, identifiant, motdepasse, idgroupe)
VALUES
 ('Georges', 'Gaëtan', 'g2.iris.lla@gmail.com', 'ggeorges', SHA2('as0304sa', 512), (SELECT idgroupe FROM groupe WHERE nom='PROFS')),
 ('Toto', 'Titi', 'toto.titi@gmail.com', 'toto', SHA2('toto', 512), (SELECT idgroupe FROM groupe WHERE nom='CIELIR1_Grp1'))
;


CREATE TABLE IF NOT EXISTS eval
(
    ideval INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(25),
    idutilisateur_prof INT,
    idgroupe INT,
    date DATETIME,
    type VARCHAR(5),
    statut INT,
    ws_port INT,
    pid INT,
    FOREIGN KEY(idutilisateur_prof) REFERENCES utilisateur(idutilisateur),
    FOREIGN KEY(idgroupe) REFERENCES groupe(idgroupe)
);

INSERT INTO eval (titre, idutilisateur_prof, idgroupe, date, type, statut, ws_port, pid) 
 VALUES 
  ('M01SW01 Grp1', (SELECT idutilisateur FROM utilisateur WHERE identifiant='ggeorges'), (SELECT idgroupe FROM groupe WHERE nom='CIELIR1_Grp1'), NOW(), 'live', '1', '0', '0')
;

CREATE TABLE IF NOT EXISTS question
(
    idquestion INT PRIMARY KEY AUTO_INCREMENT,
    ideval INT,
    question VARCHAR(200),
    type VARCHAR(6),  -- QCM ou Libre
    reponse VARCHAR(200),
    note FLOAT,
    FOREIGN KEY(ideval) REFERENCES eval(ideval)
);

CREATE TABLE IF NOT EXISTS choix
(
    idchoix INT PRIMARY KEY AUTO_INCREMENT,
    idquestion INT,
    proposition VARCHAR(100),
    correct BOOLEAN,
    FOREIGN KEY(idquestion) REFERENCES question(idquestion)
);

INSERT INTO question (ideval, question, type, reponse, note) 
 VALUES 
    ((SELECT ideval FROM eval WHERE titre='M01SW01 Grp1'), 'Que signifie le sigle HTML ?', 'LIBRE', 'HyperText Markup Language', 1.0)
;

INSERT INTO question (ideval, question, type, reponse, note) 
 VALUES 
    ((SELECT ideval FROM eval WHERE titre='M01SW01 Grp1'), 'Le HTML est un langage interprété ou compilé ?', 'QCM', '', 1.0)
;

INSERT INTO choix (idquestion, proposition, correct) 
 VALUES
    ((SELECT idquestion FROM question WHERE question LIKE "Le HTML est%"), 'interprété', true),
    ((SELECT idquestion FROM question WHERE question LIKE "Le HTML est%"), 'compilé', false)
;