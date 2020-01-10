<?php

/**
* selectLex	Select an association which as not already been proposed in the variety choose by the user
* 
* @param	$db_manager		(Db_manager)	Instance of Db_manager
* 
* @return	$rep	(Association)	The association selected
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/
function selectLex($db_manager){
	if(!($db_manager instanceof Db_manager)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $db_manager doit être une instance de la classe Db_manager. Donné : '.gettype($db_manager).'.';
		$message['origine'] = 'Fonction selectLex';
		return $message;
	}
	
	$codeVar = $_SESSION['codeVar'];
	
	$listPrio = $db_manager->getPrioRest();
	
	$i = 0;
	
	do{
		$prio = $listPrio[$i];
		
		// get the list of the existing category for the priority
		$listCodeCat = $db_manager->getCatParPrio($prio);
		
		do{
			// select a random category
			$codeCat = array_rand(array_flip($listCodeCat));
			
			// remove this category from the list of category to be tested
			unset($listCodeCat[array_search($codeCat, $listCodeCat)]);
			
			// get the list of the corresponding lexemes
			$listLex = $db_manager->getLexParVarCat($codeCat, $codeVar);
			
			// remove the lexeme already proposed
			foreach($listLex as $cle => $lex){
				if(in_array($lex['Lid'], $_SESSION['lexDejaProp']))
					unset($listLex[$cle]);
			}
			
			if(!empty($listLex)){
				// look for a lexeme linked to an item
				do{
					// select a random item
					$lexeme = $listLex[array_rand($listLex)];
					
					// remove the lexeme from the list of lexeme to be tested and remember it as already proposed
					unset($listLex[array_search($lexeme, $listLex)]);
					$_SESSION['lexDejaProp'][] = $lexeme['Lid'];
					
					// get the items for this lexeme
					$listItems = $db_manager->getItemPourLex($lexeme['Lid']);
				}while(empty($listItems) && !empty($listLex));
			}
		}while(empty($listItems) && !empty($listCodeCat));
		$i++;
	}while(empty($listItems) && isset($listPrio[$i]));
	
	if(empty($listItems) && !isset($listPrio[$i])){
		$message['Erreur'] = 'Aucun lexème restant, ils ont tous été testés.';
		$message['origine'] = 'Fonction selectLex';
		return $message;
	}else{
		// create an instance of Lexeme
		$lex = new Lexeme($lexeme);
		
		// if there is only one item link to this lexeme we get it, else we choose a random item in the list
		if(count($listItems) == 1){
			$item = new Item($listItems[0]);
		}else{
			$itemSelect = $listItems[array_rand($listItems)];
			$item = new Item($itemSelect);
		}
		
		// get the informations about the association between the lexeme and the item
		$infoAssoc = $db_manager->getAssoc($lex->lid(), $item->qid());
		
		// create and return an instance of Association
		$assoc = new Association($lex, $item, $infoAssoc);
		
		return $assoc;
	}
}
?>