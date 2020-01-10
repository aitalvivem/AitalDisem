<?php
/**
* verseSens	This function is used to generate a new sens from an association and insert it in wikidata.
* 			First the function checks if there are already senses without items in wikidata for this lexema.
* 			if there are senses, the function gets them and generate a high-priority question which would be asked
* 			to a user to find out if one of this senses matches the item we are about to insert.
* 			Else the function create a new sens and add the item.
* 
* @param	$assoc	(Association)	The association to generate the sens
* @param	$api_manager	(Api_manager)	An instance of Api_manager
* 
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/
function verseSens($assoc, $api_manager){
	if(!($api_manager instanceof Api_manager)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $api_manager doit être une instance de la classe Api_manager. Donné : '.gettype($api_manager).'.';
		$message['origine'] = 'Fonction verseSens';
		return $message;
	}
	if(!($assoc instanceof Association)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $api_manager doit être une instance de la classe Association. Donné : '.gettype($api_manager).'.';
		$message['origine'] = 'Fonction verseSens';
		return $message;
	}
	
	// check if the lexeme already has senses without item in wikidata
	$listSens = $api_manager->retourneSens($assoc->lexeme()->lid());
	
	if(empty($listSens)){
		// create a new sens
		$Sid = $api_manager->creeSens($assoc->lexeme()->lid(), $assoc->item()->qid());
		
		if(isset($Sid['Erreur'])){
			$message['Erreur'] = 'Erreur de la création du sens '.$assoc->lexeme()->lid().' - '.$assoc->item()->qid();
			$message['origine'] = 'Fonction verseSens';
			$message['ErreurOriginelle'] = $Sid['Erreur'];
			return $message;
		}
		if(!preg_match("#L[0-9]+-S[0-9]+#", $Sid)){
			$message = array('Erreur' => 'Sid retourné par creeSens invalide', 'origine' => 'Fonction verseSens');
			return $message;
		}
		
		$result = $api_manager->ajouteItemSens($Sid, $assoc->item()->qid());
		
		if(isset($result['Erreur'])){
			$message['Erreur'] = 'Erreur dans l\'ajout de l\'item '.$assoc->item()->qid().' pour le sens '.$Sid;
			$message['origine'] = 'Fonction verseSens';
			$message['ErreurOriginelle'] = $result['Erreur'];
			return $message;
		}
	}else{
		// generate a high-priority question
		$assocSer = urlencode(serialize($assoc));
		$_SESSION['questionPrio'] = array($assocSer => $listSens);
	}
}
?>