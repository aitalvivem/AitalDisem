-- ------------------------------------------------------------
--         Script MySQL.
-- ------------------------------------------------------------

-- ---------------------------------------------------------
-- CREATE DataBase: aitaldisem
-- ---------------------------------------------------------

DROP DATABASE IF EXISTS aitaldisem;
CREATE DATABASE IF NOT EXISTS aitaldisem;

USE joc;

-- ------------------------------------------------------------
--  Table: Item
-- ------------------------------------------------------------

CREATE TABLE Item(
        Qid    Varchar (50) NOT NULL ,
        nomFr  Varchar (50) NOT NULL ,
        descFr Varchar (255) NOT NULL ,
        nomOc  Varchar (50) NOT NULL ,
        descOc Varchar (255) NOT NULL ,
        nomEn  Varchar (50) NOT NULL ,
        descEn Varchar (255) NOT NULL
	,CONSTRAINT Item_PK PRIMARY KEY (Qid)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Variete
-- ------------------------------------------------------------

CREATE TABLE Variete(
        idVar         Int NOT NULL AUTO_INCREMENT,
        varieteCon	  Varchar (50) NOT NULL ,
        varieteWiki   Varchar (50) NOT NULL ,
        etiquetteFr   Varchar (50) NOT NULL ,
        etiquetteOc   Varchar (50) NOT NULL ,
        etiquetteEn   Varchar (50) NOT NULL
	,CONSTRAINT Variete_PK PRIMARY KEY (idVar)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Traduction
-- ------------------------------------------------------------

CREATE TABLE Traduction(
        idTrad Int NOT NULL AUTO_INCREMENT,
        orth   Varchar (50) NOT NULL
	,CONSTRAINT Traduction_PK PRIMARY KEY (idTrad)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
-- Table: Categorie
-- ------------------------------------------------------------

CREATE TABLE Categorie(
        codeCat     Int NOT NULL AUTO_INCREMENT,
        catWiki     Varchar (50) NOT NULL ,
        etiquetteFr Varchar (50) NOT NULL ,
        etiquetteOc Varchar (50) NOT NULL ,
        etiquetteEn Varchar (50) NOT NULL ,
        priorite    Int NOT NULL 
	,CONSTRAINT Categorie_PK PRIMARY KEY (codeCat)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: CatCongres
-- ------------------------------------------------------------

CREATE TABLE CatCongres(
        codeCatC Int NOT NULL AUTO_INCREMENT,
        cat      Varchar (50) NOT NULL,
		
		codeCat	 Int NOT NULL
	,CONSTRAINT CatCongres_PK PRIMARY KEY (codeCatC)
	
	,CONSTRAINT CatCongres_Categorie_FK FOREIGN KEY (codeCat) REFERENCES Categorie(codeCat)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Lexeme
-- ------------------------------------------------------------

CREATE TABLE Lexeme(
        Lid     Varchar (50) NOT NULL ,
        orth    Varchar (50) NOT NULL ,
        freq    Float NOT NULL ,
        codeCat Int NOT NULL ,
		erreur 	Varchar	(255) DEFAULT NULL
	,CONSTRAINT Lexeme_PK PRIMARY KEY (Lid)

	,CONSTRAINT Lexeme_Categorie_FK FOREIGN KEY (codeCat) REFERENCES Categorie(codeCat)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Logs
-- ------------------------------------------------------------

CREATE TABLE Logs(
        id  	Int NOT NULL AUTO_INCREMENT,
        Sid 	Varchar (50) NOT NULL, 
        Qid 	Varchar (50) NOT NULL, 
		dateLog	DATETIME
	,CONSTRAINT Logs_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Association
-- ------------------------------------------------------------

CREATE TABLE Association(
        Qid   Varchar (50) NOT NULL ,
        Lid   Varchar (50) NOT NULL ,
        nbOui Int NOT NULL ,
        nbNon Int NOT NULL ,
        verse Int NOT NULL ,
		repAcquise	Int DEFAULT 0 ,
		valeurRep	Varchar (50) DEFAULT NULL
	,CONSTRAINT Association_PK PRIMARY KEY (Qid,Lid)

	,CONSTRAINT Association_Item_FK FOREIGN KEY (Qid) REFERENCES Item(Qid) ON DELETE CASCADE
	,CONSTRAINT Association_Lexeme0_FK FOREIGN KEY (Lid) REFERENCES Lexeme(Lid) ON DELETE CASCADE
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: EtreUtilise
-- ------------------------------------------------------------

CREATE TABLE EtreUtilise(
        idVar Int NOT NULL ,
        Lid   Varchar (50) NOT NULL
	,CONSTRAINT EtreUtilise_PK PRIMARY KEY (idVar,Lid)

	,CONSTRAINT EtreUtilise_Variete_FK FOREIGN KEY (idVar) REFERENCES Variete(idVar) ON DELETE CASCADE
	,CONSTRAINT EtreUtilise_Lexeme0_FK FOREIGN KEY (Lid) REFERENCES Lexeme(Lid) ON DELETE CASCADE
)ENGINE=InnoDB;


-- ------------------------------------------------------------
--  Table: Correspondre
-- ------------------------------------------------------------

CREATE TABLE Correspondre(
        idTrad Int NOT NULL ,
        Lid    Varchar (50) NOT NULL
	,CONSTRAINT Correspondre_PK PRIMARY KEY (idTrad,Lid)

	,CONSTRAINT Correspondre_Traduction_FK FOREIGN KEY (idTrad) REFERENCES Traduction(idTrad) ON DELETE CASCADE
	,CONSTRAINT Correspondre_Lexeme0_FK FOREIGN KEY (Lid) REFERENCES Lexeme(Lid) ON DELETE CASCADE
)ENGINE=InnoDB;
