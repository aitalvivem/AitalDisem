<?php
/**
* lit_xml	Read an xml file and return the data as an array.
* 
* @param	$xmlFile	(String)	Path to the xml file
* 
* @return	$result	(ArrayAssoc)	Data of the xml file
*/
function lit_xml($xmlFile){
	$xml = simplexml_load_file($xmlFile);
	$result = array();
	
	foreach($xml->text->body->entry as $entree){
		// get the spelling
		$orth = $entree->form->orth->__toString();
		$cat = $entree->form->pos->__toString();
		
		// get the xmlId
		$attribute = $entree->form->attributes('http://www.w3.org/XML/1998/namespace');
		
		$xmlId = $attribute['id']->__toString();
		
		// get the Q-ids of the dialects
		$listVar = array();
		
		foreach($entree->form->form->listRelation->relation as $relation) {
			if($relation->attributes()->name == 'P346')
				$listVar[] = $relation->attributes()->passive->__toString();
		}
		
		$result[$xmlId] = array('orth' => $orth, 'cat' => $cat, 'listVar' => $listVar);
	}
	
	return $result;
}
?>