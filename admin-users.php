<?php

//usando Namespace
use \Hcode\PageAdmin;
use \Hcode\Model\User;


//Rota -> http://www.hcodecommerce.com.br:81/admin/users      (get)
$app->get('/admin/users', function(){

	User::verifyLogin();

	//Se a "search" foi definida pegue o valor, se não deixe o valor vazio.
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	//Se o "page" foi definido pegue o número da página, se não passe a página-1.
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {
		$pagination = User::getPageSearch($search, $page);
	} 
	else{
		$pagination = User::getPage($page);
	}

	$pages = []; //Array vazio.

	for($x=0; $x < $pagination['pages']; $x++){
		//Preencher o Array.
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}//Fim for.

	$page = new PageAdmin();

	$page->setTpl("users", array(
		'users'=>$pagination['data'],
		'search'=>$search,
		'pages'=>$pages
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

?>