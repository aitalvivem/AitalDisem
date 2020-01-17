-- ------------------------------------------------------------
--         Script MySQL.
-- ------------------------------------------------------------

-- ---------------------------------------------------------
-- CREATE DataBase: aitaldisem
-- ---------------------------------------------------------

DROP DATABASE IF EXISTS aitaldisem;
CREATE DATABASE IF NOT EXISTS aitaldisem;

USE aitaldisem;

-- ------------------------------------------------------------
--  Table: Item
-- ------------------------------------------------------------

CREATE TABLE item(
        Qid    Varchar (50) NOT NULL ,
        nomFr  Varchar (50) NOT NULL ,
        descFr Varchar (255) NOT NULL ,
        nomOc  Varchar (50) NOT NULL ,
        descOc Varchar (255) NOT NULL ,
        nomEn  Varchar (50) NOT NULL ,
        descEn Varchar (255) NOT NULL
	,CONSTRAINT item_PK PRIMARY KEY (Qid)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Variete
-- ------------------------------------------------------------

CREATE TABLE variete(
        idVar         Int NOT NULL AUTO_INCREMENT,
        varieteCon	  Varchar (50) NOT NULL ,
        varieteWiki   Varchar (50) NOT NULL ,
        etiquetteFr   Varchar (50) NOT NULL ,
        etiquetteOc   Varchar (50) NOT NULL ,
        etiquetteEn   Varchar (50) NOT NULL
	,CONSTRAINT variete_PK PRIMARY KEY (idVar)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Traduction
-- ------------------------------------------------------------

CREATE TABLE traduction(
        idTrad Int NOT NULL AUTO_INCREMENT,
        orth   Varchar (50) NOT NULL
	,CONSTRAINT traduction_PK PRIMARY KEY (idTrad)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
-- Table: Categorie
-- ------------------------------------------------------------

CREATE TABLE categorie(
        codeCat     Int NOT NULL AUTO_INCREMENT,
        catWiki     Varchar (50) NOT NULL ,
        etiquetteFr Varchar (50) NOT NULL ,
        etiquetteOc Varchar (50) NOT NULL ,
        etiquetteEn Varchar (50) NOT NULL ,
        priorite    Int NOT NULL 
	,CONSTRAINT categorie_PK PRIMARY KEY (codeCat)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Lexeme
-- ------------------------------------------------------------

CREATE TABLE lexeme(
        Lid     Varchar (50) NOT NULL ,
        orth    Varchar (50) NOT NULL ,
        freq    Float NOT NULL ,
        codeCat Int NOT NULL ,
		erreur 	Varchar	(255) DEFAULT NULL
	,CONSTRAINT lexeme_PK PRIMARY KEY (Lid)

	,CONSTRAINT lexeme_categorie_FK FOREIGN KEY (codeCat) REFERENCES categorie(codeCat)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Logs
-- ------------------------------------------------------------

CREATE TABLE logs(
        id  	Int NOT NULL AUTO_INCREMENT,
        Sid 	Varchar (50) NOT NULL, 
        Qid 	Varchar (50) NOT NULL, 
		dateLog	DATETIME
	,CONSTRAINT logs_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Association
-- ------------------------------------------------------------

CREATE TABLE association(
        Qid   Varchar (50) NOT NULL ,
        Lid   Varchar (50) NOT NULL ,
        nbOui Int NOT NULL ,
        nbNon Int NOT NULL ,
        verse Int NOT NULL ,
		repAcquise	Int DEFAULT 0 ,
		valeurRep	Varchar (50) DEFAULT NULL
	,CONSTRAINT association_PK PRIMARY KEY (Qid,Lid)

	,CONSTRAINT association_item_FK FOREIGN KEY (Qid) REFERENCES item(Qid) ON DELETE CASCADE
	,CONSTRAINT association_lexeme0_FK FOREIGN KEY (Lid) REFERENCES lexeme(Lid) ON DELETE CASCADE
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: EtreUtilise
-- ------------------------------------------------------------

CREATE TABLE etreutilise(
        idVar Int NOT NULL ,
        Lid   Varchar (50) NOT NULL
	,CONSTRAINT etreutilise_PK PRIMARY KEY (idVar,Lid)

	,CONSTRAINT etreutilise_variete_FK FOREIGN KEY (idVar) REFERENCES variete(idVar) ON DELETE CASCADE
	,CONSTRAINT etreutilise_lexeme0_FK FOREIGN KEY (Lid) REFERENCES lexeme(Lid) ON DELETE CASCADE
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Correspondre
-- ------------------------------------------------------------

CREATE TABLE correspondre(
        idTrad Int NOT NULL ,
        Lid    Varchar (50) NOT NULL
	,CONSTRAINT correspondre_PK PRIMARY KEY (idTrad,Lid)

	,CONSTRAINT correspondre_traduction_FK FOREIGN KEY (idTrad) REFERENCES traduction(idTrad) ON DELETE CASCADE
	,CONSTRAINT correspondre_lexeme0_FK FOREIGN KEY (Lid) REFERENCES lexeme(Lid) ON DELETE CASCADE
)ENGINE=InnoDB;
