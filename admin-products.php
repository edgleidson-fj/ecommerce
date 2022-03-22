<?php

//usando Namespace.
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;
 
//Rota -> http://www.hcodecommerce.com.br:81/admin/products      (get)
$app->get("/admin/products", function(){

	User::verifyLogin();

	$products = Product::listAll();

	$page = new PageAdmin();

	$page->setTpl("products", [
		"products"=>$products
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