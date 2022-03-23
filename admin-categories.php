<?php

//usando Namespace
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;


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


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/categories   (get)
$app->get("/admin/categories/:idcategory/products", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);
});//Fim Rota.


//Tabela PRODUTO-CATEGORIA

//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/categories/1/products   "INSERT" (get)
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->addProduct($product);

	header("Location: /admin/categories/". $idcategory ."/products");
	exit;
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/admin/categories/1/products   "DELETE" (get)
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$product = new Product();
	$product->get((int)$idproduct);

	$category->removeProduct($product);

	header("Location: /admin/categories/". $idcategory ."/products");
	exit;
});//Fim Rota.

?>