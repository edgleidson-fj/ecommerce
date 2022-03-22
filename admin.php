<?php

//usando Namespace
use \Hcode\PageAdmin;
use \Hcode\Model\User;


//Rota -> http://www.hcodecommerce.com.br:81/admin     (get)
$app->get('/admin', function() {

	User::verifyLogin(); //Acessando método static.
    
	$page = new PageAdmin(); //Ao chamar o new PageAdmin() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	$page->setTpl("index"); //Adicionar o arquivo index.html que tem o corpo do template.

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/login     (get)
$app->get('/admin/login', function(){

	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("login");
});//Fim da Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/login      (post)
$app->post('/admin/login', function(){

	User::login($_POST['login'], $_POST['password']);

	header("Location: /admin");
	exit();
});//Fim Rota POST.


//Rota -> http://www.hcodecommerce.com.br:81/admin/logout      (get)
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit();
});//Fim da Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/forgot      "Esqueci a Senha"  (get)
$app->get("/admin/forgot", function() {

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");	
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/forgot       "Esqueci a Senha"  (post)
$app->post("/admin/forgot", function(){

	$user = User::getForgot($_POST["email"]);

	header("Location: /admin/forgot/sent");
	exit;
});//Fim Rota POST.


//Rota -> http://www.hcodecommerce.com.br:81/admin/forgot/sent      "Esqueci a Senha - Envio de Email" (get)
$app->get("/admin/forgot/sent", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");
});;//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/forgot/reset      "Esqueci a Senha - Alterar Senha" (get)
$app->get("/admin/forgot/reset", function(){

	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset", array(
		"name"=>$user['desperson'],
		"code"=>$_GET['code']
	));
}); //Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/forgot/reset      "Esqueci a Senha - Alterar Senha" (post)
$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");
}); //Fim Rota
?>