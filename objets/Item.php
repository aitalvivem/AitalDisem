<?php
/**
* Lexeme	Class to represent a item.
* 
* @version	0.1
* @author	Vincent Gleizes
* @author	Lo Congrès Permanent de la Lenga Occitana
*/
class Item{
	
	/**
	* $_qid
	* 
	* @var		string
	* @access	private
	*/
	private $_qid;
	
	/**
	* $_nomFr
	* 
	* @var		string
	* @access	private
	*/
	private $_nomFr;
	
	/**
	* $_descFr
	* 
	* @var		string
	* @access	private
	*/
	private $_descFr;
	
	/**
	* $_nomOc
	* 
	* @var		string
	* @access	private
	*/
	private $_nomOc;
	
	/**
	* $_descOc
	* 
	* @var		string
	* @access	private
	*/
	private $_descOc;
	
	/**
	* $_nomEn
	* 
	* @var		string
	* @access	private
	*/
	private $_nomEn;
	
	/**
	* $_descEn
	* 
	* @var		string
	* @access	private
	*/
	private $_descEn;
	
	/**
	* __construct	Constructor of the class.
	* 
	* @param	$data	(ArrayAssoc)	Contains the data to initialize the attributes of the class
	*/
	public function __construct($data){
		if(
			!empty($data['Qid']) &&
			!empty($data['nomFr']) &&
			!empty($data['descFr']) &&
			!empty($data['nomOc']) &&
			!empty($data['descOc']) &&
			!empty($data['nomEn']) &&
			!empty($data['descEn'])
		){
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
	* afficheItem	Print the item.
	* 
	* @return	$str	(String)	Html code representing the item
	*/
	public function afficheItem(){
		if($this->descOc() != 'aucune description disponible'){
			$str = '<div class="sens" >
					<p>Sens : "<b>'.ucfirst($this->descOc()).'</b>"</p>
				</div>';
		}elseif($this->descFr() != 'aucune description disponible'){
			$str = '<div class="sens" >
					<p>Sens : "<b>'.ucfirst($this->descFr()).'</b>"</p>
				</div>';
		}else{
			$str = '<div class="sens" >
					<p>Sens : "<b>'.ucfirst($this->descEn()).'</b>"</p>
				</div>';
		}
		return $str;
	}
	
	// getters
	public function qid(){
		return $this->_qid;
	}
	public function nomFr(){
		return $this->_nomFr;
	}
	public function descFr(){
		return $this->_descFr;
	}
	public function nomOc(){
		return $this->_nomOc;
	}
	public function descOc(){
		return $this->_descOc;
	}
	public function nomEn(){
		return $this->_nomEn;
	}
	public function descEn(){
		return $this->_descEn;
	}
	
	// setters
	public function setQid($Qid){
		if(preg_match("#Q[0-9]+#", $Qid))
			$this->_qid = $Qid;
	}
	public function setNomFr($nom){
		if(is_string($nom))
			$this->_nomFr = $nom;
	}
	public function setDescFr($desc){
		if(is_string($desc))
			$this->_descFr = $desc;
	}
	public function setNomOc($nom){
		if(is_string($nom))
			$this->_nomOc = $nom;
	}
	public function setDescOc($desc){
		if(is_string($desc))
			$this->_descOc = $desc;
	}
	public function setNomEn($nom){
		if(is_string($nom))
			$this->_nomEn = $nom;
	}
	public function setDescEn($desc){
		if(is_string($desc))
			$this->_descEn = $desc;
	}
}
?>