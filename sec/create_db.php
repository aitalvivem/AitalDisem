<?php

/*
Before runnig this script you need to create the database. To do so execute the script "sec/sql/script_joc.sql" on the MySQL server
*/

echo 'début de la récupération et de l\'insertion des données dans la base : <br/>';

require_once 'biblio_utils/biblio_utils.php';
require_once('config.php');

// intanciate Db_manager and Api_manager
$api_manager = new Api_manager($api_url, $api_username, $api_password);
$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);

// insert the varieties
$db_manager->insereVar("auvern", "Q35359", "Avergnat", "Auvernhat", "Auvergnat");
$db_manager->insereVar("gascon", "Q35735", "Gascon", "Gascon", "Gascon");
$db_manager->insereVar("lemosin", "Q427614", "Limousin", "Lemosin", "Limousin");
$db_manager->insereVar("lengadoc", "Q942602", "Languedocien", "Lengadocian", "Languedocien");
$db_manager->insereVar("provenc", "Q241243", "Provençal", "Provençau", "Provençal");
$db_manager->insereVar("vivaraup", "Q1649613", "Vivaro-Alpin", "Vivaroaupenc", "Vivaro-Alpine");
			
// insert the lexicals categories
echo 'résultat de l\'insertion des catégories : <br/>';
var_dump(insereCat($catcsv_path, $cat_csv_delimiter, $api_manager, $db_manager));

// instanciate PDO to get the frequencies
try{
	$pdoFreq = new PDO('mysql:host='.$dbfreq_host.';dbname='.$dbfreq_dbname.';charset=utf8', $dbfreq_username, $dbfreq_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch (exeption $e){
	die('Erreur : ' . $e->getMessage());
}

// insert the lexemes
echo 'résultat de l\'insertion des lexèmes : <br/>';
var_dump(recupLex($inputscsv_path, $inputscsv_delimiter, $dataxml_path, $pdoFreq, $db_manager));

// if we need to use the translations too
if($useTraduction){
	// instanciate PDO to get the translations
	try{
		$pdoTrad = new PDO('mysql:host='.$dbtrad_host.';dbname='.$dbtrad_dbname.';charset=utf8', $dbtrad_username, $dbtrad_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
	catch (exeption $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	// insert the translations
	echo 'résultat de l\'insertion des traductions : <br/>';
	var_dump(recupTrad($pdoTrad, $db_manager));
	
	// search for items and link them to the lexemes
	echo 'résultat de l\'insertion des items : <br/>';
	var_dump(insereItemCorres($api_manager, $db_manager, true));
}else{
	// search for items and link them to the lexemes
	echo 'résultat de l\'insertion des items : <br/>';
	var_dump(insereItemCorres($api_manager, $db_manager, false));
}

echo 'script creat_db terminé.<br/>';
?>