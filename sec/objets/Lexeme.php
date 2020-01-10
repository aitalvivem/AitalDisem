<?php
/**
* Lexeme	Class to represent a lexeme.
* 
* @version	0.1
* @author	Vincent Gleizes
* @author	Lo Congrès Permanent de la Lenga Occitana
*/
class Lexeme{
	/**
	* $_lid
	* 
	* @var		string
	* @access	private
	*/
	private $_lid;
	
	/**
	* $_orth
	* 
	* @var		string
	* @access	private
	*/
	private $_orth;
	
	/**
	* $_freq
	* 
	* @var		float
	* @access	private
	*/
	private $_freq;
	
	/**
	* __construct	Constructor of the class.
	* 
	* @param	$data	(ArrayAssoc)	Contains the data to initialize the attributes of the class
	*/
	public function __construct(array $data){
		if(!empty($data['Lid']) && !empty($data['orth']) && !empty($data['freq'])){
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
	* afficheLex	Print the lexeme.
	* 
	* @return	$str	(String)	Html code representing the lexeme
	*/
	public function afficheLex(){
		$str = '<div class="lexeme" >
			<p>Mot : "<b>'.ucfirst($this->orth()).'</b>"</p>
		</div>';
		
		return $str;
	}
	
	// getters
	public function lid(){
		return $this->_lid;
	}
	public function orth(){
		return $this->_orth;
	}
	public function freq(){
		return $this->_freq;
	}
	
	// setters
	public function setLid($Lid){
		if(preg_match("#L[0-9]+#", $Lid))
			$this->_lid = $Lid;
	}
	public function setOrth($orth){
		if(is_string($orth))
			$this->_orth = $orth;
	}
	public function setFreq($freq){
		if(is_float($freq))
			$this->_freq = $freq;
	}
}
?>