<?php
/**
* recupLex	Fill tables Lexemes and EtreUtilise of the database using a csv file and, an xml file and a database.
* 
* @param	$csvFile	(String)	Path to the csv file
* @param	$separateur	(String)	Delimiter of the csv file
* @param	$xmlFile	(String)	Path to the xml file
* @param	$pdoFreq	(PDO)		Instance of PDO to communicate waith a database matches a lemma and its frequency of use
* @param	$db_manager		(Db_manager)	Instance of Db_manager
* 
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/
function recupLex($csvFile, $separateur, $xmlFile, $pdoFreq, $db_manager){
	require_once('lit_xml.php');
	
	// check the parameters
	$err = 0;
	
	if(!is_string($csvFile)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $csvFile doit être du type String. Donné : '.gettype($csvFile).'.';
	}
	if(!is_string($separateur)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $separateur doit être du type String. Donné : '.gettype($separateur).'.';
	}
	if(!is_string($xmlFile)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $xmlFile doit être du type String. Donné : '.gettype($xmlFile).'.';
	}
	if(!($pdoFreq instanceof PDO)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $pdoFreq doit être une instance de la classe PDO. Donné : '.gettype($pdoFreq).'.';
	}
	if(!($db_manager instanceof Db_manager)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $db_manager doit être une instance de la classe Db_manager. Donné : '.gettype($db_manager).'.';
	}
	
	if($err > 0){
		$message['origine'] = 'Méthode recupLex';
		return $message;
	}
	
	// read the xml file to get the data as an array
	$xml = lit_xml($xmlFile);
	
	$message = array();
	$err = 0;
	
	// reading through the xml file
	if (($fichier = fopen($csvFile, "r")) !== FALSE) {
		while (($donnees = fgetcsv($fichier, 1000, $separateur)) !== FALSE) {
			$Lid = $donnees[1];
			$xmlId = $donnees[0];
			
			if(preg_match("#L[0-9]+-F[0-9]+#", $Lid))
				continue;
			
			$orth = $xml[$xmlId]['orth'];
			
			$req = $pdoFreq->prepare('SELECT freq FROM freq WHERE orth = :orth');
			$req->bindValue(':orth', $orth);
			$req->execute();
		
			$rep = $req->fetch(PDO::FETCH_ASSOC);
			
			$freq = (float) $rep['freq'];
			
			$idCatW = $xml[$xmlId]['cat'];
			$codeCat = $db_manager->getCodeCat($idCatW);
			
			$listVar = $xml[$xmlId]['listVar'];
			$listIdVar = array();
			foreach($listVar as $cle => $Qid)
				$listIdVar[] = $db_manager->getIdVar($Qid);
			
			// check if the lexeme as a namesake in the database
			$homonymes = $db_manager->getLexOrthCat($orth, $codeCat);
			if(!empty($homonymes)){
				$res = $db_manager->insereLex($Lid, $orth, $freq, $listIdVar, $codeCat);
				$db_manager->updateLexErr($Lid, 'homonyme');
				
				foreach($homonymes as $hom){
					$db_manager->updateLexErr($hom, 'homonyme');
				}
			}else
				$res = $db_manager->insereLex($Lid, $orth, $freq, $listIdVar, $codeCat);
			
			if(isset($res['Erreur'])){
				$err++;
				$message[] = array(
						'Erreur '.$err => 'Impossible d\'insérer le lexeme : '.$orth.'(L-id : '.$Lid.' - xmlId : '.$xmlId.')',
						'origine' => 'Méthode recupLex',
						'ErreurOriginelle' => $res
					);
			}
		}
		fclose($fichier);
	}
	if($err>0)
		return $message;
}
?>