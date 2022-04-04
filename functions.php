<?php

//Usando namespace.
use \Hcode\Model\User;
use \Hcode\Model\Cart;


//Formatar para valor em Real.
function formatPrice($vlprice){
	if (!$vlprice > 0) $vlprice = 0;

	return number_format($vlprice, 2, ",", ".");  // (2-> casas decimais) - ("," -> separador decimal) - ("." -> separador de milhar)
}//Fim formatPrice().


//Formata data.
function formatDate($date){
	return date("d/m/y", strtotime($date));
}//Fim formatDate().


//Verificar o tipo do user (0/False-> Normal, 1/True-> Adm).
function checkLogin($inadmin = true){
	return User::checkLogin($inadmin);
}//Fim checkLogin().


//Pegar o nome do user da sessão.
function getUserName(){
	$user = User::getFromSession();
	return $user->getdesperson();
}//Fim getUserName().


//Pegar a quantidade de itens no carrinho.
function getCartNrQtd(){
	$cart = Cart::getFromSession(); //Pegando o carrinho da sessão.

	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];
}//Fim getCartNrQtd().


//Pegar o valor dos itens no carrinho.
function getCartVlSubTotal(){
	$cart = Cart::getFromSession(); //Pegando o carrinho da sessão.

	$totals = $cart->getProductsTotals();

	return formatPrice($totals['vlprice']);
}//Fim getCartNrQtd().

?>