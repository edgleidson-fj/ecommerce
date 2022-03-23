<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //Usando o namespace Sql.
use \Hcode\Model;  //Usando o namespace Model.

class Product extends Model{

	public static function listAll(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
	}//Fim static listAll().


	public static function checkList($list){
		foreach ($list as &$row) {
			$p = new Product();
			$p->setDados($row);
			$row = $p->getValues();	//Verificar e pega todos os valores dos produtos incluindo as imagens.	
		}

		return $list;
	}//Fim static checkList().


	public function save(){
		$sql = new Sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

		$this->setDados($results[0]);
	}//Fim save().


	public function get($idproduct){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
			':idproduct'=>$idproduct
		]);

		$this->setDados($results[0]);
	}//Fim get().
	

	public function delete(){
		$sql = new Sql();

		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
			"idproduct"=>$this->getidproduct()
		]);
	}//Fim delete().


	//Verificar foto do produto.
	public function checkPhoto(){

		//Se o arquivo existe no diretório ->  ecommerce\res\site\img\products\1.jpg
		if (file_exists(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR . 
			"img" . DIRECTORY_SEPARATOR . 
			"products" . DIRECTORY_SEPARATOR . 
			$this->getidproduct() . ".jpg"
			)) {

			$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

		} else {
			$url = "/res/site/img/products/sem-foto.jpg";
		}

		return $this->setdesphoto($url);
	}//Fim checkPhoto().


	//Sobreecrevendo o método da classe pai.
	public function getValues(){
		$this->checkPhoto();

		$values = parent::getValues();

		return $values;
	}//Fim getValues().



	public function setPhoto($file){
		$extension = explode('.', $file['name']);
		$extension = end($extension);

		switch ($extension) {
			case "jpg":
			case "jpeg":
			$image = imagecreatefromjpeg($file["tmp_name"]);
			break;

			case "gif":
			$image = imagecreatefromgif($file["tmp_name"]);
			break;

			case "png":
			$image = imagecreatefrompng($file["tmp_name"]);
			break;
		}

		$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			"res" . DIRECTORY_SEPARATOR . 
			"site" . DIRECTORY_SEPARATOR . 
			"img" . DIRECTORY_SEPARATOR . 
			"products" . DIRECTORY_SEPARATOR . 
			$this->getidproduct() . ".jpg";

		imagejpeg($image, $dist);

		imagedestroy($image);

		$this->checkPhoto();
	}//Fim setPhoto().
	
}
?>