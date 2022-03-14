<?php

namespace Hcode;

class Model {

	private $values = [];

	//Método mágico.
	public function __call($name, $args){

		$method = substr($name, 0, 3); //Recortar 3 caracteres a partir da posição 0 da String.
		$fieldname = substr($name, 3, strlen($name)); //Recortar todos caracteres a partir da posição 3 da String.

		//var_dump($method, $fieldname);
		//exit();

		switch ($method) {
			case 'get':
				$this->values[$fieldname];
				break;
			
			case 'set':
				$this->values[$fieldname] = $args[0];
				break;
		}
	}//Fim __call().


	public function setDados($dados = array()){

		foreach ($dados as $key => $value) {
			$this->{"set" . $key}($value);
		}
	}//Fim setDados().


	public function getValues(){
		return $this->values;
	}
}

?>