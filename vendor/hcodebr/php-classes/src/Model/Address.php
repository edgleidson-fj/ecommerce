<?php 

namespace Hcode\Model;

//Usando namespace.
use \Hcode\DB\Sql;
use \Hcode\Model;

class Address extends Model {

	//Constante.
	const SESSION_ERROR = "AddressError";

	public static function getCEP($nrcep){
		$nrcep = str_replace("-", "", $nrcep); //Para garantir que seja repassado apenas números.

		//Ex:  https://viacep.com.br/ws/01001000/json/

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://viacep.com.br/ws/$nrcep/json/");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data = json_decode(curl_exec($ch), true);

		curl_close($ch);

		return $data;
	}//Fim getCEP().



	public function loadFromCEP($nrcep){
		$data = Address::getCEP($nrcep);

		//Se o 'logradouro' foi definido, E se 'logadouro' não está vazio. 
		if(isset($data['logradouro']) && $data['logradouro'] !== ''){
			$this->setdesaddress($data['logradouro']);
			$this->setdescomplement($data['complemento']);
			$this->setdesdistrict($data['bairro']);
			$this->setdescity($data['localidade']); //Cidade.
			$this->setdesstate($data['uf']);
			$this->setdescountry('Brasil');
			$this->setdeszipcode($nrcep);
		}
	}//Fim loadFromCEP().


	public function save(){
		$sql = new Sql();

		//Procedure.
		$results = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :desnumber, :descomplement, :descity, :desstate, :descountry, :deszipcode, :desdistrict)", [
			':idaddress'=>$this->getidaddress(),
			':idperson'=>$this->getidperson(),
			':desaddress'=>utf8_decode($this->getdesaddress()),
			':desnumber'=>$this->getdesnumber(),
			':descomplement'=>utf8_decode($this->getdescomplement()),
			':descity'=>utf8_decode($this->getdescity()),
			':desstate'=>utf8_decode($this->getdesstate()),
			':descountry'=>utf8_decode($this->getdescountry()),
			':deszipcode'=>$this->getdeszipcode(),
			':desdistrict'=>$this->getdesdistrict()
		]);
		
		if (count($results) > 0) {
			$this->setData($results[0]);
		}
	}//Fim save().


	public static function setMsgError($msg){
		$_SESSION[Address::SESSION_ERROR] = $msg;
	}//Fim setMsgError().


	public static function getMsgError(){
		$msg = (isset($_SESSION[Address::SESSION_ERROR])) ? $_SESSION[Address::SESSION_ERROR] : "";

		Address::clearMsgError();

		return $msg;
	}//Fim getMsgError().


	public static function clearMsgError(){
		$_SESSION[Address::SESSION_ERROR] = NULL;
	}//Fim clearMsgError().
	
}
 ?>