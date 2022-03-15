<?php 

session_start(); //Iniciando a sessão.

require_once("vendor/autoload.php");

use \Slim\Slim; //Usando o namespace Slim.
use \Hcode\Page;//Usando o namespace Page.
use \Hcode\PageAdmin;//Usando o namespace PageAdmin.
use \Hcode\Model\User;//Usando o namespace User.

$app = new Slim();

$app->config('debug', true);


//Rota inicial -> http://www.hcodecommerce.com.br:81/.
$app->get('/', function() {
    
	$page = new Page(); //Ao chamar o new Page() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	$page->setTpl("index"); //Adicionar o arquivo index.html que tem o corpo do template.

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin
$app->get('/admin', function() {

	User::verifyLogin(); //Acessando método static.
    
	$page = new PageAdmin(); //Ao chamar o new PageAdmin() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	$page->setTpl("index"); //Adicionar o arquivo index.html que tem o corpo do template.

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/login
$app->get('/admin/login', function(){

	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl("login");

});//Fim da Rota.


//Rota POST ->
$app->post('/admin/login', function(){

	User::login($_POST['login'], $_POST['password']);

	header("Location: /admin");
	exit();

});//Fim Rota POST.


//Rota
$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit();

});//Fim da Rota.


//Rota
$app->get('/admin/users', function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		'users'=>$users
	));
});//Fim Rota.


//Rota.
$app->get('/admin/users/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});//Fim Rota.


//Rota com parâmetro. "DELETE"
$app->get("/admin/users/:iduser/delete", function($iduser) {

	User::verifyLogin();	

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});


//Rota com parâmetro.
$app->get('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});//Fim Rota.


//Rota POST. "INSERT"
$app->post('/admin/users/create', function(){

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin']))?1:0; //Se tiver marcado (inadmin) é igual 1, se não é igual 0.

	$user->setDados($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});//Fim Rota.


//Rota POST com parâmetro. "UPDATE"
$app->post('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin']))?1:0;

	$user->get((int)$iduser);

	$user->setDados($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;
});//Fim Rota POST.

$app->run();

 ?>