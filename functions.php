<?php

//Usando namespace.
use \Hcode\Model\User;


//Formatar para valor em Real.
function formatPrice(float $vlprice){
	return number_format($vlprice, 2, ",", ".");   // (2-> casas decimais) - ("," -> separador decimal) - ("." -> separador de milhar)
}


//Verificar o tipo do user (0/False-> Normal, 1/True-> Adm).
function checkLogin($inadmin = true){
	return User::checkLogin($inadmin);
}


//Pegar o nome do user da sessão.
function getUserName(){
	$user = User::getFromSession();
	return $user->getdesperson();
}

?>