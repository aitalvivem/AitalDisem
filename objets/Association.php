<?php
/**
* Lexeme	Class to represent an association.
* 
* @version	0.3
* @author	Vincent Gleizes
* @author	Lo Congrès Permanent de la Lenga Occitana
*/
class Association{
	
	/**
	* $_lexeme
	* 
	* @var		Lexeme
	* @access	private
	*/
	private $_lexeme;
	
	/**
	* $_item
	* 
	* @var		Item
	* @access	private
	*/
	private $_item;
	
	/**
	* $_nbOui
	* 
	* @var		integer
	* @access	private
	*/
	private $_nbOui;
	
	/**
	* $_nbNon
	* 
	* @var		integer
	* @access	private
	*/
	private $_nbNon;
	
	/**
	* $_verse
	* 
	* @var		integer
	* @access	private
	*/
	private $_verse;
	
	/**
	* $_repAcquise
	* 
	* @var		integer
	* @access	private
	*/
	private $_repAcquise;
	
	/**
	* $_valeurRep
	* 
	* @var		string
	* @access	private
	*/
	private $_valeurRep;
	
	/**
	* __construct	Constructeur de la classe.
	* 
	* @param	$lexeme	(Lexeme)		Lexème of the association
	* @param	$item	(Item)			Item of the association
	* @param	$data	(ArrayAssoc)	Contains the data to initialize the others attributes of the class
	*/
	public function __construct($lexeme, $item, $data){
		if(
			isset($data['nbOui']) && 
			isset($data['nbNon']) && 
			isset($data['verse']) && 
			isset($data['repAcquise'])
		){
			$this->setLexeme($lexeme);
			$this->setItem($item);
			
			foreach($data as $key => $value)
			{
				$method = 'set'.ucfirst($key);
				
				if(method_exists($this, $method))
				{
					$this->$method($value);
				}
			}
		}
	}
	
	/**
	* afficheAssoc	Print the association.
	* 
	* @return	$str	(String)	Html code representing the association
	*/
	public function afficheAssoc(){
		$str ='<div class="assoc">'.$this->lexeme()->afficheLex().$this->item()->afficheItem().'</div>';
		return $str;
	}
	
	/**
	* afficheStatAssoc	Print satistics about the association.
	* 
	* @return	$str	(String)	Html code for the statistics
	*/
	public function afficheStatAssoc(){
		if($this->_nbOui + $this->_nbNon == 0){
			$ratioOui = 0;
			$ratioNon = 0;
		}else{
			$ratioOui = $this->_nbOui/($this->_nbOui + $this->_nbNon) * 100;
			$ratioNon = 100 - $ratioOui;
		}
		
		$mot = $this->lexeme()->orth();
		
		if($this->item()->descOc() != 'aucune description disponible'){
			$sens = ucfirst($this->item()->descOc());
		}elseif($this->item()->descFr() != 'aucune description disponible'){
			$sens = ucfirst($this->item()->descFr());
		}else{
			$sens = ucfirst($this->item()->descEn());
		}
		
		$str = '<div class="statAssoc">
			<h1>Estatisticas per la question precedenta :</h1>
			<p>Per lo mot "<b>'.$mot.'</b>" e lo sens "<b>'.$sens.'</b>" :</p>
			<p><b>'.round($ratioOui).'</b> % dels utilisators an picats "<b>Òc</b>".</p>
			<p><b>'.round($ratioNon).'</b> % dels utilisators an picats "<b>Non</b>".</p>
		</div>';
		
		return $str;
	}
	
	/**
	* proposeAssoc	Generate an html code to present the association as a question
	* 
	* @return	$str	(String)	Html code for the question
	*/
	public function proposeAssoc(){
		$str = '<div class="partie">
			<div class="unePartie">
				<h1>Diriás qu\'aqueste mot es coerent amb aquel sens ?</h1>
				'.$this->afficheAssoc().'
				<div class="listRep">
					<div class="boutonRep"><a href="partie.php?rep=Òc" onclick="attend()">Solide</a></div>
					<div class="boutonRep"><a href="partie.php?rep=Non" onclick="attend()">Pas brica</a></div>
					<div class="boutonRep"><a href="partie.php?rep=none" onclick="attend()">Sabi pas</a></div>
				</div>
			</div>
			<div class="quitter">
				<a href="bye.php">Arrestar la partida</a>
			</div>
		</div>';
		return $str;
	}
	
	// getters
	public function lexeme(){
		return $this->_lexeme;
	}
	public function item(){
		return $this->_item;
	}
	public function nbOui(){
		return $this->_nbOui;
	}
	public function nbNon(){
		return $this->_nbNon;
	}
	public function verse(){
		return $this->_verse;
	}
	public function repAcquise(){
		return $this->_repAcquise;
	}
	public function valeurRep(){
		return $this->_valeurRep;
	}
	
	// setters
	public function setLexeme($lex){
		if($lex instanceof Lexeme)
			$this->_lexeme = $lex;
	}
	public function setItem($lex){
		if($lex instanceof Item)
			$this->_item = $lex;
	}
	public function setNbOui($nb){
		if(is_int($nb))
			$this->_nbOui = $nb;
	}
	public function setNbNon($nb){
		if(is_int($nb))
			$this->_nbNon = $nb;
	}
	public function setVerse($val){
		if(is_int($val))
			$this->_verse = $val;
	}
	public function setRepAcquise($val){
		if(is_int($val))
			$this->_repAcquise = $val;
	}
	public function setValeurRep($val){
		if($val == 'oui' || $val == 'non'){
			$this->_valeurRep = $val;
		}
	}
}
?>