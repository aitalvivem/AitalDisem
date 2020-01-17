<?php
/**
* insereItemCorres	Fill the tables Item and Correspondre of the database.
* 
* @param	$api_manager	(Api_manager)	Instance of Api_manager
* @param	$db_manager		(Db_manager)	Instance of Db_manager
* @param	$useTrad		(Bool)			Parameter saying if we search for the french translation of the spelling too
* 
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/
function insereItemCorres($api_manager, $db_manager, $useTrad){
	// check the parameters
	$err = 0;
	
	if(!($api_manager instanceof Api_manager)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $api_manager doit être une instance de la classe Api_manager. Donné : '.gettype($api_manager).'.';
	}
	if(!($db_manager instanceof Db_manager)){
		$err++;
		$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $db_manager doit être une instance de la classe Db_manager. Donné : '.gettype($db_manager).'.';
	}
	
	if($err > 0){
		$message['origine'] = 'Méthode insereItemCorres';
		return $message;
	}
	
	$listLex = $db_manager->getAllLex();
	$message = array();
	
	foreach($listLex as $cle => $lex){
		$Lid = $lex['Lid'];
		$orth = $lex['orth'];
		
		// get the matching items
		$itemCorres = getItemCorres($orth, $api_manager, $db_manager, $useTrad);
		if(isset($itemCorres['Erreur'])){
			$message[] = array(
							'Erreur' => 'Erreur dans l\'insertion de catégorie',
							'origine' => 'Méthode insereCat',
							'ErreurOriginelle' => $itemCorres
						);
			continue;
		}
		
		foreach($itemCorres as $cle => $Qid){
			$data['Qid'] = $Qid;
			
			// get the label and description en occitan, french, english
			$infos = $api_manager->getInfoItem($Qid);
			if(isset($infos['Erreur'])){
				$message[] = array(
					'Erreur' => 'Erreur dans l\'insertion de catégorie',
					'origine' => 'Méthode insereCat',
					'ErreurOriginelle' => $infos
				);
				continue;
			}
			
			$listLabels = $infos['labels'];
			$listDesc = $infos['descriptions'];
			
			$listLg = [$api_manager->main_lg(), $api_manager->trad_lg(), 'en'];
			
			foreach($listLg as $lg){
				if(isset($listLabels[$lg]))
					$data['nom'.ucfirst($lg)] = $listLabels[$lg];
				else
					$data['nom'.ucfirst($lg)] = 'étiquette inconnue';
				
				if(isset($listDesc[$lg]))
					$data['desc'.ucfirst($lg)] = $listDesc[$lg];
				else
					$data['desc'.ucfirst($lg)] = 'aucune description disponible';
			}
			
			$res = $db_manager->insereItem($data);
			if(isset($res['Erreur'])){
				$message[] = array(
					'Erreur' => 'Erreur dans l\'insertion de catégorie',
					'origine' => 'Méthode insereCat',
					'ErreurOriginelle' => $res
				);
				continue;
			}
			$res = $db_manager->insereAssoc($Lid, $Qid);
			if(isset($res['Erreur'])){
				$message[] = array(
					'Erreur' => 'Erreur dans l\'insertion de catégorie',
					'origine' => 'Méthode insereCat',
					'ErreurOriginelle' => $res
				);
				continue;
			}
		}
	}
	if(!empty($message))
		return $message;
}
?>