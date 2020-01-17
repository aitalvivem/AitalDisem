<?php

/**
* selectLexTest	Get a validated association for a given variety to test the reliability of the answers of a user.
* 
* @param	$db_manager		(Db_manager)	Instance of Db_manager
* @param	$idVar		(Integer)	Id of the variety
* 
* @return	$assoc	(Association)	An instance of Association
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/
function selectLexTest($db_manager, $idVar){
	if(!($db_manager instanceof Db_manager)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $db_manager doit être une instance de la classe Db_manager. Donné : '.gettype($db_manager).'.';
		$message['origine'] = 'Fonction selectLexTest';
		return $message;
	}
	if(!is_int($idVar)){
		$message = array('Erreur' => 'Paramètre invalide, le paramètre $idVar doit être du type Integer. Donné : '.gettype($idVar).'.', 
						'origine' => 'Fonction selectLexTest');
		return $message;
	}
	
	// get the already validated associations
	$listAssoc = $db_manager->getAssocTest($idVar);
	
	// remove the associations already proposed
	foreach($listAssoc as $cle => $assoc){
		if(in_array($assoc, $_SESSION['assocTestProp'])){
			unset($listAssoc[$cle]);
		}
	}
	
	// choose a random association
	if(!empty($listAssoc))
		$assoc = $listAssoc[array_rand($listAssoc)];
	else{
		$message = array('Erreur' => 'Aucune association de test disponible dans la base de données.', 
						'origine' => 'Fonction selectLexTest');
		return $message;
	}
	
	// add the selected association to the already proposed list
	$_SESSION['assocTestProp'][] = $assoc;
	
	// create a return an instance of Association
	$Lid = $assoc['Lid'];
	$Qid = $assoc['Qid'];
	
	$lex = new Lexeme($db_manager->getLex($Lid));
	
	$item = new Item($db_manager->getItem($Qid));
	
	$infoAssoc = $db_manager->getAssoc($lex->lid(), $item->qid());
	$assoc = new Association($lex, $item, $infoAssoc);
	
	return $assoc;
}

?>