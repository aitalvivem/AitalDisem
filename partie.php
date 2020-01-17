<?php
session_start();
include('sec/require.php');

if(isset($_SESSION['lexDejaProp'])){
	// define function to handle errors (to write the error's messages in a log.txt file)
	function eclateTableau($tab){
		$str = "\n";
		
		foreach($tab as $cle => $valeur){
			if(is_array($valeur)){
				$str .= $cle.' : '."\n".eclateTableau($valeur);
			}else{
				$str .= $cle.' : '.$valeur."\n";
			}
		}
		
		return $str;
	}
	function ecritErreur($erreur){
		$datetime = date('Y-m-d H:i:s');
		
		$contenu = '----------'."\n";
		$contenu .= 'Date : '.$datetime."\n";
		
		$contenu .= eclateTableau($erreur);
		
		$contenu .= '----------'."\n";
		
		$file = fopen("log.txt", "a");
		fwrite($file, $contenu);
		fclose($file);
	}
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
			<div id="filtre"></div>
			<div id="boxMsgAtt">
				<img id="imgAtt" src="img/cargament.gif" />
				<p id="msgAtt"></p>
			</div>
			<div class="pagePartie">
	<?php

	if(!isset($_GET['codeVar']) && !isset($_SESSION['codeVar'])){
		echo 'hé on t\'a dit de choisir une variété !';
		header('Location: index.php');
		exit;
	}else{
		if(isset($_GET['codeVar']))
			$_SESSION['codeVar'] = (int) $_GET['codeVar'];
		
		if(isset($_GET['rep']))
			include 'resultat.php';
		
		$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);
		
		
		// if this is a test game
		if($_SESSION['test'] == true){
			
			// get a test association
			$assoc = selectLexTest($db_manager, $_SESSION['codeVar']);
			
			// if there are no more test associations in the database before the end of the test, we stop the test but we don't save the user's answers
			if(!($assoc instanceof Association) && $_SESSION['nbTest'] < $toTrustUsers_nbTest){
				$_SESSION['test'] = false;
				$_SESSION['jouePourDuBeurre'] = 1;
				
				ecritErreur($assoc);
				
				header('Location: partie.php');
			}else{
				$_SESSION['assocEnCours'] = urlencode(serialize($assoc));
				echo $assoc->proposeAssoc();
				
				echo '<script>
				function attend(){
					setTimeout(msgAttente, 1000);
				}
				function msgAttente() {
					document.getElementById("msgAtt").innerHTML = "Enregistrament de la responsa...";
					document.getElementById("boxMsgAtt").style.display="flex";
					document.getElementById("filtre").style.display="block";
				}
				</script>';
			}
		}else{
			// else if there is a high-priority question
			if(isset($_SESSION['questionPrio']) && is_array($_SESSION['questionPrio'])){
				// we ask the question
				foreach($_SESSION['questionPrio'] as $cle => $valeur){
					$assoc = unserialize(stripslashes(urldecode($cle)));
					$item = $assoc->item();
					$listSens = $valeur;
				}
				
				$str =  '<div class="partie">
					<div class="unePartie">
						<h1>Dins la tièra de definicions seguenta, quala pensatz essèr la melhora per aquel sens ?</h1>
						'.$item->afficheItem();
				
				foreach($listSens as $Sid => $apparences){
					if(isset($apparences['oc']))
						$str .= '<div class="questionPrio"><a href="partie.php?rep='.$Sid.'" onclick="attend()">'.$apparences['oc'].'</a></div>';
					elseif(isset($apparences['fr']))
						$str .= '<div class="questionPrio"><a href="partie.php?rep='.$Sid.'" onclick="attend()">'.$apparences['fr'].'</a></div>';
					elseif(isset($apparences['en']))
						$str .= '<div class="questionPrio"><a href="partie.php?rep='.$Sid.'" onclick="attend()">'.$apparences['en'].'</a></div>';
				}
					
				$str .= '<div class="questionPrio"><a href="partie.php?rep=none" onclick="attend()">Ges de definicions correspondan</a></div>
					</div>
				</div>';
				
				echo $str;
				
				echo '<script>
				function attend(){
					setTimeout(msgAttente, 1000);
				}
				function msgAttente() {
					document.getElementById("msgAtt").innerHTML = "Enregistrament de la responsa...";
					document.getElementById("boxMsgAtt").style.display="flex";
					document.getElementById("filtre").style.display="block";
				}
				</script>';
			}else{
				// else we choose an association
				$assoc = selectLex($db_manager);
				
				if($assoc instanceof Association){
					$_SESSION['assocEnCours'] = urlencode(serialize($assoc));
					echo $assoc->proposeAssoc();
					
					echo '<script>
					function attend(){
						setTimeout(msgAttente, 1000);
					}
					function msgAttente() {
						document.getElementById("msgAtt").innerHTML = "Enregistrament de la responsa...";
						document.getElementById("boxMsgAtt").style.display="flex";
						document.getElementById("filtre").style.display="block";
					}
					</script>';
				}else{
					header('Location: bye.php?complete=1&rep='.$_GET['rep']);
					exit;
				}
			}
		}
	}
	?>
			</div>
		</body>
	</html>
<?php
}else{
	session_destroy();
	header('Location: index.php');
}
?>