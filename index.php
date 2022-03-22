<?php 

session_start(); //Iniciando a sessão.

require_once("vendor/autoload.php");

use \Slim\Slim; //Usando o namespace Slim.
use \Hcode\Page;//Usando o namespace Page.
use \Hcode\PageAdmin;//Usando o namespace PageAdmin.
use \Hcode\Model\User;//Usando o namespace User.
use \Hcode\Model\Category;//Usando o namespace Category.

$app = new Slim();

$app->config('debug', true);


//Rota inicial -> http://www.hcodecommerce.com.br:81/     (get)
$app->get('/', function() {
    
	$page = new Page(); //Ao chamar o new Page() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	$page->setTpl("index"); //Adicionar o arquivo index.html que tem o corpo do template.

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


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


//Rota -> http://www.hcodecommerce.com.br:81/admin/users      (get)
$app->get('/admin/users', function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		'users'=>$users
	));
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/users/create    "INSERT" (get)
$app->get('/admin/users/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/users       "DELETE" (get)
$app->get("/admin/users/:iduser/delete", function($iduser) {

	User::verifyLogin();	

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;
});


//Rota -> http://www.hcodecommerce.com.br:81/admin/users      "UPDATE" (get)
$app->get('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/users/create       "INSERT" (post)
$app->post('/admin/users/create', function(){

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin']))?1:0; //Se tiver marcado (inadmin) é igual 1, se não é igual 0.

	$user->setDados($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/users/     "UPDATE" (post)
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

//-------------------------------------------------------------------

//Rota -> http://www.hcodecommerce.com.br:81/admin/categories    (get)
$app->get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [
		"categories"=>$categories
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/categories/create    "INSERT" (get)
$app->get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/categories/create    "INSERT" (post)
$app->post("/admin/categories/create", function(){

	User::verifyLogin();

	$category = new Category();

	$category->setDados($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});//Fim Rota POST.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/categories    "DELETE" (get)
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header('Location: /admin/categories');
	exit;
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/categories    "UPDATE" (get)
$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		"category"=>$category->getValues()
	]);
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/categories    "UPDATE" (post)
$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setDados($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;
});//Fim Rota POST.

//-----------------------------------------------------------------------------------------------

//Rota com parâmetro ->
$app->get("/categories/:idcategory", function($idcategory){

	$category =  new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>[]
	]);
});//Fim Rota.

$app->run();
 ?>