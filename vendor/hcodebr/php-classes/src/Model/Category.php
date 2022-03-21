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

		//Category::updateFile();
	}//Fim delete().
}
?>