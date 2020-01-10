<?php

/*
Avant lancer ce script il faut créer la base pour cela il faut exécuter les scripts suivants sur le serveur MySQL :
	- script_joc.sql
*/

echo 'début de la récupération et de l\'insertion des données dans la base : <br/>';

require_once 'sec/biblio_utils.php';
require_once('sec/config.php');

// on instancie les classes Db_manager et Api_manager
$api_manager = new Api_manager($api_url, $api_username, $api_password);
$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);

// on insere les varietes
$db_manager->insereVar("auvern", "Q35359", "Avergnat", "Auvernhat", "Auvergnat");
$db_manager->insereVar("gascon", "Q35735", "Gascon", "Gascon", "Gascon");
$db_manager->insereVar("lemosin", "Q427614", "Limousin", "Lemosin", "Limousin");
$db_manager->insereVar("lengadoc", "Q942602", "Languedocien", "Lengadocian", "Languedocien");
$db_manager->insereVar("provenc", "Q241243", "Provençal", "Provençau", "Provençal");
$db_manager->insereVar("vivaraup", "Q1649613", "Vivaro-Alpin", "Vivaroaupenc", "Vivaro-Alpine");
			
// on insere les catégories
echo 'résultat de l\'insertion des catégories : <br/>';
var_dump(insereCat($catcsv_path, $cat_csv_delimiter, $api_manager, $db_manager));

// on crée un pdo pour récupérer les frequences
try{
	$pdoFreq = new PDO('mysql:host='.$dbfreq_host.';dbname='.$dbfreq_dbname.';charset=utf8', $dbfreq_username, $dbfreq_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch (exeption $e){
	die('Erreur : ' . $e->getMessage());
}

// on insere les lexèmes
echo 'résultat de l\'insertion des lexèmes : <br/>';
var_dump(recupLex($inputscsv_path, $inputscsv_delimiter, $dataxml_path, $pdoFreq, $db_manager));

// si il faut utiliser les traduction
if($useTraduction){
	// on crée un pdo pour récupérer les traductions
	try{
		$pdoTrad = new PDO('mysql:host='.$dbtrad_host.';dbname='.$dbtrad_dbname.';charset=utf8', $dbtrad_username, $dbtrad_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
	catch (exeption $e){
		die('Erreur : ' . $e->getMessage());
	}
	
	// on insere les traductions 
	echo 'résultat de l\'insertion des traductions : <br/>';
	var_dump(recupTrad($pdoTrad, $db_manager));
	
	// on recherche des items et on les associent aux lexemes
	echo 'résultat de l\'insertion des items : <br/>';
	var_dump(insereItemCorres($api_manager, $db_manager, true));
}else{
	// on recherche des items et on les associent aux lexemes
	echo 'résultat de l\'insertion des items : <br/>';
	var_dump(insereItemCorres($api_manager, $db_manager, false));
}

echo 'script creat_db terminé.<br/>';
?>