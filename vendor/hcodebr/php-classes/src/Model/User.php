<?php 

namespace Hcode\Model;

//Usando o namespace
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	//Constante.
	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";


	public static function getFromSession(){
		$user = new User();

		//Verificar se a Sessão existe, e se o iduser é maior que 0.
		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

			$user->setData($_SESSION[User::SESSION]);
		}

		return $user;
	}//Fim static getFromSession().


	public static function checkLogin($inadmin = true){
		if (
			!isset($_SESSION[User::SESSION])				 //Se a sessão não foi definida.
			||
			!$_SESSION[User::SESSION]						//OU se a sessão não for verdadeira. 
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 	//OU se o (iduser) não for maior que 0.
		) {
			//Não está logado
			return false;
		} 
		else {//Se user tem permissão de Admin, e se está tentando acessar uma rota de administrador.
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
				return true;
			} 
			else if ($inadmin === false) { //Se user não é Admin, e está acessando o carrinho de compra dele.
				return true;
			} 
			else {
				return false;
			}
		}
	}//Fim checkLogin().


	public static function login($login, $password)	{
		$sql = new Sql();

		$results = $sql->select("
			SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		)); 

		//-Validando Login.
		if (count($results) === 0){
			throw new \Exception("Usuário inexistente ou senha inválida.");
			//Necessário utilizar a "\" antes de Exception, por causa do diretório.
		}

		$data = $results[0]; //posição 0 ou seja primeira posição.

		//-Validando Senha.
		//password_verify -> Função do PHP para verificar senha.
		if (password_verify($password, $data["despassword"]) === true){
			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;
		} 
		else {
			throw new \Exception("Usuário inexistente ou senha inválida.");			
		}
	}//Fim static login().


	public static function verifyLogin($inadmin = true){

		if (!User::checkLogin($inadmin)) {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
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

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
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

		$data = $results[0];

		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data);
	}//Fim get().


	public function update(){
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
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


	public static function getForgot($email, $inadmin = true){
		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email;
		", array(
			":email"=>$email
		));

		if (count($results) === 0){
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else{
			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data['iduser'],
				":desip"=>$_SERVER['REMOTE_ADDR']
			));

			if (count($results2) === 0){
				throw new \Exception("Não foi possível recuperar a senha.");
			}
			else{
				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				if ($inadmin === true) {
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				} 
				else {
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";					
				}				

				$mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
				));				

				$mailer->send();

				return $link;
			}
		}
	}//Fim forgot().


	public static function validForgotDecrypt($code){
		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			":idrecovery"=>$idrecovery
		));

		if (count($results) === 0){
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else{
			return $results[0];
		}
	}//Fim validForgotDecrypt().

	
	public static function setFogotUsed($idrecovery){
		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));
	}//Fim setForgotUsed().


	public function setPassword($password){
		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}//Fim setPassword().
	

	public static function getPasswordHash($password){
		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);
	}//Fim getPasswordHash().


	public static function setError($msg){
		$_SESSION[User::ERROR] = $msg;
	}//setError().


	public static function getError(){
		$msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : '';

		User::clearError();

		return $msg;
	}//getError().


	public static function clearError(){
		$_SESSION[User::ERROR] = NULL;
	}//clearError().


	public static function getErrorRegister(){
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;
	}//getErrorRegister().


	public static function clearErrorRegister(){
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}//clearErrorRegister().

}
 ?>