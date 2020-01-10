<?php
session_start();
include('sec/require.php');

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<link rel="stylesheet" href="script.css" />
		<link href="img/icone.ico" rel="shortcut icon" >
		<title>AitalDisèm</title>
	</head>
	
	<body>
<?php

$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);

if(isset($_GET['complete']) && $_GET['complete'] == 1){
	echo '<h1>Òsca ! As respondut per cada lexema present dins la nòstra tièra ! Plan mercès !</h1>';
}

echo '<h1>Al còp que ven, mercès de la participacion !</h1>';

// get the name of the variety
$var = $db_manager->getVar($_SESSION['codeVar']);
$stats = $db_manager->getStats($_SESSION['codeVar']);

if(!isset($var['Erreur']) && !isset($stats['Erreur'])){
	$str = '<div class="stats">
		<h2>Qualques estatisticas per la varietat "'.ucfirst($var['etiquetteOc']).'"</h2>
		<p>Mercès als nòstres contributors avèm :</p>
		<p><b>'.$stats['nbRep'].'</b> responsas d\'utilisators.</p>
		<p><b>'.$stats['oui'].'</b> sens confirmat(s).</p>
		<p><b>'.$stats['non'].'</b> sens infirmat(s).</p>
		<p><b>'.$stats['nbSensVerse'].'</b> sens foguèron ja enregistrats dins Wikidata.</p>
	</div>';
}else{
	$str = '<div class="stats">
		<p>Estatisticas indisponiblas</p>
	</div>';
}

echo $str;

echo '<div class="rejouer"><a href="index.php">Jogar una autra partida</a></div>';

session_destroy();
?>
	</body>
</html>