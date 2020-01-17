<?php
/**
* getItemCorres	Get the Q-id of the items of which the label maches to a spelling (or to its french translation if the useTrad parameter is true)
* 
* @param	$orth			(String)		The spelling to look for
* @param	$api_manager	(Api_manager)	Instance of Api_manager to communicate with the wikidata API
* @param	$db_manager		(Db_manager)	Instance of Db_manager, to communicate with the database
* @param	$useTrad		(Bool)			Parameter saying if we search for the french translation of the spelling too
* 
* @return	$listQid	(Array)		The list of the Q-id found
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/
function getItemCorres($orth, $api_manager, $db_manager, $useTrad){
	// check the parameters
	$err = 0;
	
	if(!is_string($orth)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $orth doit être du type String. Donné : '.gettype($orth).'.';
	}
	if(!($api_manager instanceof Api_manager)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $api_manager doit être une instance de la classe Api_manager. Donné : '.gettype($api_manager).'.';
	}
	if(!($db_manager instanceof Db_manager)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $db_manager doit être une instance de la classe Db_manager. Donné : '.gettype($db_manager).'.';
	}
	if(!is_bool($useTrad)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $useTrad doit être du type Booléen. Donné : '.gettype($useTrad).'.';
	}
	
	if($err > 0){
		$message['origine'] = 'Méthode getItemCorres';
		return $message;
	}
	
	$item_corres = array();
	
	// get the items matching with the occitan word
	$listCorres = $api_manager->chercheCorres($orth, $api_manager->main_lg());
	if(isset($listCorres['Erreur'])){
		$message = array(
						'Erreur' => 'Impossible de trouver les correspondances pour le mot : '.$orth,
						'origine' => 'Méthode getItemCorres',
						'ErreurOriginelle' => $listCorres
					);
		return $message;
	}
	
	foreach($listCorres as $item)
		$item_corres[] = $item;
		
	// if we need to look for the translations
	if($useTrad){
		// get the translations
		$listTrad = $db_manager->getTrad($orth);
		if(isset($listTrad['Erreur'])){
			$message = array(
							'Erreur' => 'Impossible de trouver les correspondances pour le mot : '.$orth,
							'origine' => 'Méthode getItemCorres',
							'ErreurOriginelle' => $listTrad
						);
			return $message;
		}
		
		foreach($listTrad as $trad){
			// get the items matching the translation
			$listCorres = $api_manager->chercheCorres($trad, $api_manager->trad_lg());
			if(isset($listCorres['Erreur'])){
				$message = array(
								'Erreur' => 'Impossible de trouver les correspondances pour le mot : '.$trad,
								'origine' => 'Méthode getItemCorres',
								'ErreurOriginelle' => $listCorres
							);
				return $message;
			}
			
			foreach($listCorres as $item)
				$item_corres[] = $item;
		}
	}
	
	return $item_corres;
}
?>