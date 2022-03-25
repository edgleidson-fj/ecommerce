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


	//Adicionar produto no carrinho.
	public function addProduct(Product $product){
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);

		$this->getCalculateTotal();
	}//Fim addProduct().  


	//Remover produto no carrinho.
	public function removeProduct(Product $product, $all = false){
		$sql = new Sql();

		//($all = true) ---> Se o botão(Remover Todos) de um determinado item no carrinho, for selecinado.
		if($all){
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);
		}
		else{ //($all = false) ---> Remover apenas um item selecinado por vez.
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				":idcart"=>$this->getidcart(),
				":idproduct"=>$product->getidproduct()
			]);
		}
	}//Fim removeProduct().


	public function getProducts(){
		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, 
			SUM(b.vlprice) AS vltotal FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY b.desproduct
		", [
			':idcart'=>$this->getidcart()
		]);

		return Product::checkList($rows);
	}//Fim getProducts().
	
}
?>