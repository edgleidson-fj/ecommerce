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
	}

}

?>