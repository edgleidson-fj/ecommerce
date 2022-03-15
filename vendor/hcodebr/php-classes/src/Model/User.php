<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; //Usando o namespace Sql.
use \Hcode\Model;  //Usando o namespace Model.

class User extends Model{

	//Constante.
	const SESSION = "User";

	public static function login($login, $password): User{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		//-Validando Login.
		if(count($results) === 0){
			throw new \Exception("Login ou senha inválidos!", 1); //Necessário utilizar a "\" antes de Exception, por causa do diretório.
		}

		$dados = $results[0]; //posição 0 ou seja primeira posição.

		//-Validando Senha.
		//password_verify -> Função do PHP para verificar senha.
		if(password_verify($password, $dados['despassword']) === true){

			$user = new User();
			$user->setDados($dados);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		}
		else{
			throw new \Exception("Login ou senha inválidos!", 1);
		}
	}//Fim login().


	public static function verifyLogin($inadmin = true){		 
		if(
		!isset($_SESSION[User::SESSION])                         //Se a sessão não foi definida.
		|| 
		!$_SESSION[User::SESSION]                                //OU se a sessão não for verdadeira. 
		|| 
		!(int)$_SESSION[User::SESSION]['iduser'] > 0             //OU se o (iduser) não for maior que 0.
		|| 
		(bool)$_SESSION[User::SESSION]["iduser"] !== $inadmin    //OU se (inadmin) não for verdadeiro.
		)
		{  
			header("Location: /admin/login"); //Encaminhar para tela de login.
			exit();
		}
	}//Fim verifyLogin().


	public static function logout(){
		$_SESSION[User::SESSION] = NULL;
	}//Fim logout().


	public static function listAll(){
		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}//Fim listAll().


	public function save(){
		$sql = new Sql();

		//Procedure.
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}//Fim save().


	public function get($iduser){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$dados = $results[0];

		$this->setDados($dados);
	}//Fim get().


	public function update(){
		$sql = new Sql();

		//Procedure.
		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}//Fim update().


	public function delete(){
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}//Fim delete().

}
?>