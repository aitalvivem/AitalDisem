<?php

/**
* biblio_utils	Library of php tools to create/edit/get lexicografical data in wikidata.
* 
* @version	1
* 
* @author	Vincent Gleizes
* @author	Lo Congrès Permanent de la Lenga Occitana
*/

// -----------------------------------------------------------------------------------------------------------
// ---- Class : Api_manager
// -----------------------------------------------------------------------------------------------------------

/**
* Api_manager	Classe to manage the interactions with an API
* 
* @version	0.8
* @author	Vincent Gleizes
* @author	Lo Congrès Permanent de la Lenga Occitana
*/
class Api_manager{
	
	/**
	* $_api_url
	* 
	* @var		string
	* @access	private
	*/
	private $_api_url;
	
	/**
	* $_USER_NAME
	* 
	* @var		string
	* @access	private
	*/
	private $_USER_NAME;
	
	/**
	* $_USER_PASS
	* 
	* @var		string
	* @access	private
	*/
	private $_USER_PASS;
	
	/**
	* $_main_lg
	* 
	* @var		string
	* @access	private
	*/
	private $_main_lg;
	
	/**
	* $_trad_lg
	* 
	* @var		string
	* @access	private
	*/
	private $_trad_lg;
	
	/**
	* __construct	Construtor of the class Api_manager
	* 
	* @param	$_api_url	(String)	Url to use 
	* @param	$_USER_NAME	(String)	Username of the account to connect to the API
	* @param	$_USER_PASS	(String)	Password of the account to connect to the API
	* @param	$_main_lg	(String)	Language code for the lexemes
	* @param	$_trad_lg	(String)	Language code of the translations
	*/
	public function __construct($url, $nom, $mdp, $main_lg, $trad_lg){
		require_once 'Requests-master/library/Requests.php';
		Requests::register_autoloader();
		
		$this->setApi_url($url);
		$this->setUser_name($nom);
		$this->setUser_pass($mdp);
		$this->setMain_lg($main_lg);
		$this->setTrad_lg($trad_lg);
	}
	
	// -----------------------------------------------------------------------------------------------------------
	// ---- Public methods
	// -----------------------------------------------------------------------------------------------------------
	
	/**
	* ajouteItemSens	Add an item to a sens
	* 
	* @access	public
	* 
	* @param	$Sid 	(String)	S-id of the sens in wikidata
	* @param	$Qid 	(String)	Q-id of the item
	* 
	* @return	$result (ArrayAssoc)	Associative array :
	*					'Erreur' => 'msg d'erreur' 	if an error append
	* 					'succes' => 'item bien ajouté' 	if the adding succeed
	*/
	public function ajouteItemSens($Sid, $Qid){
		// check the parameters
		if(!preg_match("#L[0-9]+-S[0-9]+#", $Sid)){
			$err = array('Erreur' => 'Sid invalide', 'origine' => 'Méthode ajouteItemSens');
			return $err;
		}
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode ajouteItemSens');
			return $err;
		}
		
		$Qid = str_replace("Q", "", $Qid);
		
		// ask for a csrf token
		$CSRF_TOKEN = $this->coApi();
		if(isset($CSRF_TOKEN['Erreur'])){
			return $CSRF_TOKEN;
		}
		
		// build the request
		$claim_value = '{"entity-type":"item", "numeric-id": "'.$Qid.'"}';
		
		$data = array(
					'action' => 'wbcreateclaim', 
					'format' => 'json',
					'entity' => $Sid,
					'snaktype' => 'value',
					'property' => 'P5137',
					'value' => $claim_value,
					'token' => $CSRF_TOKEN,
					'bot' => '1'
				);
		
		$result = $this->sendPost($data);
		
		if(isset($result['error'])){
			$err = array(
				'Erreur' => 'Impossible d\'ajouter le sens au lexeme (L-Id : '.$Sid.')',
				'origine' => 'Méthode ajouteItemSens',
				'ErreurOriginelle' => $result['error']
			);
			return $err;
		}
		
		return $result;
	}
	
	/**
	* creeSens	Create a sens for a lexeme using the informations of an item.
	* 
	* @access	public
	* 
	* @param	$Lid 	(String)	L-id of a lexeme
	* @param	$Qid 	(String)	Q-id of an item
	* 	
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	* @return	$Sid	(String)	S-id of the created sens
	*/
	public function creeSens($Lid, $Qid){
		$csrf_token = $this->coApi();
		
		if(isset($csrf_token['Erreur'])){
			$err['Erreur'] = 'Echec de la création du sens';
			$err['origine'] = 'Méthode creeSens';
			$err['ErreurOriginelle'] = $csrf_token;
			
			return $err;
		}
		
		// check the parameters
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode creeSens');
			return $err;
		}
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode creeSens');
			return $err;
		}
		
		$lg = $this->main_lg()."|en|".$this->trad_lg();
		
		// get the language and the description of the item
		$req = "?action=wbgetentities&format=json&ids=".$Qid."&props=descriptions&languages=".urlencode($lg);
		
		$result = $this->execute($req);
		if(isset($result['Erreur'])){
			$err = array(
				'Erreur' => 'Echec de la création du sens',
				'origine' => 'Méthode creeSens',
				'ErreurOriginelle' => $result
			);
			return $err;
		}
		
		// try to get the informations of the item
		if(!isset($result['entities'][$Qid]['descriptions'])){
			$err = array(
				'Erreur' => 'Impossible de récupérer les informations de l\'item (Q-Id : '.$Qid.')', 
				'origine' => 'Méthode creeSens'
			);
			return $err;
		}
		
		$infos = $result['entities'][$Qid]['descriptions'];
		
		// create the data
		$data_sens = '{"glosses":{';
		foreach($infos as $cle => $valeur){
			$data_sens .= '"'.$valeur['language'].'": {"value": "'.str_replace('"', '\"', $valeur['value']).'", "language" : "'.$valeur['language'].'"},';
		}
		$data_sens = substr($data_sens, 0, -1);
		$data_sens .= '}}';
		
		$data = array(
					'action' => 'wbladdsense',
					'format' => 'json',
					'lexemeId' => $Lid,
					'data' => $data_sens,
					'token' => $csrf_token
				);
		
		$reponse = $this->sendPost($data);
		if(isset($reponse['Erreur'])){
			$err = array(
				'Erreur' => 'Impossible d\'ajouter le sens au lexeme (L-Id : '.$Lid.')',
				'origine' => 'Méthode creeSens',
				'ErreurOriginelle' => $reponse
			);
			return $err;
		}
		
		if(isset($reponse["sense"]["id"])){
			return $reponse["sense"]["id"];
		}
		elseif(isset($reponse['error'])){
			$err = array(
				'Erreur' => 'Impossible d\'ajouter le sens au lexeme (L-Id : '.$Lid.')',
				'origine' => 'Méthode creeSens',
				'ErreurOriginelle' => $reponse['error']
			);
			return $err;
		}
	}
	
	/**
	* retourneSens	Return the list of the senses (id, glossos) without items in french, occitan, english for a lexema.
	* 
	* @access	public
	* 
	* @param	$Lid		(String) 		L-id of a lexema
	* 
	* @return 	$err		(ArrayAssoc)	Informations about an error, if one append
	* @return	$sensExist	(ArrayAssoc)	The existing senses as an array :
	* 						'id' => array['glosses']
	*/
	public function retourneSens($Lid){
		// check the parameter
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'L-id invalide', 'origine' => 'Méthode retourneSens');
			return $err;
		}
		
		// get the existing senses
		$req = '?action=wbgetentities&format=json&ids='.$Lid.'&props=claims';
		$result = $this->execute($req);
		
		if(isset($result['error'])){
			$err['Erreur'] = 'Echec de la récupération des sens';
			$err['origine'] = 'Méthode retourneSens';
			$err['ErreurOriginelle'] = $result['error'];
			return $err;
		}
		
		$sensExist = array();
		
		// if the lexema as senses
		if(isset($result['entities'][$Lid]['senses'])){
			$listSens = $result['entities'][$Lid]['senses'];
			
			foreach($listSens as $id => $sens){
				// get the id of the sens
				$idSens = $sens['id'];
				
				// take out of the list the senses having an item
				$aUnSens = false;
				
				foreach($sens['claims'] as $cle => $valeur){
					if($cle == 'P5137')
						$aUnSens = true;
				}
				
				if($aUnSens)
					continue;
				
				$listApp = array();
				foreach($sens['glosses'] as $lg => $infos){
					// add the glosses to a list
					$listApp[$lg] = $infos['value'];
				}
				
				$sensExist[$idSens] = $listApp;
			}
		}
		return $sensExist;
	}
	
	/**
	* chercheCorres	Look for the items of which the label matches to a spelling and a language, return a list of Q-ids.
	* 
	* @access	public
	* 
	* @param	$orth		(String)	Spelling to look for
	* @param	$lg		(String)	Language to look for
	* 
	* @return 	$message	(ArrayAssoc)	Informations about an error, if one append
	* @return	$listItem	(Array) 	List of the Q-id of the items found
	*/
	public function chercheCorres($orth, $lg){
		// check the parameters
		$err = 0;
		
		if(!is_string($orth)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $orth doit être du type String. Donné : '.gettype($orth).'.';
		}
		if(!is_string($lg)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $lg doit être du type String. Donné : '.gettype($lg).'.';
		}
		
		if($err > 0){
			$message['origine'] = 'Méthode chercheCorres';
			return $message;
		}
		
		// build the request
		$query= 'SELECT distinct ?item WHERE{ 
		  {?item ?label "'.$orth.'"@'.$lg.'}.
		  ?article schema:about ?item .
		  {?article schema:inLanguage "fr"} UNION {?article schema:inLanguage "oc"} UNION {?article schema:inLanguage "en"}.
		  SERVICE wikibase:label { bd:serviceParam wikibase:language "oc, fr, en". }   
		}';

		$url="https://query.wikidata.org/sparql?format=json&query=".urlencode($query);


		$curl_handle=curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 3);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Test Wikidata');
		$json = curl_exec($curl_handle);

		curl_close($curl_handle);

		$parsed_json = json_decode($json, $assoc = true);

		$results=$parsed_json['results']['bindings'];

		$listItem = array();
		foreach($results as $res){
			$listItem[] = str_replace('http://www.wikidata.org/entity/', '', $res['item']['value']);
		}
		
		return $listItem;
	}
	
	/**
	* getInfoItem	Get the labels and descriptions of an item.
	* 
	* @access	public
	* 
	* @param	$Qid	(String)	Q-id of the item
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	* @return	$infos	(ArrayAssoc)	List of the labels and descriptions found
	*/
	public function getInfoItem($Qid){
		// check the parameters
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide',
						'origine' => 'Méthode getInfoItem');
			return $err;
		}
		
		$req = '?action=wbgetentities&format=json&ids='.$Qid.'&props=descriptions|labels';
		$result = $this->execute($req);
		
		$listLabels = array();
		$listDesc = array();
		
		if(isset($result['entities'][$Qid]['labels'])){
			$labels = $result['entities'][$Qid]['labels'];
			
			foreach($labels as $cle => $valeur)
				$listLabels[$valeur['language']] = $valeur['value'];
		}
		
		if(isset($result['entities'][$Qid]['descriptions'])){
			$desc = $result['entities'][$Qid]['descriptions'];
			
			foreach($desc as $cle => $valeur)
				$listDesc[$valeur['language']] = $valeur['value'];
		}
		
		$infos = array(
					'labels' => $listLabels, 
					'descriptions' => $listDesc
				);
		
		return $infos;
	}
	
	// -----------------------------------------------------------------------------------------------------------
	// ---- Private methods
	// -----------------------------------------------------------------------------------------------------------
		
	/**
	* execute	Execute a GET request.
	* 
	* @access	private
	* 
	* @param	$req 	(String)	The request
	* 
	* @return 	$message	(ArrayAssoc)	Informations about an error, if one append
	* @return	$result	(ArrayAssoc)	The result of the request
	*/
	private function execute($req){	
		if(!is_string($req)){
			$message['Erreur '] = 'Paramètre invalide, le paramètre $req doit être du type String. Donné : '.gettype($req).'.';
			$message['origine'] = 'Méthode execute';
			return $message;
		}
			
		$req = $this->api_url().$req;
		$response = Requests::get($req);
		
		$result = json_decode($response->body, $assoc = true);
		
		return $result;
	}
		
	/**
	* sendPost	Send a POST request.
	* 
	* @access	private
	* 
	* @param	$data 	(ArrayAssoc)	The data to send
	* 
	* @return 	$message	(ArrayAssoc)	Informations about an error, if one append
	* @return	$result	(ArrayAssoc)	The result of the request
	*/
	private function sendPost(array $data){
		if(!is_array($data)){
			$message['Erreur '] = 'Paramètre invalide, le paramètre $data doit être du type Array. Donné : '.gettype($data).'.';
			$message['origine'] = 'Méthode sendPost';
			return $message;
		}
		
		$url = $this->api_url();
		
		// if the url uses https , we switch it to http
		if(substr($url, 0, 5) == 'https'){
			$url = substr($url, 0, 4).substr($url, 5);
		}
		
		$response = Requests::post($this->api_url(), array(), $data);
		$result = json_decode($response->body, $assoc = true);
		
		return $result;
	}
		
	/**
	* coApi		Log into wikidata and return a csrf token.
	* 
	* @access	private
	* 
	* @return	$err 		(ArrayAssoc)	Informations about an error, if one append
	* @return	$csrf_token (String) 		Csrf token returned
	*/
	private function coApi(){
		// ask for a login token
		$req = "?action=query&meta=tokens&type=login&format=json";
		$result = $this->execute($req);
		if(isset($result['Erreur'])){
			$err = array(
				'Erreur' => 'Erreur dans l\'exécution de la requete',
				'origine' => 'Méthode coApi',
				'ErreurOriginelle' => $result
			);
			return $err;
		}
		
		try{
			$login_token = $result['query']['tokens']['logintoken'];
		}catch (Exception $e){
			$err = array(
				'Erreur' => 'Impossible de récupérer un jeton de connexion (logintoken)',
				'origine' => 'Méthode coApi',
				'Exception reçue' => $e->getMessage()
			);
			return $err;
		}
		
		// send the login request
		$data = array(
					'action' => 'login', 
					'lgname' => $this->user_name(),
					'lgpassword' => $this->user_pass(),
					'format' => 'json',
					'lgtoken' => $login_token
				);
		
		$response = $this->sendPost($data);
		if(isset($response['Erreur'])){
			$err = array(
				'Erreur' => 'Erreur de la connexion à l\api',
				'origine' => 'Méthode coApi',
				'ErreurOriginelle' => $response
			);
			return $err;
		}
		
		// ask for a csrf token
		$data = array(
					'action' => 'query',
					'meta' => 'tokens',
					'format' => 'json'
				);
		
		$result = $this->sendPost($data);
		if(isset($result['Erreur'])){
			$err = array(
				'Erreur' => 'Erreur dans l\'exécution de la requete',
				'origine' => 'Méthode coApi',
				'ErreurOriginelle' => $result
			);
			return $err;
		}
		
		try{
			$csrf_token = $result['query']['tokens']['csrftoken'];
		}catch (Exception $e){
			$err = array(
				'Erreur' => 'Impossible de récupérer un jeton csrf (csrftoken)',
				'origine' => 'Méthode coApi',
				'Exception reçue' => $e->getMessage(),
			);
			return $err;
		}
		
		return $csrf_token;
	}
	
	// -----------------------------------------------------------------------------------------------------------
	// ---- Getters
	// -----------------------------------------------------------------------------------------------------------

	private function api_url(){
		return $this->_api_url;
	}
	private function user_name(){
		return $this->_USER_NAME;
	}
	private function user_pass(){
		return $this->_USER_PASS;
	}
	public function main_lg(){
		return $this->_main_lg;
	}
	public function trad_lg(){
		return $this->_trad_lg;
	}
	
	// -----------------------------------------------------------------------------------------------------------
	// ---- Setters
	// -----------------------------------------------------------------------------------------------------------

	private function setApi_url($url){
		if(is_string($url))
			$this->_api_url = $url;
	}
	private function setUser_name($nom){
		if(is_string($nom))
			$this->_USER_NAME = $nom;
	}
	private function setUser_pass($mdp){
		if(is_string($mdp))
			$this->_USER_PASS = $mdp;
	}
	private function setMain_lg($lg){
		if(is_string($lg))
			$this->_main_lg = $lg;
	}
	private function setTrad_lg($lg){
		if(is_string($lg))
			$this->_trad_lg = $lg;
	}
}

// -----------------------------------------------------------------------------------------------------------
// ---- Class : Db_manager
// -----------------------------------------------------------------------------------------------------------

/**
* Db_manager	Class to manage the interactions with a mysql database.
* 
* @version	0.26
* @author	Vincent Gleizes
* @author	Lo Congrès Permanent de la Lenga Occitana
*/
class Db_manager{
	
	/**
	* $_pdo
	* 
	* @var		string
	* @access	private
	*/
	private $_pdo;
	
	/**
	* __construct	Construtor of the class Db_manager.
	* 
	* @param	$host		(String)	Database host's Url
	* @param	$dbname		(String)	Name of the database to connect to
	* @param	$username	(String)	Username of the database account 
	* @param	$psw		(String)	Password of the database account 
	*/
	public function __construct($host, $dbname, $username, $psw){
		try{
			$pdo = new PDO('mysql:host='.$host.';dbname='.$dbname.';charset=utf8', $username, $psw, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		}
		catch (exeption $e){
			die('Erreur : ' . $e->getMessage());
		}
		
		$this->setPdo($pdo);
	}
	
	// -----------------------------------------------------------------------------------------------------------
	// ---- Public mehods
	// -----------------------------------------------------------------------------------------------------------
	
	/**
	* updateAssoc	Update a row in the table Association.
	* 
	* @access	public
	* 
	* @param	$assoc	(Association)	Instance of Association, the one to update
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function updateAssoc($assoc){
		if(!($assoc instanceof Association)){
			$err['Erreur'] = 'Paramètre invalide, le paramètre $assoc doit être une instance de la classe Association. Donné : '.gettype($assoc).'.';
			$err['origine'] = 'Méthode updateAssoc';
			return $err;
		}
		
		$req = $this->_pdo->prepare('UPDATE association 
									 SET 
										nbOui = :nbOui, 
										nbNon = :nbNon, 
										verse = :verse, 
										repAcquise = :repAcquise, 
										valeurRep = :valeurRep
									 WHERE 
										Lid = :Lid AND 
										Qid = :Qid');
		
		$req->bindValue(':nbOui', $assoc->nbOui());
		$req->bindValue(':nbNon', $assoc->nbNon());
		$req->bindValue(':verse', $assoc->verse());
		$req->bindValue(':repAcquise', $assoc->repAcquise());
		$req->bindValue(':valeurRep', $assoc->valeurRep());
		$req->bindValue(':Lid', $assoc->lexeme()->lid());
		$req->bindValue(':Qid', $assoc->item()->qid());
		$req->execute();
	}
	
	/**
	* updateLexErr	Update a row in the table Lexeme to add an error.
	* 
	* @access	public
	* 
	* @param	$Lid	(String)	Id of the lexema to update
	* @param	$err	(String)	Description of the error
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function updateLexErr($Lid, $err){
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode updateLexErr');
			return $err;
		}
		if(!is_string($err)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $err doit être du type String. Donné : '.gettype($err).'.', 
						'origine' => 'Méthode updateLexErr');
			return $err;
		}
		
		$req = $this->_pdo->prepare('UPDATE lexeme SET erreur = :err WHERE Lid = :Lid');
		$req->bindValue(':err', $err);
		$req->bindValue(':Lid', $Lid);
		$req->execute();
	}
	
	/**
	* getStats	Méthode qui récupère des statistiques de réponse pour une variété donnée.
	* getStats	Get some statistics about a row in Variete.
	* 
	* @access	public
	* 
	* @param	$idVar	(Integer)	IdVar to look for
	* 
	* @return	$stats	(ArrayAssoc)	The statistics found
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getStats($idVar){
		if(!is_int($idVar)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $idVar doit être du type Integer. Donné : '.gettype($idVar).'.', 
						'origine' => 'Méthode getStats');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT SUM(nbOui + nbNon) AS nbRep 
									 FROM association , lexeme, etreutilise 
									 WHERE association.Lid = lexeme.Lid AND 
										   lexeme.Lid = etreutilise.Lid AND 
										   etreutilise.idVar = :idVar');
		$req->bindValue(':idVar', $idVar, PDO::PARAM_INT);
		$req->execute();
		$reponse = $req->fetch(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		$stats['nbRep'] = (int) $reponse['nbRep'];
		
		$req = $this->_pdo->prepare('SELECT COUNT(association.Lid) AS nbAssocValide, valeurRep
									 FROM association , lexeme, etreutilise 
									 WHERE repAcquise = 1 AND 
										   association.Lid = lexeme.Lid AND 
										   lexeme.Lid = etreutilise.Lid AND 
										   etreutilise.idVar = :idVar
									 GROUP BY valeurRep');
		$req->bindValue(':idVar', $idVar, PDO::PARAM_INT);
		$req->execute();
		
		while($donnees = $req->fetch(PDO::FETCH_ASSOC)){
			$stats[$donnees['valeurRep']] = (int) $donnees['nbAssocValide'];
		}
		
		if(empty($stats['oui']))
			$stats['oui'] = 0;
		if(empty($stats['non']))
			$stats['non'] = 0;
		
		$req->closeCursor();
		
		$req = $this->_pdo->prepare('SELECT COUNT(association.Lid) AS nbSensVerse 
									 FROM association , lexeme, etreutilise 
									 WHERE association.verse = 1 AND 
										   association.Lid = lexeme.Lid AND 
										   lexeme.Lid = etreutilise.Lid AND 
										   etreutilise.idVar = :idVar');
		$req->bindValue(':idVar', $idVar, PDO::PARAM_INT);
		$req->execute();
		$reponse = $req->fetch(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		$stats['nbSensVerse'] = (int) $reponse['nbSensVerse'];
		
		return $stats;
	}
	
	/**
	* getVar	Méthode qui récupère les informations d'une variété selon un identifiant donné.
	* getVar	Get the informations about a variety for a given id.
	* 
	* @access	public
	* 
	* @param	$idVar	(Integer)	Id of the variety
	* 
	* @return	$reponse	(ArrayAssoc)	The informations found
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getVar($idVar){
		if(!is_int($idVar)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $idVar doit être du type Integer. Donné : '.gettype($idVar).'.', 
						'origine' => 'Méthode getVar');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT * FROM variete WHERE idVar = :idVar');
		$req->bindValue(':idVar', $idVar, PDO::PARAM_INT);
		$req->execute();
		
		$reponse = $req->fetch(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		$reponse['idVar'] = (int) $reponse['idVar'];
		
		return $reponse;
	}
	
	/**
	* getLex	Get a lexema for a given id.
	* 
	* @access	public
	* 
	* @param	$Lid	(String)	L-id to look for
	* 
	* @return	$reponse	(ArrayAssoc)	Information of the lexema
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getLex($Lid){
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode getLex');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT Lid, orth, freq FROM lexeme WHERE Lid = :Lid');
		$req->bindValue(':Lid', $Lid);
		$req->execute();
		
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		$reponse = $reponse[0];
		
		return $reponse;
	}
	
	/**
	* getItem	Get an item for a given Q-id.
	* 
	* @access	public
	* 
	* @param	$Qid	(String)	Q-id to look for
	* 
	* @return	$reponse	(ArrayAssoc)	Informations about the item
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/	
	public function getItem($Qid){
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode getItem');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT * FROM item WHERE Qid = :Qid');
		$req->bindValue(':Qid', $Qid);
		$req->execute();
		
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		$reponse = $reponse[0];
		
		return $reponse;
	}
	
	/**
	* getAssocTest	Méthode qui retourne la liste des association pouvant servir de test selon une variété.
	* getAssocTest	Return a list of Association that could be used for testinf users, for a given variety.
	* 
	* @access	public
	* 
	* @param	$idVar	(Integer)	Id of the variety
	* 
	* @return	$reponse	(Array)	List of the Association found
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getAssocTest($idVar){
		if(!is_int($idVar)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $idVar doit être du type Integer. Donné : '.gettype($idVar).'.', 
						'origine' => 'Méthode getAssocTest');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT association.Lid, Qid 
									 FROM association, lexeme, etreutilise
									 WHERE repAcquise = 1 AND 
										   association.Lid = lexeme.Lid AND
										   lexeme.Lid = etreutilise.Lid AND
										   idVar = :idVar'
									);
		$req->bindValue(':idVar', $idVar);
		$req->execute();
		
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		return $reponse;
	}
	
	/**
	* getAllVar	Return all the existing variety.
	* 
	* @access	public
	* 
	* @return	$reponse	(ArrayAssoc)	List of the varietys
	*/
	public function getAllVar(){
		$req = $this->_pdo->query('SELECT * FROM variete');
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		return $reponse;
	}
	
	/**
	* getAssoc	Get the usefull informations of an Association.
	* 
	* @access	public
	* 
	* @param	$Lid	(String)	L-id of the Association
	* @param	$Qid	(String)	Q-id of the Association
	* 
	* @return	$rep	(ArrayAssoc)	The informations found
	*/
	public function getAssoc($Lid, $Qid){
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode getAssoc');
			return $err;
		}
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode getAssoc');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT nbOui, nbNon, verse, repAcquise, valeurRep FROM association WHERE Lid = :Lid AND Qid = :Qid');
		$req->bindValue(':Lid', $Lid);	
		$req->bindValue(':Qid', $Qid);	
		$req->execute();
		
		$rep = $req->fetch(PDO::FETCH_ASSOC);
		
		$rep['nbOui'] = (int) $rep['nbOui'];
		$rep['nbNon'] = (int) $rep['nbNon'];
		$rep['verse'] = (int) $rep['verse'];
		$rep['repAcquise'] = (int) $rep['repAcquise'];
		
		return $rep;
	}
	
	/**
	* getLexOrthCat	Check if a lexema as a namesake in the database.
	* 
	* @access	public
	* 
	* @param	$orth	(String)	Spelling of the lexema
	* @param	$codeCat	(Integer)	Id of the category of the lexema
	* 
	* @return	$listLid	(Array)	List of the L-ids of the namesakes found
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getLexOrthCat($orth, $codeCat){
		if(!is_string($orth)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $orth doit être du type String. Donné : '.gettype($orth).'.', 
						'origine' => 'Méthode getLexOrthCat');
			return $err;
		}
		if(!is_int($codeCat)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $codeCat doit être du type Integer. Donné : '.gettype($codeCat).'.', 
						'origine' => 'Méthode getLexOrthCat');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT Lid FROM lexeme WHERE orth = :orth AND codeCat = :codeCat');
		$req->bindValue(':orth', $orth);
		$req->bindValue(':codeCat', $codeCat, PDO::PARAM_INT);
		$req->execute();
		
		$listLid = array();
		
		while($donnees = $req->fetch(PDO::FETCH_ASSOC)){
			$listLid[] = $donnees['Lid'];
		}
		
		$req->closeCursor();
		return $listLid;
	}
	
	/**
	* getItemPourLex	Select the corresponding items for a given L-id.
	* 
	* @access	punlic
	* 
	* @param	$Lid	(String)	L-id to look for
	* 
	* @return	$listItem	(Array)	List of the items found
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getItemPourLex($Lid){
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode getItemPourLex');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT 
										item.Qid, 
										nomFr, 
										descFr, 
										nomOc, 
										descOc, 
										nomEn, 
										descEn, 
										(nbOui + nbNon) AS nbProp 
									 FROM 
										item, association 
									 WHERE 
										association.Qid = item.Qid AND 
										association.repAcquise = 0 AND
										Lid = :Lid
									 ORDER BY
										nbProp DESC');
		$req->bindValue(':Lid', $Lid);
		$req->execute();
		
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		$listItem = array();
		
		foreach($reponse as $item){
			$item['nbProp'] = (int) $item['nbProp'];
			
			$listItem[] = $item;
		}
		
		return $listItem;
	}
	
	/**
	* getLexParVarCat	Get all the lexema for a category and a variety given.
	* 
	* @access	public
	* 
	* @param	$codeCat	(Integer)	Id of the category
	* @param	$idVar	(Integer)	Id of the variety
	* 
	* @return	$listLex (Array)	List of the lexemas found, ordered by descending frequency order
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getLexParVarCat($codeCat, $idVar){
		if(!is_int($codeCat)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $codeCat doit être du type Integer. Donné : '.gettype($codeCat).'.', 
						'origine' => 'Méthode getLexParVarCat');
			return $err;
		}
		if(!is_int($idVar)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $idVar doit être du type Integer. Donné : '.gettype($idVar).'.', 
						'origine' => 'Méthode getLexParVarCat');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT
										lexeme.Lid, orth, freq
									 FROM
										lexeme, etreutilise, association
									 WHERE
										codeCat = :codeCat AND
										lexeme.Lid = etreutilise.Lid AND
										idVar = :idVar AND
										erreur IS NULL AND
										repAcquise = 0 AND
										lexeme.Lid = association.Lid
									 ORDER BY
										freq DESC');
		$req->bindValue(':codeCat', $codeCat, PDO::PARAM_INT);
		$req->bindValue(':idVar', $idVar, PDO::PARAM_INT);
		$req->execute();
		
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		$listLex = array();
		
		foreach($reponse as $lexeme){
			$lexeme['freq'] = (float) $lexeme['freq'];
			
			$listLex[] = $lexeme;
		}
		
		return $listLex;
	}
	
	/**
	* getCatParPrio	Get the id of all the category for a given priority.
	* 
	* @access	public
	* 
	* @param	$prio	(Integer)	The priority to search
	* 
	* @return	$listCat	(Array)	List of the codeCat found
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function getCatParPrio($prio){
		if(!is_int($prio)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $prio doit être du type Integer. Donné : '.gettype($prio).'.', 
						'origine' => 'Méthode getCatParPrio');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT codeCat FROM categorie WHERE priorite = :prio');
		$req->bindValue(':prio', $prio, PDO::PARAM_INT);
		$req->execute();
		
		$listCat = array();
		
		while($donnees = $req->fetch(PDO::FETCH_ASSOC))
			$listCat[] = (int) $donnees['codeCat'];
		
		return $listCat;
	}
	
	/**
	* getPrioRest	Get a list of the existing priority.
	* 
	* @access	public
	* 
	* @return	$listPrio	(Array)	The list of the priorities
	*/
	public function getPrioRest(){
		$req = $this->_pdo->query('SELECT DISTINCT priorite FROM categorie ORDER BY priorite DESC');
		
		$listPrio = array();
		
		while($donnees = $req->fetch(PDO::FETCH_ASSOC)){
			$listPrio[] = (int) $donnees['priorite'];
		}
		
		$req->closeCursor();
		
		return $listPrio;
	}
		
	/**
	* getIdVar	Return the idVar for a given Q-id.
	* 
	* @access	public
	* 
	* @param	$Qid	(String)	Q-id to search
	* 
	* @return	$idVar	(Integer)	The idVar found
	*/
	public function getIdVar($Qid){
		if(preg_match("#Q[0-9]+#", $Qid)){
			$req = $this->_pdo->prepare('SELECT idVar FROM variete WHERE varieteWiki = :Qid');
			$req->bindValue(':Qid', $Qid);
			$req->execute();
			
			$rep = $req->fetch(PDO::FETCH_ASSOC);
			$idVar = (int) $rep['idVar'];
			
			$req->closeCursor();
			
			return $idVar;
		}
		else{
			$err = array('Erreur' => 'Q-id invalide.', 
						'origine' => 'Méthode getIdVar');
			return $err;
		}
	}
	
	/**
	* getTrad	Get all the translations for a given lemma.
	* 
	* @access	public
	* 
	* @param	$orth		(Strinq)	Lemma to translate
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	* @return	$listTrad	(Array)	List of the translation's spelling found
	*/
	public function getTrad($orth){
		if(!is_string($orth)){
			$err = array('Erreur' => 'Paramètre invalide, le paramètre $orth doit être du type String. Donné : '.gettype($orth).'.', 
						'origine' => 'Méthode getTrad');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT 
										traduction.orth
									FROM 
										traduction, lexeme, correspondre
									WHERE 
										lexeme.orth = :orth AND
										lexeme.Lid = correspondre.Lid AND
										correspondre.idTrad = traduction.idTrad'
									);
									
		$req->bindValue(':orth', $orth);
		$req->execute();
		
		$listTrad = $req->fetch(PDO::FETCH_ASSOC);
		
		$req->closeCursor();
		
		return $listTrad;
	}
	
	/**
	* getCodeCat	Get the codeCat of a given Q-id in the table Categorie.
	* 
	* @access	public
	* 
	* @param	$Qid	(String)	The Q-id to look for
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	* @return	$codeCat	(Integer) The codeCat found
	*/
	public function getCodeCat($Qid){
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode getCodeCat');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT codeCat FROM categorie WHERE catWiki = :catWiki');
		$req->bindValue(':catWiki', $Qid);
		$req->execute();
		
		$rep = $req->fetch(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		$codeCat = (int) $rep['codeCat'];
		
		return $codeCat;
	}
	
	/**
	* getAllLex	Get all the lexema existing in the database.
	* 
	* @access	public
	* 
	* @return	$reponse	(Array)	The list of lexema, each lexème is represented in an associative array
	*/
	public function getAllLex(){
		$req = $this->_pdo->query('SELECT * FROM lexeme');
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		$req->closeCursor();
		
		return $reponse;
	}
	
	/**
	* insereLog	Insert a new row in the table Logs.
	* 
	* @access	public
	* 
	* @param	$Sid	(String)	S-id to insert
	* @param	$Qid	(String)	Q-id to insert
	* 
	* @err 	$message	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function insereLog($Sid, $Qid){
		if(!preg_match("#L[0-9]+-S[0-9]+#", $Sid)){
			$err = array('Erreur' => 'Sid invalide', 'origine' => 'Méthode insereLog');
			return $err;
		}
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode insereLog');
			return $err;
		}
		
		$datetime = date('Y-m-d H:i:s');
		
		$req = $this->_pdo->prepare('INSERT INTO logs(Sid, Qid, datelog) VALUES(:Sid, :Qid, :date)');
		$req->bindValue(':Sid', $Sid);
		$req->bindValue(':Qid', $Qid);
		$req->bindValue(':date', $datetime);
		
		$req->execute();
	}
	
	/**
	* insereVar	Insert a new row in the table Variete.
	* 
	* @access	public
	* 
	* @param	$varC	(String)	Name used by lo congres to represent the variety
	* @param	$varW	(String)	Q-id representing the variety in wikidata
	* @param	$etFr	(String)	French name of the variety
	* @param	$etOc	(String)	Occitan name of the variety
	* @param	$etEn	(String)	English name of the variety
	* 
	* @return 	$message	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function insereVar($varC, $varW, $etFr, $etOc, $etEn){
		$err = 0;
		
		if(!preg_match("#Q[0-9]+#", $varW)){
			$err++;
			$message['Erreur '.$err] = 'Qid invalide';
		}
		if(!is_string($varC)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $varC doit être du type String. Donné : '.gettype($varC).'.';
		}
		if(!is_string($etFr)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $etFr doit être du type String. Donné : '.gettype($etFr).'.';
		}
		if(!is_string($etOc)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $etOc doit être du type String. Donné : '.gettype($etOc).'.';
		}
		if(!is_string($etEn)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $etEn doit être du type String. Donné : '.gettype($etEn).'.';
		}
		
		if($err > 0){
			$message['origine'] = 'Méthode insereVar, impossible d\'insérer la variété "'.$varC.'"';
			return $message;
		}
		
		$req = $this->_pdo->prepare('INSERT INTO variete(varieteCon, varieteWiki, etiquetteFr, etiquetteOc, etiquetteEn) 
									 VALUES(:varieteCon, :varieteWiki, :etiquetteFr, :etiquetteOc, :etiquetteEn)');
		$req->bindValue(':varieteCon', $varC);
		$req->bindValue(':varieteWiki', $varW);
		$req->bindValue(':etiquetteFr', $etFr);
		$req->bindValue(':etiquetteOc', $etOc);
		$req->bindValue(':etiquetteEn', $etEn);
		
		$req->execute();
	}
	
	/**
	* insereLex	Insert a new row in the table Lexeme and link to the corresponding variety in the table EtreUtilise.
	* 
	* @access	public
	* 
	* @param	$Lid	(String)	L-id to insert
	* @param	$orth	(String)	Spelling of the lexema to insert
	* @param	$freq	(Float)		Frequency of use of the lexema
	* @param	$listVar	(Array)	List of variety of the lexema
	* @param	$codeCat	(Integer)	Id of the category of the lexema
	* 
	* @return 	$message	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function insereLex($Lid, $orth, $freq, $listVar, $codeCat){
		$err = 0;
		
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err++;
			$message['Erreur '.$err] = 'Lid invalide';
		}
		if(!is_string($orth)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $orth doit être du type String. Donné : '.gettype($orth).'.';
		}
		if(!is_float($freq)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $freq doit être du type Float. Donné : '.gettype($freq).'.';
		}
		if(!is_array($listVar)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $listVar doit être du type Array. Donné : '.gettype($freq).'.';
		}
		if(!is_int($codeCat)){
			$err++;
			$message['Erreur '.$err] = 'Paramètre invalide, le paramètre $codeCat doit être du type Integer. Donné : '.gettype($freq).'.';
		}
		
		if($err > 0){
			$message['origine'] = 'Méthode insereLex, impossible d\'insérer le lexeme "'.$orth.'"';
			return $message;
		}
		
		// on vérifie si le Lid n'existe pas déjà dans la base de données
		$req = $this->_pdo->prepare('SELECT * FROM lexeme WHERE Lid = :Lid');
		$req->bindValue(':Lid', $Lid);
		$req->execute();
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		
		if(empty($reponse)){
			$req = $this->_pdo->prepare('INSERT INTO lexeme(Lid, orth, freq, codeCat) VALUES(:Lid, :orth, :freq, :codeCat)');
			$req->bindValue(':Lid', $Lid);
			$req->bindValue(':orth', $orth);
			$req->bindValue(':freq', $freq);
			$req->bindValue(':codeCat', $codeCat);
			$req->execute();
			
			foreach($listVar as $var){
				$req2 = $this->_pdo->prepare('INSERT INTO etreutilise(Lid, idVar) VALUES(:Lid, :idVar)');
				$req2->bindValue(':Lid', $Lid);
				$req2->bindValue(':idVar', $var);
				$req2->execute();
			}
		}
	}
	
	/**
	* insereCat	insert a new row in the table Categorie.
	* 
	* @access	public
	* 
	* @param	$data	(ArrayAssoc)	The data to insert :
	* 						'Qid' => Q-id of the item representing the category in wikidata
	* 						'oc' => label in occitan
	* 						'fr' => label in french
	* 						'en' => label in english
	* 						'priorite' => priority of the category
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	* @return	$idCat	(integer)	The id of the row inserted
	*/
	public function insereCat($data){
		if(!is_array($data)){
			$err = array(
					'Erreur' => 'Paramètre invalide, le paramètre $data doit être du type Array. Donné : '.gettype($data).'.', 
					'origine' => 'Méthode insereCat'
					);
			return $err;
		}
		
		$req = $this->_pdo->prepare('INSERT INTO categorie(catWiki, etiquetteFr, etiquetteOc, etiquetteEn, priorite)
										VALUES(:catWiki, :etiquetteFr, :etiquetteOc, :etiquetteEn, :priorite)
									');
		
		$req->bindValue(':catWiki', $data['Qid']);
		$req->bindValue(':etiquetteFr', $data['fr']);
		$req->bindValue(':etiquetteOc', $data['oc']);
		$req->bindValue(':etiquetteEn', $data['en']);
		$req->bindValue(':priorite', $data['priorite']);
		
		$req->execute();
		
		$idCat = $this->_pdo->lastInsertId();
		
		return $idCat;
	}
	
	/**
	* inseteItem	Insert a new row in the table Item.
	* 
	* @access	public
	* 
	* @param	$data	(ArrayAssoc)	The data to insert :
	* 						'Qid' => Q-id of the item
	* 						'nomFr' => french label
	* 						'descFr' => french description
	* 						'nomOc' => occitan label
	* 						'descOc' => occitan description
	* 						'nomEn' => english label
	* 						'descEn' => english description
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function insereItem($data){
		if(!is_array($data)){
			$err = array(
					'Erreur' => 'Paramètre invalide, le paramètre $data doit être du type Array. Donné : '.gettype($data).'.', 
					'origine' => 'Méthode insereItem'
					);
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT * FROM item WHERE Qid = :Qid');
		$req->bindValue(':Qid', $data['Qid']);		
		$req->execute();
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		
		// var_dump($reponse);
		
		if(empty($reponse)){
			$req = $this->_pdo->prepare('INSERT INTO item(Qid, nomFr, descFr, nomOc, descOc, nomEn, descEn)
											VALUES (:Qid, :nomFr, :descFr, :nomOc, :descOc, :nomEn, :descEn)');
											
			$req->bindValue(':Qid', $data['Qid']);
			$req->bindValue(':nomFr', $data['nomFr']);
			$req->bindValue(':descFr', $data['descFr']);
			$req->bindValue(':nomOc', $data['nomOc']);
			$req->bindValue(':descOc', $data['descOc']);
			$req->bindValue(':nomEn', $data['nomEn']);
			$req->bindValue(':descEn', $data['descEn']);
			
			$req->execute();
		}
	}
	
	/**
	* insereAssoc	Insert a new row in the table Association.
	* 
	* @access	public
	* 
	* @param	$Lid	(String)	L-id to insert
	* @param	$Qid	(String)	Q-id to insert
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function insereAssoc($Lid, $Qid){
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode insereAssoc');
			return $err;
		}
		if(!preg_match("#Q[0-9]+#", $Qid)){
			$err = array('Erreur' => 'Qid invalide', 'origine' => 'Méthode insereAssoc');
			return $err;
		}
		
		$req = $this->_pdo->prepare('SELECT * FROM association WHERE Qid = :Qid AND Lid = :Lid');
		$req->bindValue(':Qid', $Qid);
		$req->bindValue(':Lid', $Lid);
		$req->execute();
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		
		if(empty($reponse)){	
			$req = $this->_pdo->prepare('INSERT INTO association(Qid, Lid, nbOui, nbNon, verse)
											VALUES (:Qid, :Lid, 0, 0, 0)');
			$req->bindValue(':Qid', $Qid);
			$req->bindValue(':Lid', $Lid);
			$req->execute();
		}
	}
	
	/**
	* insereTrad	Insert a new row in the table Traduction.
	* 
	* @acces	public
	* 
	* @param	$orth	(String)	Spelling of the translation to insert
	* 
	* @return 	$message	(ArrayAssoc)	Informations about an error, if one append
	* @return 	$idTrad	(Integer)	Id of the row inserted
	*/
	public function insereTrad($orth){
		if(!is_string($orth)){
			$message['Erreur'] = 'Paramètre invalide, le paramètre $orth doit être du type String. Donné : '.gettype($orth).'.';
			$message['origine'] = 'Méthode insereTrad';
			return $message;
		}
		
		$req = $this->_pdo->prepare('INSERT INTO traduction(orth) VALUES(:orth)');
		$req->bindValue(':orth', $orth);
		$req->execute();
		
		$idTrad = (int) $this->_pdo->lastInsertId();
		
		return $idTrad;
	}
	
	/**
	* insereCorres	Insert a new row in the table Correspondre.
	* 
	* @acces	public
	* 
	* @param	$Lid	(String)	L-id to insert
	* @param	$idTrad	(Integer)	idTrad to insert
	* 
	* @return 	$err	(ArrayAssoc)	Informations about an error, if one append
	*/
	public function insereCorres($Lid, $idTrad){
		if(!preg_match("#L[0-9]+#", $Lid)){
			$err = array('Erreur' => 'Lid invalide', 'origine' => 'Méthode insereCorres');
			return $err;
		}
		if(!is_int($idTrad)){
			$err = array('Erreur' => 'Paramètre invalide, $idTrad doit être du type Integer, donné : '.gettype($idTrad).'.', 
						'origine' => 'Méthode insereCorres');
			return $err;
		}
		
		// check if the association doesn't already exists
		$req = $this->_pdo->prepare('SELECT * FROM correspondre WHERE Lid = :Lid AND idTrad = :idTrad');
		$req->bindValue(':Lid', $Lid);
		$req->bindValue(':idTrad', $idTrad);
		$req->execute();
		$reponse = $req->fetchAll(PDO::FETCH_ASSOC);
		
		if(empty($reponse)){	
			$req = $this->_pdo->prepare('INSERT INTO correspondre(Lid, idTrad) VALUES (:Lid, :idTrad)');
			$req->bindValue(':Lid', $Lid);
			$req->bindValue(':idTrad', $idTrad);
			$req->execute();
		}
	}
	
	// -----------------------------------------------------------------------------------------------------------
	// ---- Setters
	// -----------------------------------------------------------------------------------------------------------
	
	private function setPdo(PDO $pdo){
		$this->_pdo = $pdo;
	}
}
?>