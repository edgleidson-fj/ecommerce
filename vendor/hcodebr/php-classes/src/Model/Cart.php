<?php 

namespace Hcode\Model;

//Usando namespace.
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model {

	//Constante.
	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";


	//Limpar sessão para zerar o carrinho.
	public function removeSession(){
		$_SESSION[Cart::SESSION] = Null;
		session_regenerate_id();
	}//Fim removeSession().


	public static function getFromSession()	{
		$cart = new Cart();

		//Se a Sessão já existe e se dentro dela tem o idcart que seja maior que 0.
		if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
		} 
		else {
			$cart->getFromSessionID();

			//Se o idcart for igual 0, significar que aind anão existia o carrinho.
			if (!(int)$cart->getidcart() > 0) {

				$data = [
					'dessessionid'=>session_id() //session_id() é uma função do próprio PHP.
				];

				//False -> Garatir que é um user comum acessando o próprio carrinho, e não um Admin.
				if (User::checkLogin(false)) {
					$user = User::getFromSession();
					
					$data['iduser'] = $user->getiduser();
				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession(); //Inserir o carrinho na Sessão.
			}
		}

		return $cart;
	}//Fim static getFromSession().


	public function setToSession()	{
		$_SESSION[Cart::SESSION] = $this->getValues();
	}//Fim setToSession().


	public function getFromSessionID()	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);

		if (count($results) > 0) {
			$this->setData($results[0]);
		}
	}//Fim getFomSessionID().


	public function get(int $idcart){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
		]);

		if (count($results) > 0) {
			$this->setData($results[0]);
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

		$this->setData($results[0]);
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
		if ($all) {
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		} 
		else { //($all = false) ---> Remover apenas um item selecinado por vez.
			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
				':idcart'=>$this->getidcart(),
				':idproduct'=>$product->getidproduct()
			]);
		}

		$this->getCalculateTotal();
	}//Fim removeProduct().

	public function getProducts(){
		$sql = new Sql();

		$rows = $sql->select("
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl 
			ORDER BY b.desproduct
		", [
			':idcart'=>$this->getidcart()
		]);

		return Product::checkList($rows);
	}//Fim getProducts().


	public function getProductsTotals()	{
		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
			FROM tb_products a
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
		", [
			':idcart'=>$this->getidcart()
		]);

		if (count($results) > 0) {
			return $results[0];
		} else {
			return []; //Retornar um Array vazio.
		}
	}//Fim getProductTotals().


	//Frete.
	public function setFreight($nrzipcode){
		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if ($totals['nrqtd'] > 0) {

			//Ex: Regras de negócio do frete dos Correios.
			if ($totals['vlheight'] < 2) $totals['vlheight'] = 2; //Se altura for menor de 2, então será = 2;
			if ($totals['vllength'] < 16) $totals['vllength'] = 16; //Se o comprimento for menor de 16, então será = 16;

			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'S'
			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);

			$result = $xml->Servicos->cServico;

			//Se apresentar algum erro.
			if ($result->MsgErro != '') {
				Cart::setMsgError($result->MsgErro);
			} 
			else {
				Cart::clearMsgError();
			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;
		} 
		else {
		}
	}//Fim setFreight().


	public static function formatValueToDecimal($value):float {
		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
	}//Fim formatValueToDecimal().


	public static function setMsgError($msg){
		$_SESSION[Cart::SESSION_ERROR] = $msg;
	}//Fim setMsgError().


	public static function getMsgError(){
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;
	}//Fim getMsgError();


	public static function clearMsgError(){
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}//Fim clearMsgError().


	public function updateFreight(){

		if ($this->getdeszipcode() != '') {
			$this->setFreight($this->getdeszipcode());
		}
	}//Fim updateFreight().


	public function getValues(){
		$this->getCalculateTotal();

		return parent::getValues();
	}//Fim getValues().


	public function getCalculateTotal(){
		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + (float)$this->getvlfreight());
	}//Fim getCalculateTotal().

}
 ?>