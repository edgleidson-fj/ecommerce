<?php

namespace Hcode;

class Model {

	private $values = [];

	//Método mágico.
	public function __call($name, $args){

		$method = substr($name, 0, 3); //Recortar 3 caracteres a partir da posição 0 da String.
		$fieldName = substr($name, 3, strlen($name)); //Recortar todos caracteres a partir da posição 3 da String.

		//var_dump($method, $fieldName);
		//exit();

		switch ($method)
		{
			case "get":
				return (isset($this->values[$fieldName])) ? $this->values[$fieldName] : NULL;
			break;

			case "set":
				$this->values[$fieldName] = $args[0];
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
	}//Fim getValues().
}

?>