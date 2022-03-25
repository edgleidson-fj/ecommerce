<?php

namespace Hcode\Model;

//Usando namespace.
use \Hcode\DB\Sql; 
use \Hcode\Model; 
use \Hcode\Model\User; 

class Cart extends Model{

	//Constante.
	const SESSION = "Cart";


	public static function getFromSession(){
		$cart = new Cart();

		//Se a Sessão já existe e se dentro dela tem o idcart que seja maior que 0.
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		}
		else{
			$cart->getFromSessionID();

			//Se o idcart for igual 0, significar que aind anão existia o carrinho.
			if(!(int)$cart->getidcart() > 0){

				//Criar um novo carrinho.
				$dados = [
					"dessessionid"=>session_id()     //session_id() é uma função do próprio PHP.
				];

				//False -> Garatir que é um user comum acessando o próprio carrinho, e não um Admin.
				if(User::checkLogin(false)){
					$user = User::getFromSession();

					$dados["iduser"] = $user->getiduser();
				}

				$cart->setDados($dados);

				$cart->save();

				//Inserir o carrinho na Sessão.
				$cart->setToSession();
			}
		}

		return $cart;
	}//Fim static getFromSession().


	public function setToSession(){
		$_SESSION[Cart::SESSION] = $this->getValues();
	}//Fim setToSession().


	public function getFromSessionID(){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_cart WHERE dessessionid = :dessessionid", [
			":dessessionid"=>session_id()
		]);

		if(count($results) > 0){
			$this->setDados($results[0]);
		}	
	}//Fim getFomSessionID().


	public function get(int $idcart){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_cart WHERE idcart = :idcart", [
			":idcart"=>$idcart
		]);

		if(count($results) > 0){
			$this->setDados($results[0]);
		}		
	}//Fim get().


	public function save(){
		$sql = new Sql();

		//Procedure.
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);

		$this->setDados($results[0]);
	}//Fim save().
}
?>