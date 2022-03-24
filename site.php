<?php

//usando Namespace
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\User;


//Rota inicial -> http://www.hcodecommerce.com.br:81/     (get)
$app->get('/', function() {

	$products = Product::listAll();
    
	$page = new Page(); //Ao chamar o new Page() ele vai acionar o __construct() com header.html que tem o cabeçalho do template.

	//Adicionar o arquivo index.html que tem o corpo do template.
	$page->setTpl("index", [
		"products"=>Product::checkList($products) //Acessando o checkList() para verificar todos valores dos produtos incluindo as imagens. 
	]); 

	//Quanto terminar a execução vai ser acionado o __destruct() com o footer.html que tem a tag para fechar o html finalizando o template.
});//Fim Rota.


//Rota com parâmetro -> http://www.hcodecommerce.com.br:81/categories/    (get)
$app->get("/categories/:idcategory", function($idcategory){

	//Saber qual é a página que está no momento.
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;//Se foi definido a página no GET no URL pega o número, se não, pega a página 1.

	$category =  new Category();

	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	$pages = [];

	for ($i=1; $i <= $pagination['pages']; $i++) { 
    array_push($pages, [
    'link'=>'/categories/'.$category->getidcategory().'?page='.$i,  //Ex: -> /categories/1?page=1
    'page'=>$i
    ]);
    }

	$page = new Page();

	$page->setTpl("category", [
		"category"=>$category->getValues(),
		"products"=>$pagination['data'],
		"pages"=>$pages
	]);	
});//Fim Rota.


?>