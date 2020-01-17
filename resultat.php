<?php
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

	// get the association tested form the session
	if(isset($_SESSION['assocEnCours'])){
		$assoc = unserialize(stripslashes(urldecode($_SESSION['assocEnCours'])));

		// get the answers
		if(!isset($_GET['rep'])){
			echo 'hé faudrait répondre aux questions qu\'on te pause quand même !';
			header('Location: partie.php');
			exit;
		}else{
			$rep = $_GET['rep'];
			
			// if this is a test game
			if($_SESSION['test'] == true){
				$_SESSION['nbTest'] += 1;
				
				if($rep == 'Òc')
					$valRep = 'oui';
				elseif($rep == 'Non')
					$valRep = 'non';
				else
					$valRep = 'jsp';
				
				// look if its a good answer
				if($valRep == $assoc->valeurRep())
					$_SESSION['nbBonneRep'] += 1;
				if($rep == 'none')
					$rep = 'Sabi pas';
				
				if($_SESSION['nbTest'] >= $toTrustUsers_nbTest){
					$_SESSION['test'] = false;
					
					// calculate the ratio good/bad answers
					$ratio = $_SESSION['nbBonneRep'] / $_SESSION['nbTest'];
					
					if(!($ratio >= $toTrustUsers_goodAnswersRatio))
						$_SESSION['jouePourDuBeurre'] = 1;
					else
						$_SESSION['jouePourDuBeurre'] = 0;
					
				}
			// if this is a high-priority question
			}elseif(isset($_SESSION['questionPrio']) && is_array($_SESSION['questionPrio'])){
				$api_manager = new Api_manager($api_url, $api_username, $api_password, $main_lg, $translation_lg);
				$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);
				
				// we get the association
				foreach($_SESSION['questionPrio'] as $cle => $valeur){
					$assoc = unserialize(stripslashes(urldecode($cle)));
				}
				
				// if the user has chosen a sens
				if(preg_match("#L[0-9]+-S[0-9]+#", $rep)){
					// we get the item of the association
					$item = $assoc->item();
					
					// add the item to the sens
					$result = $api_manager->ajouteItemSens($rep, $item->qid());
					if(isset($result['Erreur'])){
						ecritErreur($result);
					}
					
					$result = $db_manager->insereLog($rep, $item->qid());
					if(isset($result['Erreur'])){
						ecritErreur($result);
					}
					
					unset($_SESSION['questionPrio']);
				}elseif($rep == 'none'){
					// create a new sens
					$Sid = $api_manager->creeSens($assoc->lexeme()->lid(), $assoc->item()->qid());
					
					if(isset($Sid['Erreur'])){
						ecritErreur($Sid);
					}
					
					// add the item
					$result = $api_manager->ajouteItemSens($Sid, $assoc->item()->qid());
							
					if(isset($result['Erreur'])){
						ecritErreur($result);
					}else{
						$assoc->setVerse(1);
						$db_manager->updateAssoc($assoc);
					}
					
					unset($_SESSION['questionPrio']);
				}
			// if this is a regular game and if the user's anwsers are reliable we save it
			}else{
				if($rep == 'none')
					$rep = 'Sabi pas';
				elseif(isset($_SESSION['jouePourDuBeurre']) && $_SESSION['jouePourDuBeurre'] != 1){	
					if($rep == 'Òc'){
						$assoc->setNbOui($assoc->nbOui() + 1);
					}elseif($rep == 'Non'){
						$assoc->setNbNon($assoc->nbNon() + 1);
					}
					
					$nbRep = $assoc->nbOui() + $assoc->nbNon();
					
					// check if the association has enough answers to be trusted
					if($nbRep >= $toTrustData_nbAnswers){
						// check the good/bad answers ratio
						if($assoc->nbNon() == 0)
							$ratioOui = 1;
						else
							$ratioOui = $assoc->nbOui() / ($assoc->nbOui() + $assoc->nbNon());
						
						$ratioNon = 1 - $ratioOui;
						
						if($ratioOui >= $toTrustData_ratio){
							$assoc->setRepAcquise(1);
							$assoc->setValeurRep('oui');
							
							// check if the association is ready to be inserted in wikidata
							if(($assoc->repAcquise() == 1) && ($assoc->valeurRep() == 'oui')){
								$api_manager = new Api_manager($api_url, $api_username, $api_password, $main_lg, $translation_lg);
								
								// insert the association in wikidata
								$result = verseSens($assoc, $api_manager);
								
								if(isset($result['Erreur'])){
									ecritErreur($result);
								}else{
									$assoc->setVerse(1);
								}
							}
						}elseif($ratioNon >= $toTrustData_ratio){
							$assoc->setRepAcquise(1);
							$assoc->setValeurRep('non');
						}
					}
					
					// update the association in the database
					$db_manager = new Db_manager($db_host, $db_dbname, $db_username, $db_password);
					$db_manager->updateAssoc($assoc);
				}
			}
			
			echo '<aside>
				<p>Ta responsa : <b>'.$rep.'</b></p>';
			
			// print some staistics for the user
			echo $assoc->afficheStatAssoc();
			echo '</aside>';
		}
	}
}else{
	session_destroy();
	header('Location: index.php');
}
?>