<?php

//Usando namespace.
use \Hcode\Model\User;


//Formatar para valor em Real.
function formatPrice($vlprice){
	if (!$vlprice > 0) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");  // (2-> casas decimais) - ("," -> separador decimal) - ("." -> separador de milhar)
}//Fim formatPrice().


//Verificar o tipo do user (0/False-> Normal, 1/True-> Adm).
function checkLogin($inadmin = true){
	return User::checkLogin($inadmin);
}//Fim checkLogin().


//Pegar o nome do user da sessão.
function getUserName(){
	$user = User::getFromSession();
	return $user->getdesperson();
}//Fim getUserName().

?>