<?php

session_start();
include('sec/require.php');

$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);

$_SESSION['lexDejaProp'] = array();
$_SESSION['assocTestProp'] = array();
$_SESSION['test'] = true;
$_SESSION['nbTest'] = 0;
$_SESSION['nbBonneRep'] = 0;
unset($_SESSION['questionPrio']);

// get the id and the names of all variety
$listVar = $db_manager->getAllVar();

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

echo '<h1>Planvengut !</h1>';

// ask the user to choose a variety
echo '<div class="selectVar">
<p>Abans de començar te cal causir la varietat a utilizar per aquela partida :</p>';

foreach($listVar as $var){
	echo '<div class="var">
		<a href="partie.php?codeVar='.$var['idVar'].'">'.$var['etiquetteOc'].'</a>
	</div>';
}

echo '</div>';
?>
	</body>
</html>