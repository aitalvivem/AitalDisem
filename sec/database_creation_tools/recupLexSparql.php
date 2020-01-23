<?php
/**
* recupLexSparql	Fill tables Lexemes and EtreUtilise of the database using a sparql request and a database for the frequencies.
* 
* @param	$lg			(String)		Code of the language use to research the lexemes ('oc', 'it', 'fr' ...)
* @param	$pdoFreq	(PDO)			Instance of PDO to communicate waith a database matches a lemma and its frequency of use
* @param	$db_manager	(Db_manager)	Instance of Db_manager
* 
* @return	$message	(ArrayAssoc)	Informations about an error, if one append
*/

function recupLexSparql($lg, $db_manager, $pdoFreq){
	if(!is_string($lg)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $lg doit être du type String. Donné : '.gettype($lg).'.';
		$message['origine'] = 'Méthode recupLexSparql';
		return $message;
	}
	if(strlen($lg) != 2){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $lg doit être un code de langue composé de deux caractères. Donné : "'.$lg.'".';
		$message['origine'] = 'Méthode recupLexSparql';
		return $message;
	}
	if(!($db_manager instanceof Db_manager)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $db_manager doit être une instance de la classe Db_manager. Donné : '.gettype($db_manager).'.';
		$message['origine'] = 'Méthode recupLexSparql';
		return $message;
	}
	if(!($pdoFreq instanceof PDO)){
		$message['Erreur'] = 'Paramètre invalide, le paramètre $pdoFreq doit être une instance de la classe PDO. Donné : '.gettype($pdoFreq).'.';
		$message['origine'] = 'Méthode recupLexSparql';
		return $message;
	}
	
	// get all the lexeme for the given language
	$query = "SELECT ?l ?lemma ?cat WHERE {
	  ?l a ontolex:LexicalEntry ; dct:language ?language; wikibase:lemma ?lemma ; wikibase:lexicalCategory ?cat .
	  ?language wdt:P218 '".$lg."'.
	}";
	
	$url="https://query.wikidata.org/sparql?format=json&query=".rawurlencode($query);
	
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 3);
	curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Test Wikidata');
	$json = curl_exec($curl_handle);

	curl_close($curl_handle);

	$parsed_json = json_decode($json, $assoc = true);
	
	$results=$parsed_json['results']['bindings'];
	
	$err = 0;
	
	// for each lexeme found
	foreach($results as $res){
		$Lid = str_replace('http://www.wikidata.org/entity/', '', $res['l']['value']);
		$qid = str_replace('http://www.wikidata.org/entity/', '', $res['cat']['value']);
		$orth = $res['lemma']['value'];
		
		$codeCat = $db_manager->getCodeCat($qid);
		
		// get the frequency
		$req = $pdoFreq->prepare('SELECT freq FROM freq WHERE orth = :orth');
		$req->bindValue(':orth', $orth);
		$req->execute();
		
		$rep = $req->fetch(PDO::FETCH_ASSOC);
		
		$freq = (float) $rep['freq'];
		
		$listIdVar = array();
		
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
					'origine' => 'Méthode recupLexSparql',
					'ErreurOriginelle' => $res
				);
		}
	}
	if($err>0)
		return $message;
}
?>