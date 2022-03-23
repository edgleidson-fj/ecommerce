<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //Usando o namespace Sql.
use \Hcode\Model;  //Usando o namespace Model.

class Category extends Model{

	public static function listAll(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}//Fim listAll().


	public function save(){
		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

		$this->setDados($results[0]);

		Category::updateFile();
	}//Fim save().


	public function get($idcategory){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));

		$this->setDados($results[0]);
	}//Fim get().
	

	public function delete(){
		$sql = new Sql();

		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
			"idcategory"=>$this->getidcategory()
		]);

		Category::updateFile();
	}//Fim delete().


	public static function updateFile(){
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row) {
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}

		file_put_contents(
			$_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
		}//Fim updateFile().


		//Listar todos produtos que estão RELACIONADO e NÂO RELACIONADO.
		public function getProducts($related = true){
			$sql = new Sql();

			//Se for RELACIONADO.
			if($related === true){
				return $sql->select("
					SELECT * FROM tb_products WHERE idproduct IN(
						SELECT a.idproduct FROM tb_products a 
						INNER JOIN tb_productscategories b 
						ON a.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory)
				", [
					"idcategory"=>$this->getidcategory()	
				]);

			}
			else{
				return $sql->select("
					SELECT * FROM tb_products WHERE idproduct NOT IN(
						SELECT a.idproduct FROM tb_products a 
						INNER JOIN tb_productscategories b 
						ON a.idproduct = b.idproduct
						WHERE b.idcategory = :idcategory)
				", [
					"idcategory"=>$this->getidcategory()	
				]);				
			}
		}//Fim getProducts().


	public function addProduct(Product $product){
		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);
	}//Fim addProduct().


	public function removeProduct(Product $product){
		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);
	}//Fim removeProduct().
	
}
?>