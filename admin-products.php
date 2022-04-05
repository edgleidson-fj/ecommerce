<?php

//usando Namespace.
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;
 
//Rota -> http://www.hcodecommerce.com.br:81/admin/products      (get)
$app->get("/admin/products", function(){

	User::verifyLogin();

	//Se a "search" foi definida pegue o valor, se não deixe o valor vazio.
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";

	//Se o "page" foi definido pegue o número da página, se não passe a página-1.
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	if ($search != '') {
		$pagination = Product::getPageSearch($search, $page);
	} 
	else{
		$pagination = Product::getPage($page);
	}

	$pages = []; //Array vazio.

	for($x=0; $x < $pagination['pages']; $x++){
		//Preencher o Array.
		array_push($pages, [
			'href'=>'/admin/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}//Fim for.

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);
});//Fim Rota.


//Rota -> http://www.hcodecommerce.com.br:81/admin/products/create    "INSERT"  (get)
$app->get("/admin/products/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("products-create");
});//Fim Rota.


//Rota ->  http://www.hcodecommerce.com.br:81/admin/products/create   "INSERT"   (post)
$app->post("/admin/products/create", function(){

	User::verifyLogin();

	$product = new Product();

	$product->setDados($_POST);

	$product->save();

	header("Location: /admin/products");
	exit;
});//Fim Rota POST.


//Rota ->  http://www.hcodecommerce.com.br:81/admin/products     "UPDATE"  (get)
$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$page = new PageAdmin();

	$page->setTpl("products-update", [
		'product'=>$product->getValues()
	]);
});//Fim Rota.


//Rota ->  http://www.hcodecommerce.com.br:81/admin/products     "UPDATE"  (post)
$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->setData($_POST);

	$product->save();

	$product->setPhoto($_FILES["file"]);

	header('Location: /admin/products');
	exit;
});//Fim Rota POST.


//Rota ->  http://www.hcodecommerce.com.br:81/admin/products     "DELETE"  (get)
$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();

	$product = new Product();

	$product->get((int)$idproduct);

	$product->delete();

	header('Location: /admin/products');
	exit;
});//Fim Rota.
?>